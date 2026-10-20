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

it('adds a text segment to a node', function () {
    Livewire::test(BuilderCanvas::class)
        ->call('add', 'paragraph')
        ->call('addText', 'n2', 'Hello')
        ->assertSet('tree.nodes.n3.type', 'text')
        ->assertSet('tree.nodes.n3.text', 'Hello')
        ->assertSet('tree.nodes.n2.children', ['n3']);
});

it('changes a text segment', function () {
    Livewire::test(BuilderCanvas::class)
        ->call('add', 'paragraph')
        ->call('addText', 'n2', 'Hello')
        ->call('updateText', 'n3', 'Goodbye')
        ->assertSet('tree.nodes.n3.text', 'Goodbye');
});

it('removes a text segment', function () {
    Livewire::test(BuilderCanvas::class)
        ->call('add', 'paragraph')
        ->call('addText', 'n2', 'Hello')
        ->call('removeText', 'n3')
        ->assertSet('tree.nodes.n2.children', [])
        ->assertSet('tree.nodes', fn (array $nodes): bool => ! array_key_exists('n3', $nodes));
});

it('ignores an empty text segment', function () {
    Livewire::test(BuilderCanvas::class)
        ->call('add', 'paragraph')
        ->call('addText', 'n2', '   ')
        ->assertSet('tree.nodes.n2.children', []);
});

it('adds an attribute to the node being configured', function () {
    Livewire::test(BuilderCanvas::class)
        ->call('add', 'div')
        ->call('openConfig', 'n2')
        ->set('attributeName', 'id')
        ->set('attributeValue', 'hero')
        ->call('addAttribute')
        ->assertSet('tree.nodes.n2.attrs', ['id' => 'hero'])
        ->assertSet('attributeName', '')
        ->assertSet('attributeValue', '');
});

it('refuses an attribute the guard blocks', function () {
    Livewire::test(BuilderCanvas::class)
        ->call('add', 'div')
        ->call('openConfig', 'n2')
        ->set('attributeName', 'onclick')
        ->set('attributeValue', 'alert(1)')
        ->call('addAttribute')
        ->assertSet('tree.nodes.n2.attrs', []);
});

it('ignores an attribute with no name', function () {
    Livewire::test(BuilderCanvas::class)
        ->call('add', 'div')
        ->call('openConfig', 'n2')
        ->set('attributeName', '   ')
        ->set('attributeValue', 'hero')
        ->call('addAttribute')
        ->assertSet('tree.nodes.n2.attrs', []);
});

it('loads an attribute back into the inputs to edit it', function () {
    Livewire::test(BuilderCanvas::class)
        ->call('add', 'div')
        ->call('openConfig', 'n2')
        ->set('attributeName', 'id')
        ->set('attributeValue', 'hero')
        ->call('addAttribute')
        ->call('editAttribute', 'id')
        ->assertSet('attributeName', 'id')
        ->assertSet('attributeValue', 'hero')
        ->assertSet('editingAttribute', 'id');
});

it('updates an attribute that is being edited', function () {
    Livewire::test(BuilderCanvas::class)
        ->call('add', 'div')
        ->call('openConfig', 'n2')
        ->set('attributeName', 'id')
        ->set('attributeValue', 'hero')
        ->call('addAttribute')
        ->call('editAttribute', 'id')
        ->set('attributeValue', 'banner')
        ->call('addAttribute')
        ->assertSet('tree.nodes.n2.attrs', ['id' => 'banner'])
        ->assertSet('editingAttribute', null);
});

it('deletes an attribute', function () {
    Livewire::test(BuilderCanvas::class)
        ->call('add', 'div')
        ->call('openConfig', 'n2')
        ->set('attributeName', 'id')
        ->set('attributeValue', 'hero')
        ->call('addAttribute')
        ->call('deleteAttribute', 'id')
        ->assertSet('tree.nodes.n2.attrs', []);
});

it('empties the attribute inputs when the drawer is opened', function () {
    Livewire::test(BuilderCanvas::class)
        ->call('add', 'div')
        ->call('openConfig', 'n2')
        ->set('attributeName', 'id')
        ->set('attributeValue', 'hero')
        ->call('addAttribute')
        ->call('editAttribute', 'id')
        ->call('openConfig', 'n2')
        ->assertSet('attributeName', '')
        ->assertSet('attributeValue', '')
        ->assertSet('editingAttribute', null);
});

it('opens the drawer when a text element is added', function () {
    Livewire::test(BuilderCanvas::class)
        ->call('add', 'paragraph')
        ->assertSet('selectedId', 'n2')
        ->assertSet('configuringId', 'n2');
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

it('renders a clean preview without any of the builders additions', function () {
    $component = Livewire::test(BuilderCanvas::class)
        ->call('add', 'headers.h1');

    $clean = $component->instance()->cleanPreview();

    expect($clean)->toContain('<h1')
        ->not->toContain('wire:sort')
        ->not->toContain('data-node-id')
        ->not->toContain('position: relative');
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
