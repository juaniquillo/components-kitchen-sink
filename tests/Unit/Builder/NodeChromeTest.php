<?php

declare(strict_types=1);

use App\Builder\Contracts\BuilderNode;
use App\Builder\Support\NodeChrome;

it('gives a flow container a div for its chrome', function () {
    expect(NodeChrome::tag('div'))->toBe('div')
        ->and(NodeChrome::tag('form.form'))->toBe('div')
        ->and(NodeChrome::tag('lists.li'))->toBe('div');
});

it('gives a phrasing container a span for its chrome', function () {
    expect(NodeChrome::tag('paragraph'))->toBe('span')
        ->and(NodeChrome::tag('headers.h1'))->toBe('span')
        ->and(NodeChrome::tag('table.td'))->toBe('span')
        ->and(NodeChrome::tag('form.textarea'))->toBe('span');
});

it('gives the elements that only accept one kind of child a matching tag', function () {
    expect(NodeChrome::tag('lists.ul'))->toBe('li')
        ->and(NodeChrome::tag('table.tr'))->toBe('td')
        ->and(NodeChrome::tag('table.tbody'))->toBe('tr')
        ->and(NodeChrome::tag('table.table'))->toBe('caption');
});

it('gives no chrome to the components that cannot hold one', function () {
    expect(NodeChrome::tag('inline.img'))->toBeNull()
        ->and(NodeChrome::tag('form.text'))->toBeNull()
        ->and(NodeChrome::tag('form.select'))->toBeNull()
        ->and(NodeChrome::tag('form.option'))->toBeNull()
        ->and(NodeChrome::tag('table.col'))->toBeNull();
});

it('builds a chrome with a handle, a configure button and an add menu', function () {
    $node = BuilderNode::make('n1', 'div');

    $chrome = NodeChrome::build($node);

    expect($chrome)->not->toBeNull();

    $attributes = $chrome?->getAttributes() ?? [];
    $contents = $chrome?->getContents() ?? [];

    expect($attributes)->toHaveKey('x-data')
        ->toHaveKey('x-on:click.outside')
        ->and($attributes['wire:key'])->toBe('chrome-n1');

    $buttons = [];

    foreach ($contents as $content) {
        if (method_exists($content, 'getAttributes')) {
            $buttons[] = $content->getAttributes();
        }
    }

    expect($buttons)->toHaveCount(5)
        ->and($buttons[0])->toHaveKey('wire:sort:handle')
        ->and($buttons[1]['wire:click.stop.prevent'] ?? null)->toContain('openConfig')
        ->and($buttons[2]['x-on:click'] ?? null)->toBe('open = !open')
        ->and($buttons[4]['x-on:click'] ?? null)->toContain('arm()');
});

it('marks the chrome so a drag can leave it alone', function () {
    $node = BuilderNode::make('n1', 'div');

    $chrome = NodeChrome::build($node);

    expect($chrome->getAttributes()['class'] ?? '')->toContain('bw-chrome');
});

it('leaves a node that cannot be moved without a handle', function () {
    $node = BuilderNode::make('n1', 'div');

    $chrome = NodeChrome::build($node, draggable: false);

    $handles = 0;

    foreach ($chrome->getContents() as $content) {
        if (method_exists($content, 'getAttributes') && array_key_exists('wire:sort:handle', $content->getAttributes())) {
            $handles++;
        }
    }

    expect($handles)->toBe(0);
});

it('swallows the default so a link is not activated by the chrome', function () {
    $node = BuilderNode::make('n1', 'inline.link');

    $chrome = NodeChrome::build($node);

    expect($chrome->getAttributes())->toHaveKey('x-on:click.prevent');

    // The configure button stops the propagation, so the default has to be
    // swallowed there as well.
    $contents = $chrome->getContents();
    $config = $contents[1];

    expect($config->getAttributes())->toHaveKey('wire:click.stop.prevent');
});

it('offers a text entry in the menu', function () {
    $node = BuilderNode::make('n1', 'div');

    $chrome = NodeChrome::build($node);

    $contents = $chrome->getContents();

    $menu = $contents[3] ?? null;

    $parts = method_exists($menu, 'getContents') ? $menu->getContents() : [];

    $first = $parts[0] ?? null;

    $items = method_exists($first, 'getContents') ? $first->getContents() : [];

    expect($items[0] ?? null)->toBe('Text');
});

it('offers the components a node may contain in its menu', function () {
    $node = BuilderNode::make('n1', 'div');

    $chrome = NodeChrome::build($node);

    $contents = $chrome?->getContents() ?? [];

    $menu = $contents[3] ?? null;

    $parts = method_exists($menu, 'getContents') ? $menu->getContents() : [];

    $list = $parts[1] ?? null;

    $items = method_exists($list, 'getContents') ? $list->getContents() : [];

    $types = [];

    foreach ($items as $item) {
        $attributes = method_exists($item, 'getAttributes') ? $item->getAttributes() : [];
        $types[] = $attributes['x-on:click'] ?? '';
    }

    expect($types)->not->toBeEmpty()
        ->and(implode(' ', $types))->toContain('add(');
});

it('builds no chrome for a component that cannot hold one', function () {
    $node = BuilderNode::make('n1', 'form.text');

    // It still gets one, so that it can be wrapped in something that can.
    expect(NodeChrome::tag('form.text'))->toBeNull()
        ->and(NodeChrome::build($node))->not->toBeNull();
});

it('only offers the components a node may contain', function () {
    $node = BuilderNode::make('n1', 'lists.ul');

    expect(NodeChrome::allowedChildren($node))->toBe(['lists.li']);
});

it('offers the inline components to a paragraph', function () {
    $node = BuilderNode::make('n1', 'paragraph');

    $allowed = NodeChrome::allowedChildren($node);

    expect($allowed)->toContain('inline.link', 'inline.strong')
        ->not->toContain('div');
});

it('offers nothing to a node that cannot hold children', function () {
    $node = BuilderNode::make('n1', 'inline.img');

    expect(NodeChrome::allowedChildren($node))->toBe([]);
});

it('offers nothing to a component that only takes text', function () {
    expect(NodeChrome::allowedChildren(BuilderNode::make('n1', 'form.textarea')))->toBe([])
        ->and(NodeChrome::allowedChildren(BuilderNode::make('n1', 'form.option')))->toBe([]);
});
