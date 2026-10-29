<?php

declare(strict_types=1);

use App\Livewire\Builder\BuilderCanvas;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;

it('saves a theme', function () {
    Livewire::test(BuilderCanvas::class)
        ->set('themeGroup', 'action')
        ->set('themeVariant', 'primary')
        ->set('themeClasses', 'bg-indigo-600 text-white')
        ->call('saveTheme')
        ->assertSet('themes', [
            'action' => ['primary' => 'bg-indigo-600 text-white'],
        ]);
});

it('shows the themes in the drawer', function () {
    // The themes are no longer a panel of the page: the drawer keeps them,
    // so a theme of the canvas has to be reached through its panel.
    Livewire::test(BuilderCanvas::class)
        ->set('themeGroup', 'action')
        ->set('themeVariant', 'primary')
        ->set('themeClasses', 'bg-indigo-600 text-white')
        ->call('saveTheme')
        ->call('openThemes')
        ->assertSee('bg-indigo-600 text-white', false)
        ->assertSee('data-open="true"', false);
});

it('refuses a theme with a name that cannot be used', function () {
    Livewire::test(BuilderCanvas::class)
        ->set('themeGroup', '../etc')
        ->set('themeVariant', 'primary')
        ->set('themeClasses', 'bg-indigo-600')
        ->call('saveTheme')
        ->assertSet('themes', []);
});

it('says why a theme was refused', function () {
    Livewire::test(BuilderCanvas::class)
        ->set('themeGroup', '../etc')
        ->set('themeVariant', 'primary')
        ->set('themeClasses', 'bg-indigo-600')
        ->call('saveTheme')
        ->assertSet('notice', ['surface' => 'theme', 'message' => 'A group and a variant may only hold letters, numbers, dashes and underscores.']);
});

it('says why a theme with no classes was refused', function () {
    Livewire::test(BuilderCanvas::class)
        ->set('themeGroup', 'action')
        ->set('themeVariant', 'primary')
        ->set('themeClasses', '   ')
        ->call('saveTheme')
        ->assertSet('notice', ['surface' => 'theme', 'message' => 'A theme needs at least one class.']);
});

it('keeps the classes of a theme when the new ones are not classes', function () {
    Livewire::test(BuilderCanvas::class)
        ->set('themeGroup', 'action')
        ->set('themeVariant', 'primary')
        ->set('themeClasses', 'bg-indigo-600')
        ->call('saveTheme')
        ->call('updateThemeClasses', 'action', 'primary', '???')
        ->assertSet('themes', ['action' => ['primary' => 'bg-indigo-600']])
        ->assertSet('notice', ['surface' => 'theme', 'message' => 'A theme needs at least one class.']);
});

it('applies a theme to a node', function () {
    Livewire::test(BuilderCanvas::class)
        ->call('add', 'inline.button')
        ->set('themeGroup', 'action')
        ->set('themeVariant', 'primary')
        ->set('themeClasses', 'bg-indigo-600')
        ->call('saveTheme')
        ->call('toggleTheme', 'n2', 'action', 'primary')
        ->assertSet('tree.nodes.n2.theme', ['action' => ['primary']]);
});

it('applies several variants of one group to a node', function () {
    Livewire::test(BuilderCanvas::class)
        ->call('add', 'inline.button')
        ->set('themeGroup', 'padding')
        ->set('themeVariant', 'top-sm')
        ->set('themeClasses', 'pt-3')
        ->call('saveTheme')
        ->set('themeGroup', 'padding')
        ->set('themeVariant', 'bottom-sm')
        ->set('themeClasses', 'pb-3')
        ->call('saveTheme')
        ->call('toggleTheme', 'n2', 'padding', 'top-sm')
        ->call('toggleTheme', 'n2', 'padding', 'bottom-sm')
        ->assertSet('tree.nodes.n2.theme', ['padding' => ['top-sm', 'bottom-sm']]);
});

it('takes a theme off a node', function () {
    Livewire::test(BuilderCanvas::class)
        ->call('add', 'inline.button')
        ->set('themeGroup', 'action')
        ->set('themeVariant', 'primary')
        ->set('themeClasses', 'bg-indigo-600')
        ->call('saveTheme')
        ->call('toggleTheme', 'n2', 'action', 'primary')
        ->call('removeNodeTheme', 'n2', 'action', 'primary')
        ->assertSet('tree.nodes.n2.theme', []);
});

it('takes one variant off a node and leaves the others', function () {
    Livewire::test(BuilderCanvas::class)
        ->call('add', 'inline.button')
        ->set('themeGroup', 'padding')
        ->set('themeVariant', 'top-sm')
        ->set('themeClasses', 'pt-3')
        ->call('saveTheme')
        ->set('themeGroup', 'padding')
        ->set('themeVariant', 'bottom-sm')
        ->set('themeClasses', 'pb-3')
        ->call('saveTheme')
        ->call('toggleTheme', 'n2', 'padding', 'top-sm')
        ->call('toggleTheme', 'n2', 'padding', 'bottom-sm')
        ->call('removeNodeTheme', 'n2', 'padding', 'top-sm')
        ->assertSet('tree.nodes.n2.theme', ['padding' => ['bottom-sm']]);
});

it('toggles a theme off a node when it is applied again', function () {
    Livewire::test(BuilderCanvas::class)
        ->call('add', 'inline.button')
        ->set('themeGroup', 'action')
        ->set('themeVariant', 'primary')
        ->set('themeClasses', 'bg-indigo-600')
        ->call('saveTheme')
        ->call('toggleTheme', 'n2', 'action', 'primary')
        ->call('toggleTheme', 'n2', 'action', 'primary')
        ->assertSet('tree.nodes.n2.theme', []);
});

it('keeps a theme that a node is still using', function () {
    Livewire::test(BuilderCanvas::class)
        ->call('add', 'inline.button')
        ->set('themeGroup', 'action')
        ->set('themeVariant', 'primary')
        ->set('themeClasses', 'bg-indigo-600')
        ->call('saveTheme')
        ->call('toggleTheme', 'n2', 'action', 'primary')
        ->call('deleteTheme', 'action', 'primary')
        ->assertSet('themes', [
            'action' => ['primary' => 'bg-indigo-600'],
        ]);
});

it('removes a theme that nothing uses', function () {
    Livewire::test(BuilderCanvas::class)
        ->set('themeGroup', 'action')
        ->set('themeVariant', 'primary')
        ->set('themeClasses', 'bg-indigo-600')
        ->call('saveTheme')
        ->call('deleteTheme', 'action', 'primary')
        ->assertSet('themes', []);
});

it('says why a theme that is still used cannot be removed', function () {
    Livewire::test(BuilderCanvas::class)
        ->call('add', 'inline.button')
        ->set('themeGroup', 'action')
        ->set('themeVariant', 'primary')
        ->set('themeClasses', 'bg-indigo-600')
        ->call('saveTheme')
        ->call('toggleTheme', 'n2', 'action', 'primary')
        ->call('deleteTheme', 'action', 'primary')
        ->assertSet('notice', ['surface' => 'theme', 'message' => 'This theme is still used by a node, so it cannot be removed.'])
        ->assertSet('themes', ['action' => ['primary' => 'bg-indigo-600']]);
});

it('clears the theme notice when a theme is saved', function () {
    Livewire::test(BuilderCanvas::class)
        ->set('themeGroup', '../etc')
        ->set('themeVariant', 'primary')
        ->set('themeClasses', 'bg-indigo-600')
        ->call('saveTheme')
        ->assertSet('notice', ['surface' => 'theme', 'message' => 'A group and a variant may only hold letters, numbers, dashes and underscores.'])
        ->set('themeGroup', 'action')
        ->set('themeVariant', 'primary')
        ->call('saveTheme')
        ->assertSet('notice', null);
});

it('writes the themes into the generated code', function () {
    $component = Livewire::test(BuilderCanvas::class)
        ->call('add', 'inline.button')
        ->set('themeGroup', 'action')
        ->set('themeVariant', 'primary')
        ->set('themeClasses', 'bg-indigo-600 text-white')
        ->call('saveTheme')
        ->call('toggleTheme', 'n2', 'action', 'primary');

    expect($component->instance()->code())
        ->toContain('->setThemes([')
        ->toContain("'action' => 'primary',")
        ->toContain('resources/views/_themes/tailwind/action.blade.php');
});

it('keeps the themes when it is reset', function () {
    Livewire::test(BuilderCanvas::class)
        ->set('themeGroup', 'action')
        ->set('themeVariant', 'primary')
        ->set('themeClasses', 'bg-indigo-600')
        ->call('saveTheme')
        ->call('resetCanvas')
        ->assertSet('themes', [
            'action' => ['primary' => 'bg-indigo-600'],
        ]);
});

afterEach(function () {
    foreach (Storage::disk('local')->files(builderTestDirectory()) as $file) {
        Storage::disk('local')->delete($file);
    }
});
