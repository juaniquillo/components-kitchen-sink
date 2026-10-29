<?php

declare(strict_types=1);

use App\Livewire\Builder\BuilderCanvas;
use Livewire\Livewire;

it('says why a drop was refused, and takes it away after a move that lands', function () {
    // A paragraph only holds phrasing content, so a div dropped into it is
    // refused with a word instead of snapping back quietly.
    Livewire::test(BuilderCanvas::class)
        ->call('add', 'paragraph')
        ->call('add', 'div')
        ->call('handleSort', 'n3', 0, 'n2')
        ->assertSet('notice', ['surface' => 'canvas', 'message' => 'A Paragraph cannot hold a Div.'])
        ->assertSee('A Paragraph cannot hold a Div.', false)
        ->call('handleSort', 'n3', 0, 'n1')
        ->assertSet('notice', null);
});

it('says why a node cannot be dropped into itself', function () {
    Livewire::test(BuilderCanvas::class)
        ->call('add', 'div')
        ->call('handleSort', 'n2', 0, 'n2')
        ->assertSet('notice', ['surface' => 'canvas', 'message' => 'A node cannot be moved into itself.']);
});

it('says why the root cannot be duplicated', function () {
    Livewire::test(BuilderCanvas::class)
        ->call('openConfig', 'n1')
        ->call('duplicate', 'n1')
        ->assertSet('notice', ['surface' => 'node', 'message' => 'The root of the composition has nowhere to go.'])
        ->assertSee('The root of the composition has nowhere to go.', false);
});

it('does not blame the root when the node is gone', function () {
    Livewire::test(BuilderCanvas::class)
        ->call('openConfig', 'n1')
        ->call('duplicate', 'nope')
        ->assertSet('notice', ['surface' => 'node', 'message' => 'That node is not on the canvas any more.']);
});

it('takes an attribute refusal away when another node is opened', function () {
    Livewire::test(BuilderCanvas::class)
        ->call('add', 'paragraph')
        ->set('attributeName', 'onclick')
        ->set('attributeValue', 'alert(1)')
        ->call('addAttribute')
        ->assertSet('notice', ['surface' => 'attribute', 'message' => 'That attribute is not allowed.'])
        ->call('openConfig', 'n1')
        ->assertSet('notice', null);
});
