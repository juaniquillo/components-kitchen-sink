<?php

declare(strict_types=1);

use App\Livewire\Builder\BuilderCanvas;
use Livewire\Livewire;

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

it('clears the selection of a cell the column operation took out', function () {
    // The operation reaches across the rows of the table, so the cell
    // the selection is on can go even though another cell asked for the
    // deletion. A selection left on a node that is not there makes the
    // button that configures it do nothing at all.
    Livewire::test(BuilderCanvas::class)
        ->call('tableMode', 'n1')
        ->call('scaffoldTable')
        ->call('select', 'n22')
        ->call('deleteColumn', 'n15')
        ->assertSet('selectedId', 'n1');
});

it('closes the drawer of a cell the column operation took out', function () {
    Livewire::test(BuilderCanvas::class)
        ->call('tableMode', 'n1')
        ->call('scaffoldTable')
        ->call('openConfig', 'n22')
        ->call('deleteColumn', 'n15')
        ->assertSet('configuringId', null);
});

it('leaves the selection alone when the column is refused', function () {
    Livewire::test(BuilderCanvas::class)
        ->call('tableMode', 'n1')
        ->call('scaffoldTable')
        ->call('openConfig', 'n13')
        ->set('attributeName', 'colspan')
        ->set('attributeValue', '2')
        ->call('addAttribute')
        ->call('select', 'n22')
        ->call('deleteColumn', 'n15')
        ->assertSet('selectedId', 'n22');
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
