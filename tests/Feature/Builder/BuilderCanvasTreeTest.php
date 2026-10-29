<?php

declare(strict_types=1);

use App\Livewire\Builder\BuilderCanvas;
use App\Livewire\Builder\DrawerMode;
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

it('puts the drawer on the copy of the node it duplicated', function () {
    // The copy is what the user is about to work on: duplicating a node
    // and then typing into the drawer would quietly change the original
    // instead, so the drawer follows the copy and the draft of the
    // original does not.
    Livewire::test(BuilderCanvas::class)
        ->call('add', 'paragraph')
        ->set('textDraft', 'still typing')
        ->call('duplicate', 'n2')
        ->assertSet('selectedId', 'n3')
        ->assertSet('drawer', DrawerMode::NODE)
        ->assertSet('drawerTarget', 'n3')
        ->assertSet('textDraft', '')
        ->assertSet('tree.nodes.n1.children', ['n2', 'n3']);
});

it('refuses to copy the root, and remembers nothing to undo', function () {
    // The root has nowhere to go, so the copy is refused before any id is
    // handed out: nothing to select, nothing to configure, and nothing for
    // the undo to bring back.
    $component = Livewire::test(BuilderCanvas::class)
        ->call('duplicate', 'n1');

    $instance = $component->instance();

    expect(array_keys($instance->tree['nodes']))->toBe(['n1'])
        ->and($instance->selectedId)->toBe('n1')
        ->and($instance->canUndo())->toBeFalse();
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
        ->assertSet('drawer', null);
});

it('closes the drawer when a node it was showing is deleted with its parent', function () {
    Livewire::test(BuilderCanvas::class)
        ->call('add', 'paragraph')
        ->call('add', 'inline.link', 'n2')
        ->call('openConfig', 'n3')
        ->call('remove', 'n2')
        ->assertSet('drawer', null)
        ->assertSet('selectedId', 'n1');
});

it('leaves the drawer closed when a container is added', function () {
    Livewire::test(BuilderCanvas::class)
        ->call('add', 'div')
        ->assertSet('selectedId', 'n2')
        ->assertSet('drawer', null);
});

it('does not open the drawer when the component could not be added', function () {
    Livewire::test(BuilderCanvas::class)
        ->call('add', 'form.text')
        ->call('add', 'table.td', 'n2')
        ->assertSet('drawer', null);
});

it('opens the drawer for a node', function () {
    Livewire::test(BuilderCanvas::class)
        ->call('add', 'headers.h1')
        ->call('openConfig', 'n2')
        ->assertSet('drawer', DrawerMode::NODE)
        ->assertSet('drawerTarget', 'n2')
        ->assertSet('selectedId', 'n2');
});

it('says when the canvas is empty', function () {
    // A fresh page holds nothing but its root, so it says what to do
    // instead of leaving a blank frame. The hint is a sibling of the
    // preview and never a part of the tree.
    Livewire::test(BuilderCanvas::class)
        ->assertSee('The canvas is empty.', false)
        ->call('add', 'paragraph')
        ->assertDontSee('The canvas is empty.', false);
});

it('takes the empty hint away with the reset as well', function () {
    Livewire::test(BuilderCanvas::class)
        ->call('add', 'paragraph')
        ->assertDontSee('The canvas is empty.', false)
        ->call('resetCanvas')
        ->assertSee('The canvas is empty.', false);
});

it('holds a native save confirmation on the canvas', function () {
    // The save tells the page it happened, and the confirmation is a
    // native popover waiting for it: the browser owns the top layer and
    // the close button, and only the timer that dismisses it is script.
    Livewire::test(BuilderCanvas::class)
        ->assertSee('popover="manual"', false)
        ->assertSee('popovertarget="builder-saved-toast"', false)
        ->assertSee('popovertargetaction="hide"', false)
        ->assertSee('x-on:builder-saved.window', false);
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
        ->assertSet('drawer', null)
        ->assertSee('data-open="false"', false);
});

it('opens the drawer on the tool that was asked for', function () {
    Livewire::test(BuilderCanvas::class)
        ->call('openThemes')
        ->assertSet('drawer', DrawerMode::THEMES)
        ->call('closeConfig')
        ->assertSet('drawer', null)
        ->call('openThemes')
        ->assertSet('drawer', DrawerMode::THEMES);
});

it('gives the tools the whole drawer when a node is asked about', function () {
    // The drawer shows one thing at a time, so a configured node takes
    // the place of the tool panel that was showing.
    Livewire::test(BuilderCanvas::class)
        ->call('openThemes')
        ->call('openConfig', 'n1')
        ->assertSet('drawer', DrawerMode::NODE)
        ->assertSet('drawerTarget', 'n1');
});

it('clears the node of the drawer when a tool is asked for', function () {
    Livewire::test(BuilderCanvas::class)
        ->call('openConfig', 'n1')
        ->call('openThemes')
        ->assertSet('drawer', DrawerMode::THEMES)
        ->assertSet('drawerTarget', null);
});

it('closes the drawer', function () {
    Livewire::test(BuilderCanvas::class)
        ->call('add', 'headers.h1')
        ->call('openConfig', 'n2')
        ->call('closeConfig')
        ->assertSet('drawer', null);
});

it('ignores a request to configure a node that does not exist', function () {
    Livewire::test(BuilderCanvas::class)
        ->call('openConfig', 'nope')
        ->assertSet('drawer', null);
});

it('configures the selected node when there is no button to press', function () {
    Livewire::test(BuilderCanvas::class)
        ->call('add', 'form.text')
        ->call('configureSelected')
        ->assertSet('drawer', DrawerMode::NODE)
        ->assertSet('drawerTarget', 'n2');
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

it('clears the canvas and keeps the name when it is reset', function () {
    // The name belongs to the workspace, not to the canvas: a reset starts a
    // new composition in the same workspace.
    $component = Livewire::test(BuilderCanvas::class)
        ->call('add', 'paragraph')
        ->set('name', 'Hero')
        ->call('resetCanvas')
        ->assertSet('tree.nodes', fn (array $nodes): bool => count($nodes) === 1)
        ->assertSet('name', 'Hero')
        ->assertSet('drawer', null);

    $tree = $component->instance()->tree;

    expect($tree['nodes'][$tree['root']]['type'])->toBe('div');
});

it('gives the configuration back when a node is asked about', function () {
    // The drawer shows one thing at a time: the node a user opened on the
    // canvas takes the place of the form the table had.
    Livewire::test(BuilderCanvas::class)
        ->call('tableMode', 'n1')
        ->call('openConfig', 'n1')
        ->assertSet('drawer', DrawerMode::NODE)
        ->assertSet('drawerTarget', 'n1')
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
