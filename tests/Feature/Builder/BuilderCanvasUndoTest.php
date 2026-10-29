<?php

declare(strict_types=1);

use App\Builder\Contracts\BuilderNode;
use App\Builder\Support\CompositionRepository;
use App\Livewire\Builder\BuilderCanvas;
use App\Livewire\Builder\DrawerMode;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;

afterEach(function () {
    foreach (Storage::disk('local')->files(builderTestDirectory()) as $file) {
        Storage::disk('local')->delete($file);
    }
});

it('undoes a change and redoes it', function () {
    $component = Livewire::test(BuilderCanvas::class)
        ->call('add', 'paragraph');

    expect($component->instance()->tree['nodes'])->toHaveKey('n2');

    $component->call('undo');

    // The undo brings back the state the add replaced, selection with it.
    $instance = $component->instance();

    expect($instance->tree['nodes'])->not->toHaveKey('n2')
        ->and($instance->selectedId)->toBe('n1');

    $component->call('redo');

    $instance = $component->instance();

    expect($instance->tree['nodes'])->toHaveKey('n2')
        ->and($instance->selectedId)->toBe('n2');
});

it('forgets the redo when a new change is made', function () {
    // A redo over a new change would bring back work the user went another
    // way from, which is not what a redo is for.
    $component = Livewire::test(BuilderCanvas::class)
        ->call('add', 'paragraph')
        ->call('undo')
        ->call('add', 'headers.h1');

    expect($component->instance()->redoStack)->toBe([]);

    $component->call('redo');

    $nodes = $component->instance()->tree['nodes'];

    expect($nodes)->not->toHaveKey('n2')
        ->toHaveKey('n3');
});

it('undoes the themes with the tree', function () {
    $component = Livewire::test(BuilderCanvas::class)
        ->set('themeGroup', 'action')
        ->set('themeVariant', 'primary')
        ->set('themeClasses', 'bg-indigo-600')
        ->call('saveTheme')
        ->call('toggleTheme', 'n1', 'action', 'primary');

    expect($component->instance()->tree['nodes']['n1']['theme'])->toBe(['action' => ['primary']]);

    // The first undo takes the variant off the node, the second takes the
    // theme out of the palette.
    $component->call('undo')->call('undo');

    $instance = $component->instance();

    expect($instance->themes)->toBe([])
        ->and($instance->tree['nodes']['n1']['theme'])->toBe([]);
});

it('does not remember a change that changed nothing', function () {
    // A list item is refused everywhere the selection can put it, so the
    // add is a refusal. An undo that had to step over it before it took
    // the span back would feel like the undo was broken.
    $component = Livewire::test(BuilderCanvas::class)
        ->call('add', 'inline.span')
        ->call('add', 'lists.li')
        ->call('undo');

    $nodes = $component->instance()->tree['nodes'];

    expect($nodes)->not->toHaveKey('n2')
        ->and(count($nodes))->toBe(1)
        ->and($component->instance()->undoStack)->toBe([]);
});

it('keeps the drawer on a node the undo leaves in place', function () {
    $component = Livewire::test(BuilderCanvas::class)
        ->call('add', 'paragraph')
        ->call('addText', 'n1', 'Hello');

    expect($component->instance()->drawer)->toBe(DrawerMode::Node)
        ->and($component->instance()->drawerTarget)->toBe('n2');

    $component->call('undo');

    // The text went, but the node the drawer is on survived it.
    expect($component->instance()->drawer)->toBe(DrawerMode::Node)
        ->and($component->instance()->drawerTarget)->toBe('n2')
        ->and($component->instance()->tree['nodes']['n1']['children'])->toBe(['n2']);
});

it('closes the drawer for a node the undo took away', function () {
    $component = Livewire::test(BuilderCanvas::class)
        ->call('add', 'paragraph')
        ->call('add', 'headers.h1');

    expect($component->instance()->drawer)->toBe(DrawerMode::Node)
        ->and($component->instance()->drawerTarget)->toBe('n3');

    $component->call('undo');

    expect($component->instance()->drawer)->toBeNull();
});

it('undoes a load back to the work it replaced', function () {
    (new CompositionRepository)->save('Simple', BuilderNode::tree('n1', 'div'));

    $component = Livewire::test(BuilderCanvas::class)
        ->call('add', 'paragraph')
        ->set('compositionName', 'My work')
        ->call('load', 'simple');

    expect($component->instance()->compositionName)->toBe('Simple');

    $component->call('undo');

    $instance = $component->instance();

    // The undo gives the name back with the tree: a save made after the
    // undo would write over the composition the canvas held before.
    expect($instance->tree['nodes'])->toHaveKey('n2')
        ->and($instance->compositionName)->toBe('My work');
});

it('undoes a reset back to the cleared work', function () {
    $component = Livewire::test(BuilderCanvas::class)
        ->call('add', 'paragraph')
        ->call('resetCanvas');

    expect($component->instance()->tree['nodes'])->not->toHaveKey('n2');

    $component->call('undo');

    expect($component->instance()->tree['nodes'])->toHaveKey('n2');
});

it('keeps a limit on what the undo remembers', function () {
    // A session is a long run of small changes, and a stack that grows
    // without end would drag every request behind it.
    $component = Livewire::test(BuilderCanvas::class);

    foreach (range(1, 30) as $i) {
        $component->call('add', 'inline.span');
    }

    for ($i = 0; $i < 26; $i++) {
        $component->call('undo');
    }

    // Thirty adds with a stack of twenty five: the undo runs out with the
    // first five spans still on the canvas, and the ones after it do
    // nothing.
    expect(count($component->instance()->tree['nodes']))->toBe(6)
        ->and($component->instance()->undoStack)->toBe([]);
});
