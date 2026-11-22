<?php

declare(strict_types=1);

use App\Builder\Contracts\BuilderNode;
use App\Builder\Support\ThemeArchive;
use App\Builder\Support\WorkspaceRepository;
use App\Components\RouteCollectionGroup;
use App\Cruds\Actions\Validation\LaravelValidationLabelsAction;
use App\Cruds\Actions\Validation\LaravelValidationRulesAction;
use App\Cruds\CrudCollection;
use App\DesignSystem\DesignSystemCollection;
use App\Emails\EmailCollection;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Juaniquillo\BackendDesignSystem\Designs\Design;

Route::get('/', function () {

    return view('index');

})->name('home');

Route::get('components', function () {

    $links = RouteCollectionGroup::makeLinks();

    return view('components')
        ->with('links', $links);

})->name('components');

Route::get('components/{group}', function ($group) {

    $routes = new RouteCollectionGroup;
    $group = $routes::get($group);

    if (! $group) {
        return abort(404);
    }

    return view('component-group')
        ->with('name', $group['name'])
        ->with('group', $group)
        ->with('links', $routes::makeLinks());

})->name('component');

Route::get('design-system', function (Request $request) {
    $designs = collect(config('backend-design-system.designs', []))
        ->map(fn (array $definition, string $key): array => [
            'key' => $key,
            'label' => (string) ($definition['label'] ?? $key),
        ])
        ->values();

    $key = (string) $request->query('design', config('backend-design-system.default', 'base'));

    if (! $designs->contains('key', $key)) {
        abort(404);
    }

    // The package binds one design, resolved from the configured default.
    // Switching here means switching the default before the binding is
    // first resolved, which is why nothing may resolve it earlier in the
    // request.
    config(['backend-design-system.default' => $key]);
    app()->forgetInstance(Design::class);

    $design = app(Design::class);

    return view('design-system')
        ->with('design', $design)
        ->with('designs', $designs)
        ->with('boxes', DesignSystemCollection::boxes($design));

})->name('design-system');

Route::get('/cruds', function (Request $request) {

    $oldValues = $request->old();
    $errors = $request->session()->get('errors')?->toArray();

    $cruds = CrudCollection::list($oldValues, $errors);

    return view('cruds')
        ->with('cruds', $cruds);

})->name('cruds');

Route::get('/emails', function () {

    return view('emails')
        ->with('modals', EmailCollection::presentGroup());

})->name('emails');

Route::get('/emails/{name}', function (string $name) {

    $email = EmailCollection::getByIdentifier($name);

    if (! $email) {
        abort(404);
    }

    $mailer = $email::mailer();

    return new $mailer;

})->name('email.preview');

Route::get('/builder', function () {

    return view('builder-onboarding');

})->name('builder');

Route::post('/builder', function () {

    $repository = new WorkspaceRepository;

    $id = $repository->create(
        WorkspaceRepository::DEFAULT_NAME,
        BuilderNode::tree('n1', 'div'),
    );

    return redirect()->route('builder.workspace', ['workspace' => $id]);

})->name('builder.store');

Route::post('/builder/open', function (Request $request) {

    $id = WorkspaceRepository::idFromLink((string) $request->input('link', ''));

    if ($id === null) {
        return back()->with('builder-notice', 'That is not a workspace link. Paste the whole address, or the id at the end of it.');
    }

    if (! (new WorkspaceRepository)->exists($id)) {
        return back()->with('builder-notice', 'That workspace is not here any more.');
    }

    return redirect()->route('builder.workspace', ['workspace' => $id]);

})->name('builder.open');

Route::get('/builder/{workspace}', function (string $workspace) {

    $repository = new WorkspaceRepository;

    if (! WorkspaceRepository::validId($workspace)) {
        abort(404);
    }

    if (! $repository->exists($workspace)) {
        return redirect()
            ->route('builder')
            ->with('builder-notice', 'That workspace is not here any more.');
    }

    return view('builder', ['workspace' => $workspace]);

})->name('builder.workspace');

Route::get('/builder/{workspace}/export', function (string $workspace) {

    $found = (new WorkspaceRepository)->find($workspace);

    if ($found === null) {
        abort(404);
    }

    // The archive is written to a temporary file and handed to the browser;
    // nothing of the export stays on the server once it has been sent.
    $path = (new ThemeArchive)->write($found['themes']);

    return response()
        ->download(
            $path,
            (Str::slug($found['name']) ?: 'themes').'.zip',
            ['Content-Type' => 'application/zip'],
        )
        ->deleteFileAfterSend(true);

})->name('builder.export');

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
