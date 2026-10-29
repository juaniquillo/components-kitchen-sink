<?php

declare(strict_types=1);

use App\Builder\Contracts\BuilderNode;
use App\Builder\Support\CompositionRepository;
use App\Livewire\Builder\BuilderCanvas;
use App\Livewire\Builder\DrawerMode;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;

afterEach(function () {
    foreach (Storage::disk('local')->files(builderTestDirectory()) as $file) {
        Storage::disk('local')->delete($file);
    }
});

it('brings the themes back with a saved composition', function () {
    Livewire::test(BuilderCanvas::class)
        ->call('add', 'inline.button')
        ->set('themeGroup', 'action')
        ->set('themeVariant', 'primary')
        ->set('themeClasses', 'bg-indigo-600')
        ->call('saveTheme')
        ->call('toggleTheme', 'n2', 'action', 'primary')
        ->set('compositionName', 'Themed')
        ->call('save');

    Livewire::test(BuilderCanvas::class)
        ->call('load', 'themed')
        ->assertSet('themes', ['action' => ['primary' => 'bg-indigo-600']])
        ->assertSet('tree.nodes.n2.theme', ['action' => ['primary']]);
});

it('gives a new component an id that the loaded composition is not using', function () {
    // The ids of a saved composition have gaps in them, because a node that
    // was deleted leaves its id behind. There are seven nodes here and the
    // highest id is eight, so the count of the nodes is not enough to know
    // where the next id can start.
    $tree = BuilderNode::tree('n1', 'div');
    $tree['nodes']['n1']['children'] = ['n2'];
    $tree['nodes']['n2'] = BuilderNode::make('n2', 'paragraph');
    $tree['nodes']['n2']['children'] = ['n4', 'n6', 'n7'];
    $tree['nodes']['n4'] = BuilderNode::make('n4', 'inline.span');
    $tree['nodes']['n4']['children'] = ['n5'];
    $tree['nodes']['n5'] = BuilderNode::make('n5', BuilderNode::TYPE_TEXT, 'My Span');
    $tree['nodes']['n6'] = BuilderNode::make('n6', 'inline.img');
    $tree['nodes']['n7'] = BuilderNode::make('n7', 'inline.button');
    $tree['nodes']['n7']['children'] = ['n8'];
    $tree['nodes']['n8'] = BuilderNode::make('n8', BuilderNode::TYPE_TEXT, 'My Button');

    (new CompositionRepository)->save('Gapped', $tree);

    Livewire::test(BuilderCanvas::class)
        ->call('load', 'gapped')
        ->call('add', 'paragraph')
        ->assertSet('tree.nodes.n5.text', 'My Span')
        ->assertSet('tree.nodes.n8.text', 'My Button')
        ->assertSet('tree.nodes.n9.type', 'paragraph')
        ->assertSet('tree.nodes.n1.children', ['n2', 'n9'])
        ->assertSet('tree.nodes.n7.children', ['n8']);
});

it('closes the drawer when a composition is loaded', function () {
    (new CompositionRepository)->save('Simple', BuilderNode::tree('n1', 'div'));

    Livewire::test(BuilderCanvas::class)
        ->call('add', 'paragraph')
        ->assertSet('drawer', DrawerMode::NODE)
        ->assertSet('drawerTarget', 'n2')
        ->call('load', 'simple')
        ->assertSet('drawer', null);
});

it('asks for a name before it saves a composition', function () {
    // A save without a name can only fall back to a name the user never
    // chose, so the drawer is opened on the input that names it instead.
    Livewire::test(BuilderCanvas::class)
        ->call('add', 'paragraph')
        ->call('save')
        ->assertSet('drawer', DrawerMode::SAVED)
        ->assertSet('compositionName', '')
        ->assertSee('data-open="true"', false);

    expect((new CompositionRepository)->all())->toBe([]);
});

it('keeps the saved compositions drawer open while one is loaded', function () {
    (new CompositionRepository)->save('Simple', BuilderNode::tree('n1', 'div'));

    Livewire::test(BuilderCanvas::class)
        ->call('openSaved')
        ->assertSee('Simple', false)
        ->call('load', 'simple')
        ->assertSet('drawer', DrawerMode::SAVED)
        ->assertSee('data-open="true"', false)
        ->assertSet('drawerTarget', null);
});

it('asks before a load replaces work that is not saved', function () {
    // A load swaps the whole tree in, so a canvas that holds work which
    // is not saved has to be asked, the way the reset and the delete of
    // a composition are. A clean canvas has nothing to lose, and the
    // load goes ahead without a word.
    (new CompositionRepository)->save('Simple', BuilderNode::tree('n1', 'div'));

    Livewire::test(BuilderCanvas::class)
        ->call('openSaved')
        ->assertSee("wire:click=\"load('simple')\"", false)
        ->assertDontSee('wire:confirm="Loading a composition will replace the one on the canvas', false);

    Livewire::test(BuilderCanvas::class)
        ->call('add', 'paragraph')
        ->call('openSaved')
        ->assertSee('wire:confirm="Loading a composition will replace the one on the canvas. Are you sure?"', false);
});

it('announces that a save will overwrite an existing composition', function () {
    // A save is found by its name, so a name the directory already holds
    // makes the save an overwrite, and the file it replaces is work the
    // user may have wanted kept. The button asks, and the panel says so
    // while the name is still being typed.
    (new CompositionRepository)->save('Simple', BuilderNode::tree('n1', 'div'));

    Livewire::test(BuilderCanvas::class)
        ->call('openSaved')
        ->assertDontSee('wire:confirm="A composition with this name already exists', false)
        ->assertDontSee('An existing composition with this name will be overwritten.', false)
        ->set('compositionName', 'Simple')
        ->assertSee('wire:confirm="A composition with this name already exists and will be overwritten. Are you sure?"', false)
        ->assertSee('An existing composition with this name will be overwritten.', false);

    // The name is what decides it, not how it is written, so any spelling
    // that turns into the same slug announces the overwrite as well.
    Livewire::test(BuilderCanvas::class)
        ->call('openSaved')
        ->set('compositionName', 'SIMPLE')
        ->assertSee('An existing composition with this name will be overwritten.', false);
});

it('names the canvas after the composition that was loaded', function () {
    // The name the input was still carrying belongs to the composition that
    // was on the canvas before: loading another one makes it wrong, and a
    // save made with it would end up somewhere unexpected.
    (new CompositionRepository)->save('Simple', BuilderNode::tree('n1', 'div'));

    (new CompositionRepository)->save('Other', BuilderNode::tree('n1', 'div'));

    Livewire::test(BuilderCanvas::class)
        ->call('add', 'paragraph')
        ->set('compositionName', 'Simple')
        ->call('load', 'other')
        ->assertSet('compositionName', 'Other');
});

it('is clean when the page opens', function () {
    Livewire::test(BuilderCanvas::class)
        ->assertSee('data-dirty="false"', false);
});

it('is dirty once the composition is changed', function () {
    Livewire::test(BuilderCanvas::class)
        ->call('add', 'paragraph')
        ->assertSee('data-dirty="true"', false);
});

it('is clean again after a save', function () {
    Livewire::test(BuilderCanvas::class)
        ->call('add', 'paragraph')
        ->set('compositionName', 'Hero')
        ->call('save')
        ->assertSee('data-dirty="false"', false);
});

it('is clean again after a composition is loaded', function () {
    (new CompositionRepository)->save('Simple', BuilderNode::tree('n1', 'div'));

    Livewire::test(BuilderCanvas::class)
        ->call('add', 'paragraph')
        ->call('load', 'simple')
        ->assertSee('data-dirty="false"', false);
});

it('renders a clean preview without any of the builders additions', function () {
    $component = Livewire::test(BuilderCanvas::class)
        ->call('add', 'headers.h1');

    $clean = $component->instance()->cleanPreview();

    expect($clean)->toContain('<h1')
        ->not->toContain('wire:sort')
        ->not->toContain('data-node-id')
        ->not->toContain('position: relative');
});

it('renders the preview dialog with ids of its own', function () {
    // The canvas and its preview are on the page at once, and a label
    // points at the first element that carries its `for`. The canvas
    // keeps the ids the composition carries and the dialog gets scoped
    // ones.
    $tree = BuilderNode::tree('n1', 'div');
    $tree['nodes']['n1']['children'] = ['n2', 'n3'];
    $tree['nodes']['n2'] = BuilderNode::make('n2', 'form.label');
    $tree['nodes']['n2']['attrs'] = ['for' => 'name'];
    $tree['nodes']['n3'] = BuilderNode::make('n3', 'form.text');
    $tree['nodes']['n3']['attrs'] = ['id' => 'name'];

    $component = Livewire::test(BuilderCanvas::class)
        ->set('tree', $tree);

    expect($component->instance()->cleanPreview())->toContain('for="bw-preview-name"')
        ->toContain('id="bw-preview-name"');

    $component->assertSee('for="name"', false)
        ->assertSee('id="name"', false);
});

it('knocks the canvas out while the preview is open', function () {
    // The preview dialog takes the label of the canvas and would take its
    // way, so the canvas is made inert while the dialog is up. The binding
    // lives with the state that owns the dialog, and the dialog itself is
    // in the top layer, so ancestor inertness does not reach it.
    Livewire::test(BuilderCanvas::class)
        ->assertSee('x-bind:inert="previewOpen"', false);
});

it('renders the preview with the drag and drop attributes', function () {
    $component = Livewire::test(BuilderCanvas::class)
        ->call('add', 'headers.h1');

    $preview = $component->instance()->preview();

    expect($preview)->toContain('wire:sort:item="n2"')
        ->toContain('wire:sort="handleSort"')
        ->toContain('wire:sort:group="builder"')
        ->toContain('data-node-id="n2"');
});

it('shows the preview inside a dialog', function () {
    $component = Livewire::test(BuilderCanvas::class)
        ->call('add', 'headers.h1')
        ->call('openPreview');

    preg_match('/<dialog.*?<\/dialog>/s', $component->html(), $matches);

    expect($matches[0] ?? '')->toContain('<h1');
});

it('keeps the copy of the composition out of the dialog until it is asked for', function () {
    // The clean preview is a second full render of the tree on every update
    // of the canvas, so the dialog is empty while it is closed and holds
    // the copy only while the user is looking at it.
    $component = Livewire::test(BuilderCanvas::class)
        ->call('add', 'headers.h1');

    preg_match('/<dialog.*?<\/dialog>/s', $component->html(), $matches);

    expect($matches[0] ?? '')->not->toContain('<h1')
        ->and($component->instance()->previewRequested)->toBeFalse();

    $component->call('openPreview');

    expect($component->instance()->previewRequested)->toBeTrue();

    $component->call('closePreview');

    preg_match('/<dialog.*?<\/dialog>/s', $component->html(), $matches);

    expect($matches[0] ?? '')->not->toContain('<h1')
        ->and($component->instance()->previewRequested)->toBeFalse();
});

it('does not bring flux into the builder', function () {
    Livewire::test(BuilderCanvas::class)
        ->assertDontSee('data-flux-modal', false);
});

it('keeps the drawer out of the stack of the panels', function () {
    // The space between the panels is a margin on every child of the stack
    // but the first, and the drawer is an overlay rather than a panel of the
    // page: the margin would push it down.
    $html = Livewire::test(BuilderCanvas::class)->html();

    $dom = new DOMDocument;

    // The parser of the library does not know the html5 tags and says so,
    // but it keeps them all the same.
    @$dom->loadHTML($html);

    $aside = (new DOMXPath($dom))->query('//aside[@aria-label]')->item(0);

    expect($aside)->not->toBeNull()
        ->and($aside->parentNode->getAttribute('class'))->not->toContain('space-y-4');
});
