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

it('turns the runtime tailwind off and loads the page again', function () {
    Livewire::test(BuilderCanvas::class)
        ->call('toggleTailwind')
        ->assertRedirect(route('builder'));

    expect(session('builder.tailwind_runtime'))->toBeFalse();
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
