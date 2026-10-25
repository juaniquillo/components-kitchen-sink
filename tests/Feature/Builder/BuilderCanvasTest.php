<?php

declare(strict_types=1);

use App\Builder\Contracts\BuilderNode;
use App\Builder\Support\CompositionRepository;
use App\Livewire\Builder\BuilderCanvas;
use Illuminate\Support\Facades\Storage;
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

it('refuses a text segment in a node that does not take text', function () {
    // A list only holds items, so a run of text in one would be hoisted out by
    // the browser and the canvas would stop matching the tree.
    Livewire::test(BuilderCanvas::class)
        ->call('add', 'lists.ul')
        ->call('addText', 'n2', 'Item')
        ->assertSet('tree.nodes.n2.children', [])
        ->assertSet('tree.nodes', fn (array $nodes): bool => ! array_key_exists('n3', $nodes));
});

it('adds text to a component that only takes text from the drawer', function () {
    Livewire::test(BuilderCanvas::class)
        ->call('add', 'form.textarea')
        ->call('openConfig', 'n2')
        ->set('textDraft', 'Hello')
        ->call('addTextToConfiguring')
        ->assertSet('tree.nodes.n3.type', 'text')
        ->assertSet('tree.nodes.n3.text', 'Hello')
        ->assertSet('tree.nodes.n2.children', ['n3'])
        ->assertSet('textDraft', '');
});

it('refuses the drawer text in a node that does not take text', function () {
    Livewire::test(BuilderCanvas::class)
        ->call('add', 'lists.ul')
        ->call('openConfig', 'n2')
        ->set('textDraft', 'Item')
        ->call('addTextToConfiguring')
        ->assertSet('tree.nodes.n2.children', []);
});

it('ignores the drawer text when the drawer is closed', function () {
    Livewire::test(BuilderCanvas::class)
        ->set('textDraft', 'Hello')
        ->call('addTextToConfiguring')
        ->assertSet('tree.nodes.n1.children', []);
});

it('clears the text draft when another node is configured', function () {
    // The draft belongs to the node it was typed for, so it may not end up
    // in the next one.
    Livewire::test(BuilderCanvas::class)
        ->call('add', 'paragraph')
        ->set('textDraft', 'Hello')
        ->call('openConfig', 'n1')
        ->assertSet('textDraft', '');
});

it('keeps the text draft when the same node is configured again', function () {
    Livewire::test(BuilderCanvas::class)
        ->call('add', 'paragraph')
        ->set('textDraft', 'Hello')
        ->call('openConfig', 'n2')
        ->assertSet('textDraft', 'Hello');
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

it('says why an attribute was refused', function () {
    Livewire::test(BuilderCanvas::class)
        ->call('add', 'div')
        ->call('openConfig', 'n2')
        ->set('attributeName', 'onclick')
        ->set('attributeValue', 'alert(1)')
        ->call('addAttribute')
        ->assertSet('attributeNotice', 'That attribute is not allowed.');
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
        ->assertSet('attributeNotice', null)
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
        ->assertSet('themeNotice', 'A group and a variant may only hold letters, numbers, dashes and underscores.');
});

it('says why a theme with no classes was refused', function () {
    Livewire::test(BuilderCanvas::class)
        ->set('themeGroup', 'action')
        ->set('themeVariant', 'primary')
        ->set('themeClasses', '   ')
        ->call('saveTheme')
        ->assertSet('themeNotice', 'A theme needs at least one class.');
});

it('keeps the classes of a theme when the new ones are not classes', function () {
    Livewire::test(BuilderCanvas::class)
        ->set('themeGroup', 'action')
        ->set('themeVariant', 'primary')
        ->set('themeClasses', 'bg-indigo-600')
        ->call('saveTheme')
        ->call('updateThemeClasses', 'action', 'primary', '???')
        ->assertSet('themes', ['action' => ['primary' => 'bg-indigo-600']])
        ->assertSet('themeNotice', 'A theme needs at least one class.');
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
        ->assertSet('themeNotice', 'This theme is still used by a node, so it cannot be removed.')
        ->assertSet('themes', ['action' => ['primary' => 'bg-indigo-600']]);
});

it('clears the theme notice when a theme is saved', function () {
    Livewire::test(BuilderCanvas::class)
        ->set('themeGroup', '../etc')
        ->set('themeVariant', 'primary')
        ->set('themeClasses', 'bg-indigo-600')
        ->call('saveTheme')
        ->assertSet('themeNotice', 'A group and a variant may only hold letters, numbers, dashes and underscores.')
        ->set('themeGroup', 'action')
        ->set('themeVariant', 'primary')
        ->call('saveTheme')
        ->assertSet('themeNotice', null);
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

it('brings the themes back with a saved composition', function () {
    config()->set('builder.directory', 'builder-tests');

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
    config()->set('builder.directory', 'builder-tests');

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
    config()->set('builder.directory', 'builder-tests');

    (new CompositionRepository)->save('Simple', BuilderNode::tree('n1', 'div'));

    Livewire::test(BuilderCanvas::class)
        ->call('add', 'paragraph')
        ->assertSet('configuringId', 'n2')
        ->call('load', 'simple')
        ->assertSet('configuringId', null);
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
    config()->set('builder.directory', 'builder-tests');

    Livewire::test(BuilderCanvas::class)
        ->call('add', 'paragraph')
        ->set('compositionName', 'Hero')
        ->call('save')
        ->assertSee('data-dirty="false"', false);
});

it('is clean again after a composition is loaded', function () {
    config()->set('builder.directory', 'builder-tests');

    (new CompositionRepository)->save('Simple', BuilderNode::tree('n1', 'div'));

    Livewire::test(BuilderCanvas::class)
        ->call('add', 'paragraph')
        ->call('load', 'simple')
        ->assertSee('data-dirty="false"', false);
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
    foreach (Storage::disk('local')->files('builder-tests') as $file) {
        Storage::disk('local')->delete($file);
    }
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
        ->call('add', 'headers.h1');

    preg_match('/<dialog.*?<\/dialog>/s', $component->html(), $matches);

    expect($matches[0] ?? '')->toContain('<h1');
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

it('offers a table in the menu of the root', function () {
    Livewire::test(BuilderCanvas::class)
        ->assertSee('Add table…', false)
        ->assertSee('tableMode(', false);
});

it('opens the table form for a node that takes a table', function () {
    Livewire::test(BuilderCanvas::class)
        ->call('tableMode', 'n1')
        ->assertSet('tableModeId', 'n1')
        ->assertSet('newTableColumns', 3)
        ->assertSet('newTableRows', 2)
        ->assertSee('data-open="true"', false);
});

it('refuses the table form for a node that cannot hold a table', function () {
    Livewire::test(BuilderCanvas::class)
        ->call('add', 'paragraph')
        ->call('tableMode', 'n2')
        ->assertSet('tableModeId', null);
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

it('scaffolds a whole table under the node the form was opened for', function () {
    Livewire::test(BuilderCanvas::class)
        ->call('tableMode', 'n1')
        ->call('scaffoldTable')
        ->assertSet('tableModeId', null)
        ->assertSet('tableNotice', null)
        ->assertSet('tree.nodes.n2.type', 'table.table')
        ->assertSet('tree.nodes.n3.children', ['n4'])
        ->assertSet('tree.nodes.n4.children', ['n6', 'n8', 'n10'])
        ->assertSet('tree.nodes.n5.children', ['n12', 'n19']);
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

it('offers the table operations in the menus of the parts', function () {
    Livewire::test(BuilderCanvas::class)
        ->call('tableMode', 'n1')
        ->call('scaffoldTable')
        ->assertSee('Row below', false)
        ->assertSee('Delete column', false);
});

it('copies a row in the canvas below the one it was asked about', function () {
    Livewire::test(BuilderCanvas::class)
        ->call('tableMode', 'n1')
        ->call('scaffoldTable')
        ->call('addRowBelow', 'n12')
        ->assertSet('tree.nodes.n5.children', ['n12', 'n26', 'n19'])
        ->assertSet('tree.nodes.n26.type', 'table.tr')
        ->assertSet('tree.nodes.n27.children', []);
});

it('adds a column from a row of the canvas', function () {
    Livewire::test(BuilderCanvas::class)
        ->call('tableMode', 'n1')
        ->call('scaffoldTable')
        ->call('addColumn', 'n12')
        ->assertSet('tree.nodes.n4.children', ['n6', 'n8', 'n10', 'n26'])
        ->assertSet('tree.nodes.n12.children', ['n13', 'n15', 'n17', 'n28'])
        ->assertSet('tree.nodes.n19.children', ['n20', 'n22', 'n24', 'n30']);
});

it('removes a column from the canvas when a cell asks', function () {
    Livewire::test(BuilderCanvas::class)
        ->call('tableMode', 'n1')
        ->call('scaffoldTable')
        ->call('deleteColumn', 'n15')
        ->assertSet('tree.nodes.n4.children', ['n6', 'n10'])
        ->assertSet('tree.nodes.n12.children', ['n13', 'n17'])
        ->assertSet('tree.nodes.n19.children', ['n20', 'n24'])
        ->assertSet('tableNotice', null);
});

it('refuses a column while a cell of the table spans rows or columns', function () {
    Livewire::test(BuilderCanvas::class)
        ->call('tableMode', 'n1')
        ->call('scaffoldTable')
        ->call('openConfig', 'n13')
        ->set('attributeName', 'colspan')
        ->set('attributeValue', '2')
        ->call('addAttribute')
        ->call('deleteColumn', 'n15')
        ->assertSet('tree.nodes.n4.children', ['n6', 'n8', 'n10'])
        ->assertSet('tableNotice', 'This table cannot lose a column while one of its cells spans rows or columns.');
});

it('refuses a head the table already holds', function () {
    $component = Livewire::test(BuilderCanvas::class)
        ->call('tableMode', 'n1')
        ->call('scaffoldTable')
        ->assertSet('tableNotice', null);

    $before = count($component->instance()->tree['nodes']);

    $component
        ->call('add', 'table.thead', 'n2')
        ->assertSet('tableNotice', 'This table already has a head.')
        ->call('add', 'table.tbody', 'n2')
        ->assertSet('tableNotice', 'This table already has a body.');

    $tree = $component->instance()->tree;

    expect(count($tree['nodes']))->toBe($before)
        ->and(count(array_filter($tree['nodes'], fn ($node): bool => $node['type'] === 'table.thead')))->toBe(1);
});

it('takes a foot the table does not hold yet', function () {
    $component = Livewire::test(BuilderCanvas::class)
        ->call('tableMode', 'n1')
        ->call('scaffoldTable')
        ->call('add', 'table.tfoot', 'n2');

    $tree = $component->instance()->tree;
    $table = $tree['nodes']['n2'];

    expect($component->instance()->tableNotice)->toBeNull()
        ->and(count($table['children']))->toBe(3)
        ->and($tree['nodes'][end($table['children'])]['type'])->toBe('table.tfoot');
});
