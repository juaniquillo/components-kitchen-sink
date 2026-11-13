<?php

declare(strict_types=1);

use App\Livewire\Builder\BuilderCanvas;
use App\Livewire\Builder\DrawerMode;
use Livewire\Livewire;

it('offers details in the menu of the root', function () {
    Livewire::test(BuilderCanvas::class)
        ->assertSee('Add details…', false)
        ->assertSee("scaffoldMode('details'", false);
});

it('offers the details form from the palette', function () {
    // The palette has no node of its own, so the form goes where every
    // other palette entry goes: the root, when nothing is selected.
    Livewire::test(BuilderCanvas::class)
        ->assertSee('wire:click="scaffoldMode', false)
        ->call('scaffoldMode', 'details')
        ->assertSet('drawer', DrawerMode::SCAFFOLD)
        ->assertSet('scaffoldKey', 'details')
        ->assertSet('drawerTarget', 'n1');
});

it('puts a palette details in the selected container', function () {
    Livewire::test(BuilderCanvas::class)
        ->call('add', 'div')
        ->call('scaffoldMode', 'details')
        ->assertSet('drawerTarget', 'n2');
});

it('refuses the details form for a node that cannot hold details', function () {
    // The paragraph is what the drawer was already showing, and the refused
    // form does not take its place.
    Livewire::test(BuilderCanvas::class)
        ->call('add', 'paragraph')
        ->call('scaffoldMode', 'details', 'n2')
        ->assertSet('drawer', DrawerMode::NODE)
        ->assertSet('drawerTarget', 'n2');
});

it('opens the details form for a node that takes details', function () {
    Livewire::test(BuilderCanvas::class)
        ->call('scaffoldMode', 'details', 'n1')
        ->assertSet('drawer', DrawerMode::SCAFFOLD)
        ->assertSet('scaffoldKey', 'details')
        ->assertSet('drawerTarget', 'n1')
        ->assertSet('scaffoldValues', ['summary' => '', 'open' => false])
        ->assertSee('data-open="true"', false);
});

it('scaffolds a details with an empty summary under the node the form was opened for', function () {
    Livewire::test(BuilderCanvas::class)
        ->call('scaffoldMode', 'details', 'n1')
        ->call('runScaffold')
        ->assertSet('drawer', null)
        ->assertSet('notice', null)
        ->assertSet('tree.nodes.n2.type', 'details.details')
        ->assertSet('tree.nodes.n2.attrs', [])
        ->assertSet('tree.nodes.n2.children', ['n3'])
        ->assertSet('tree.nodes.n3.type', 'details.summary')
        ->assertSet('tree.nodes.n3.children', []);
});

it('scaffolds a details with the summary and openness asked for', function () {
    Livewire::test(BuilderCanvas::class)
        ->call('scaffoldMode', 'details', 'n1')
        ->set('scaffoldValues.summary', 'Shipping')
        ->set('scaffoldValues.open', true)
        ->call('runScaffold')
        ->assertSet('drawer', null)
        ->assertSet('notice', null)
        ->assertSet('tree.nodes.n2.type', 'details.details')
        ->assertSet('tree.nodes.n2.attrs', ['open' => 'open'])
        ->assertSet('tree.nodes.n2.children', ['n3'])
        ->assertSet('tree.nodes.n3.children', ['n4'])
        ->assertSet('tree.nodes.n4.text', 'Shipping');
});

it('adds a summary with a plain details', function () {
    // A details without a summary shows the browser's own "Details"
    // label, so the entry brings an empty one, ready for the text entry.
    Livewire::test(BuilderCanvas::class)
        ->call('add', 'details.details')
        ->assertSet('tree.nodes.n2.type', 'details.details')
        ->assertSet('tree.nodes.n2.children', ['n3'])
        ->assertSet('tree.nodes.n3.type', 'details.summary')
        ->assertSee('open="open"', false);
});

it('generates the details without the canvas-only open state', function () {
    $component = Livewire::test(BuilderCanvas::class)
        ->call('add', 'details.details');

    $code = $component->instance()->code();

    expect($code)->toContain('ComponentEnum::DETAILS')
        ->toContain('ComponentEnum::SUMMARY')
        ->not->toContain('open');
});
