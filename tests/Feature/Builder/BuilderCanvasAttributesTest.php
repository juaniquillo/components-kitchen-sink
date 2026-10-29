<?php

declare(strict_types=1);

use App\Livewire\Builder\BuilderCanvas;
use Livewire\Livewire;

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

it('says why an attribute was refused', function () {
    Livewire::test(BuilderCanvas::class)
        ->call('add', 'div')
        ->call('openConfig', 'n2')
        ->set('attributeName', 'onclick')
        ->set('attributeValue', 'alert(1)')
        ->call('addAttribute')
        ->assertSet('notice', ['surface' => 'attribute', 'message' => 'That attribute is not allowed.']);
});

it('clears the notice once an attribute is added', function () {
    Livewire::test(BuilderCanvas::class)
        ->call('add', 'div')
        ->call('openConfig', 'n2')
        ->set('attributeName', 'onclick')
        ->call('addAttribute')
        ->set('attributeName', 'id')
        ->set('attributeValue', 'hero')
        ->call('addAttribute')
        ->assertSet('notice', null)
        ->assertSet('tree.nodes.n2.attrs', ['id' => 'hero']);
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
