<?php

declare(strict_types=1);

use App\Livewire\Builder\BuilderCanvas;
use Livewire\Livewire;

it('starts with an empty div as the root', function () {
    Livewire::test(BuilderCanvas::class)
        ->assertStatus(200)
        ->assertSet('tree.root', 'n1')
        ->assertSet('tree.nodes.n1.type', 'div')
        ->assertSet('tree.nodes.n1.children', []);
});

it('adds a component to the selected node', function () {
    Livewire::test(BuilderCanvas::class)
        ->call('add', 'headers.h1')
        ->assertSet('tree.nodes.n2.type', 'headers.h1')
        ->assertSet('tree.nodes.n1.children', ['n2']);
});

it('adds a component to the parent when the selection cannot hold it', function () {
    Livewire::test(BuilderCanvas::class)
        ->call('add', 'headers.h1')
        ->call('add', 'paragraph')
        ->assertSet('tree.nodes.n3.type', 'paragraph')
        ->assertSet('tree.nodes.n1.children', ['n2', 'n3'])
        ->assertSet('tree.nodes.n2.children', []);
});

it('selects a node', function () {
    Livewire::test(BuilderCanvas::class)
        ->call('add', 'headers.h1')
        ->call('select', 'n1')
        ->assertSet('selectedId', 'n1');
});

it('ignores a selection of a node that does not exist', function () {
    Livewire::test(BuilderCanvas::class)
        ->call('add', 'headers.h1')
        ->call('select', 'nope')
        ->assertSet('selectedId', 'n2');
});

it('moves a node into another parent when a drop is reported', function () {
    Livewire::test(BuilderCanvas::class)
        ->call('add', 'headers.h1')
        ->call('add', 'div')
        ->call('handleSort', 'n2', 0, 'n3')
        ->assertSet('tree.nodes.n1.children', ['n3'])
        ->assertSet('tree.nodes.n3.children', ['n2']);
});

it('reorders nodes within the same parent when a drop is reported', function () {
    Livewire::test(BuilderCanvas::class)
        ->call('add', 'headers.h1')
        ->call('add', 'paragraph')
        ->call('handleSort', 'n3', 0, 'n1')
        ->assertSet('tree.nodes.n1.children', ['n3', 'n2']);
});

it('ignores a drop without a destination', function () {
    Livewire::test(BuilderCanvas::class)
        ->call('add', 'headers.h1')
        ->call('handleSort', 'n2', 0, null)
        ->assertSet('tree.nodes.n1.children', ['n2']);
});

it('removes a node', function () {
    Livewire::test(BuilderCanvas::class)
        ->call('add', 'headers.h1')
        ->call('remove', 'n2')
        ->assertSet('tree.nodes.n1.children', [])
        ->assertSet('tree.nodes', fn (array $nodes): bool => array_keys($nodes) === ['n1']);
});

it('duplicates a node with fresh ids', function () {
    Livewire::test(BuilderCanvas::class)
        ->call('add', 'headers.h1')
        ->call('duplicate', 'n2')
        ->assertSet('tree.nodes.n1.children', ['n2', 'n3'])
        ->assertSet('tree.nodes.n3.type', 'headers.h1');
});

it('moves a node up and down among its siblings', function () {
    Livewire::test(BuilderCanvas::class)
        ->call('add', 'headers.h1')
        ->call('add', 'paragraph')
        ->call('moveUp', 'n3')
        ->assertSet('tree.nodes.n1.children', ['n3', 'n2'])
        ->call('moveDown', 'n3')
        ->assertSet('tree.nodes.n1.children', ['n2', 'n3']);
});

it('closes the drawer when the node it was showing is deleted', function () {
    Livewire::test(BuilderCanvas::class)
        ->call('add', 'paragraph')
        ->call('openConfig', 'n2')
        ->call('remove', 'n2')
        ->assertSet('configuringId', null);
});

it('closes the drawer when a node it was showing is deleted with its parent', function () {
    Livewire::test(BuilderCanvas::class)
        ->call('add', 'paragraph')
        ->call('add', 'inline.link', 'n2')
        ->call('openConfig', 'n3')
        ->call('remove', 'n2')
        ->assertSet('configuringId', null)
        ->assertSet('selectedId', 'n1');
});

it('leaves the drawer closed when a container is added', function () {
    Livewire::test(BuilderCanvas::class)
        ->call('add', 'div')
        ->assertSet('selectedId', 'n2')
        ->assertSet('configuringId', null);
});

it('does not open the drawer when the component could not be added', function () {
    Livewire::test(BuilderCanvas::class)
        ->call('add', 'form.text')
        ->call('add', 'table.td', 'n2')
        ->assertSet('configuringId', null);
});

it('opens the drawer for a node', function () {
    Livewire::test(BuilderCanvas::class)
        ->call('add', 'headers.h1')
        ->call('openConfig', 'n2')
        ->assertSet('configuringId', 'n2')
        ->assertSet('selectedId', 'n2');
});

it('says on the drawer whether it is open', function () {
    // The drawer is closed by the escape key, and the browser has to know
    // whether there is anything to close.
    Livewire::test(BuilderCanvas::class)
        ->assertSee('data-open="false"', false)
        ->call('openConfig', 'n1')
        ->assertSee('data-open="true"', false)
        ->call('closeConfig')
        ->assertSee('data-open="false"', false);
});

it('says on the drawer whether a tool panel is open', function () {
    // The tools open the drawer exactly as the configure work does, since
    // the escape key closes the drawer whichever panel it shows.
    Livewire::test(BuilderCanvas::class)
        ->call('openThemes')
        ->assertSee('data-open="true"', false)
        ->call('closeConfig')
        ->assertSet('drawerMode', null)
        ->assertSee('data-open="false"', false)
        ->call('openSaved')
        ->assertSee('data-open="true"', false);
});

it('opens the drawer on the tool that was asked for', function () {
    Livewire::test(BuilderCanvas::class)
        ->call('openThemes')
        ->assertSet('drawerMode', 'themes')
        ->call('closeConfig')
        ->assertSet('drawerMode', null)
        ->call('openSaved')
        ->assertSet('drawerMode', 'saved');
});

it('gives the tools the whole drawer when a node is asked about', function () {
    // The drawer shows one thing at a time, so a configured node takes
    // the place of the tool panel that was showing.
    Livewire::test(BuilderCanvas::class)
        ->call('openThemes')
        ->call('openConfig', 'n1')
        ->assertSet('drawerMode', null)
        ->assertSet('configuringId', 'n1');
});

it('clears the node of the drawer when a tool is asked for', function () {
    Livewire::test(BuilderCanvas::class)
        ->call('openConfig', 'n1')
        ->call('openSaved')
        ->assertSet('configuringId', null)
        ->assertSet('drawerMode', 'saved');
});

it('closes the drawer', function () {
    Livewire::test(BuilderCanvas::class)
        ->call('add', 'headers.h1')
        ->call('openConfig', 'n2')
        ->call('closeConfig')
        ->assertSet('configuringId', null);
});

it('ignores a request to configure a node that does not exist', function () {
    Livewire::test(BuilderCanvas::class)
        ->call('openConfig', 'nope')
        ->assertSet('configuringId', null);
});

it('configures the selected node when there is no button to press', function () {
    Livewire::test(BuilderCanvas::class)
        ->call('add', 'form.text')
        ->call('configureSelected')
        ->assertSet('configuringId', 'n2');
});

it('generates the PHP that reproduces the tree', function () {
    $component = Livewire::test(BuilderCanvas::class)
        ->call('add', 'headers.h1')
        ->call('addText', 'n2', 'Title');

    expect($component->instance()->code())->toContain('ComponentBuilder::make(ComponentEnum::H1)')
        ->toContain("'Title',");
});

it('copies the code without assuming the clipboard api is there', function () {
    Livewire::test(BuilderCanvas::class)
        ->assertDontSee('x-on:click="navigator.clipboard', false)
        ->assertSee('execCommand', false);
});

it('clears the canvas and the name when it is reset', function () {
    $component = Livewire::test(BuilderCanvas::class)
        ->call('add', 'paragraph')
        ->set('compositionName', 'Hero')
        ->call('resetCanvas')
        ->assertSet('tree.nodes', fn (array $nodes): bool => count($nodes) === 1)
        ->assertSet('compositionName', '')
        ->assertSet('configuringId', null)
        ->assertSee('data-dirty="false"', false);

    $tree = $component->instance()->tree;

    expect($tree['nodes'][$tree['root']]['type'])->toBe('div');
});

it('gives the configuration back when a node is asked about', function () {
    // The drawer shows one thing at a time: the node a user opened on the
    // canvas takes the place of the form the table had.
    Livewire::test(BuilderCanvas::class)
        ->call('tableMode', 'n1')
        ->call('openConfig', 'n1')
        ->assertSet('tableModeId', null)
        ->assertSet('configuringId', 'n1')
        ->assertSee('data-open="true"', false);
});

it('scaffolds the size the form asks for, and nothing smaller', function () {
    // The columns and the rows are clamped between 1 and 99: whatever the
    // browser sends, a table always comes out with at least one of each.
    Livewire::test(BuilderCanvas::class)
        ->call('tableMode', 'n1')
        ->set('newTableColumns', 0)
        ->set('newTableRows', 0)
        ->call('scaffoldTable')
        ->assertSet('tree.nodes.n4.children', ['n6'])
        ->assertSet('tree.nodes.n5.children', ['n8']);
});
