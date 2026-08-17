<?php

use App\Components\RouteCollectionGroup;
use App\Cruds\Actions\Validation\LaravelValidationLabelsAction;
use App\Cruds\Actions\Validation\LaravelValidationRulesAction;
use App\Cruds\CrudCollection;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Validator;

Route::get('/', function () {

    return view('index');

})->name('home');

Route::get('components', function () {
   
    $links = RouteCollectionGroup::makeLinks();

    return view('components')
        ->with('links', $links);
    
})->name('components');

Route::get('components/{group}', function ($group) {
    
    $group = RouteCollectionGroup::get($group);

    if(!$group) {
        return abort(404);
    }
    
    return view('component-group')
        ->with('name', $group['name'])
        ->with('group', $group);

})->name('component');

Route::get('/cruds', function (Request $request) {
    
    $oldValues = $request->old();
    $errors = $request->session()->get('errors')?->toArray();

    $cruds = CrudCollection::list($oldValues, $errors);

    // dd($cruds);

    return view('cruds')
        ->with('cruds', $cruds);
    
})->name('cruds');

Route::post('/cruds', function (Request $request) {
    $identifier = $request->input('identifier');
    
    $crudArray = CrudCollection::getCrudByIdentifier($identifier);
    
    if (! $crudArray) {
        abort(404);
    }

    $crudClass = $crudArray['crud'];

    $crud = $crudClass::make();

    Validator::make(
        $request->all(),
        $crud->execute(
            new LaravelValidationRulesAction
        )->toArray(),
        [],
        $crud->execute(
            new LaravelValidationLabelsAction
        )->toArray(),

    )
        ->validate();

    return redirect()
        ->back()
        ->with('success_'.$crudClass::IDENTIFIER, 'All good 👍');
})->name('cruds.store');


Route::view('dashboard', 'dashboard')
    ->middleware(['auth', 'verified'])
    ->name('dashboard');

require __DIR__.'/settings.php';
