<?php

declare(strict_types=1);

use App\Livewire\Builder\BuilderCanvas;
use Livewire\Features\SupportLockedProperties\CannotUpdateLockedPropertyException;
use Livewire\Livewire;

it('refuses a property the canvas owns', function (string $property, mixed $value) {
    // The canvas is the only writer of its own state: a request names the
    // action it wants, and the action decides what the tree becomes. A
    // property that could be posted straight in would let a crafted request
    // put its own markup on the canvas.
    expect(fn () => Livewire::test(BuilderCanvas::class)->set($property, $value))
        ->toThrow(CannotUpdateLockedPropertyException::class);
})->with([
    'tree' => ['tree', ['root' => 'n1', 'nodes' => []]],
    'themes' => ['themes', ['action' => ['primary' => 'bg-red-600']]],
    'selection' => ['selectedId', 'n9'],
    'id counter' => ['nodeCounter', 99],
    'fingerprint' => ['fingerprint', 'forged'],
    'workspace' => ['workspace', '01ARZ3NDEKTSV4RRFFQ69G5FAV'],
]);

it('still lets the name be typed into', function () {
    // The name is the one thing the header edits directly, so it is the one
    // property that has to stay writable from the browser.
    Livewire::test(BuilderCanvas::class)
        ->set('name', 'Pricing')
        ->assertSet('name', 'Pricing');
});
