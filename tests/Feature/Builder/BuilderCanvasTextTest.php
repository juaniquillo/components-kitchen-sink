<?php

declare(strict_types=1);

use App\Livewire\Builder\BuilderCanvas;
use Livewire\Livewire;

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

it('opens the drawer when a text element is added', function () {
    Livewire::test(BuilderCanvas::class)
        ->call('add', 'paragraph')
        ->assertSet('selectedId', 'n2')
        ->assertSet('configuringId', 'n2');
});
