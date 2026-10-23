<?php

declare(strict_types=1);

use App\Builder\Contracts\BuilderNode;
use App\Builder\Registries\ComponentRegistry;
use App\Builder\Support\NodeChrome;
use Juaniquillo\BackendComponents\MainBackendComponent;

/**
 * Every string a component tree holds as its own content.
 *
 * @return array<int, string>
 */
function contentLabels(MainBackendComponent $component): array
{
    $labels = [];

    foreach ($component->getContents() as $child) {
        if (is_string($child)) {
            $labels[] = $child;

            continue;
        }

        if (method_exists($child, 'getContents')) {
            $labels = [...$labels, ...contentLabels($child)];

            continue;
        }

        if (method_exists($child, 'getContent')
            && is_string($child->getContent())
            && $child->getContent() !== '') {
            $labels[] = $child->getContent();
        }
    }

    return $labels;
}

it('gives a flow container a div for its chrome', function () {
    expect(NodeChrome::tag('div'))->toBe('div')
        ->and(NodeChrome::tag('form.form'))->toBe('div')
        ->and(NodeChrome::tag('lists.li'))->toBe('div');
});

it('gives a phrasing container a span for its chrome', function () {
    expect(NodeChrome::tag('paragraph'))->toBe('span')
        ->and(NodeChrome::tag('headers.h1'))->toBe('span')
        ->and(NodeChrome::tag('table.td'))->toBe('span');
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

it('gives a textarea no chrome of its own', function () {
    // A textarea is RCDATA: its contents are text, not markup, so a chrome
    // inside one would be read out as text and its buttons would not work.
    expect(NodeChrome::tag('form.textarea'))->toBeNull();
});

it('decides on a chrome for every component the palette offers', function () {
    // A type has to have a tag or be listed as one that has no chrome. A new
    // palette entry that is in neither would silently lose its toolbar and be
    // wrapped instead, with nothing to point at the omission.
    foreach (ComponentRegistry::all() as $type => $descriptor) {
        $hasTag = NodeChrome::tag($type) !== null;
        $hasNoChrome = in_array($type, NodeChrome::NO_CHROME, true);

        expect($hasTag !== $hasNoChrome)->toBeTrue("{$type} is not decided on");
    }
});

it('lists only components the palette offers as having no chrome', function () {
    foreach (NodeChrome::NO_CHROME as $type) {
        expect(ComponentRegistry::get($type))->not->toBeNull();
    }
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

    $menu = $chrome->getContents()[3] ?? null;

    $parts = method_exists($menu, 'getContents') ? $menu->getContents() : [];

    // The first part is the band of table operations the node takes.
    $first = $parts[1] ?? null;

    $items = method_exists($first, 'getContents') ? $first->getContents() : [];

    expect($items[0] ?? null)->toBe('Text');
});

it('offers a table in the menu of a node that takes one', function () {
    $chrome = NodeChrome::build(BuilderNode::make('n1', 'div'));

    $menu = $chrome->getContents()[3] ?? null;

    $parts = $menu ? $menu->getContents() : [];
    $items = $parts !== [] && method_exists($parts[0], 'getContents') ? $parts[0]->getContents() : [];

    $clicks = [];

    foreach ($items as $item) {
        if (method_exists($item, 'getAttributes')) {
            $clicks[] = $item->getAttributes()['x-on:click'] ?? '';
        }
    }

    expect(implode(' ', $clicks))->toContain('tableMode(')
        ->and(implode(' ', $clicks))->toContain("'n1'");
});

it('offers no table in the menu of a node that does not take one', function () {
    // A paragraph is phrasing content, and a table is not in it.
    $chrome = NodeChrome::build(BuilderNode::make('n1', 'paragraph'));

    $menu = $chrome->getContents()[3] ?? null;

    $parts = $menu ? $menu->getContents() : [];
    $items = $parts !== [] && method_exists($parts[0], 'getContents') ? $parts[0]->getContents() : [];

    $first = $items[0] ?? null;

    expect($first === null || (method_exists($first, 'getContent') && $first->getContent()) !== 'Add table…')->toBeTrue();
});

it('carries the toolbar of a table section in a cell', function () {
    // A row only holds cells, and if the chrome went straight into the row,
    // the parser would take the toolbar out of it and leave it without its
    // Alpine scope, so a head and a body would have no working controls.
    $chrome = NodeChrome::build(BuilderNode::make('n1', 'table.thead'));

    $contents = $chrome->getContents();

    expect(count($contents))->toBe(1)
        ->and(NodeChrome::tag('table.thead'))->toBe('tr')
        ->and($contents[0])->toBeInstanceOf(MainBackendComponent::class)
        ->and(count($contents[0]->getContents()))->toBe(5);
});

it('offers the row and column operations on a row', function () {
    $chrome = NodeChrome::build(BuilderNode::make('n1', 'table.tr'));

    $menu = $chrome->getContents()[3] ?? null;

    $parts = $menu ? $menu->getContents() : [];
    $items = $parts !== [] && method_exists($parts[0], 'getContents') ? $parts[0]->getContents() : [];

    $clicks = [];

    foreach ($items as $item) {
        if (method_exists($item, 'getAttributes')) {
            $clicks[] = $item->getAttributes()['x-on:click'] ?? '';
        }
    }

    expect(implode(' ', $clicks))->toContain('addRowBelow(')
        ->toContain('addColumn(')
        ->not->toContain('deleteColumn(');
});

it('offers deleting the column on a cell', function () {
    $chrome = NodeChrome::build(BuilderNode::make('n1', 'table.td'));

    $menu = $chrome->getContents()[3] ?? null;

    $parts = $menu ? $menu->getContents() : [];
    $items = $parts !== [] && method_exists($parts[0], 'getContents') ? $parts[0]->getContents() : [];

    $clicks = [];

    foreach ($items as $item) {
        if (method_exists($item, 'getAttributes')) {
            $clicks[] = $item->getAttributes()['x-on:click'] ?? '';
        }
    }

    expect(implode(' ', $clicks))->toContain('deleteColumn(')
        ->not->toContain('addRowBelow(');
});

it('offers the components a node may contain in its menu', function () {
    $node = BuilderNode::make('n1', 'div');

    $chrome = NodeChrome::build($node);

    $contents = $chrome?->getContents() ?? [];

    $menu = $contents[3] ?? null;

    $parts = method_exists($menu, 'getContents') ? $menu->getContents() : [];

    $list = $parts[2] ?? null;

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

it('offers no section a table already holds in its menu', function () {
    // The scaffold puts a head and a body in the table right away, so the
    // parts the table already holds are not on its menu.
    $table = BuilderNode::make('n1', 'table.table');
    $table['children'] = ['n2', 'n3'];

    $labels = contentLabels(NodeChrome::build($table, true, ['table.thead', 'table.tbody']));

    expect($labels)->toContain('Table Foot')
        ->and($labels)->toContain('Caption')
        ->and($labels)->not->toContain('Table Head')
        ->and($labels)->not->toContain('Table Body');
});

it('offers every section of a table that holds none', function () {
    $table = BuilderNode::make('n1', 'table.table');

    $labels = contentLabels(NodeChrome::build($table, true, []));

    expect($labels)->toContain('Table Head')
        ->and($labels)->toContain('Table Body')
        ->and($labels)->toContain('Table Foot')
        ->and($labels)->toContain('Caption');
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

it('does not offer text to a node that only takes children', function () {
    // A list has somewhere to put an item and nowhere to put a run of text.
    $chrome = NodeChrome::build(BuilderNode::make('n1', 'lists.ul'));

    $menu = $chrome->getContents()[3] ?? null;

    expect($menu->getContents())->toHaveCount(1);
});

it('offers text to a component that only takes text', function () {
    $chrome = NodeChrome::build(BuilderNode::make('n1', 'form.textarea'));

    $menu = $chrome->getContents()[3] ?? null;
    $parts = $menu->getContents();

    expect($parts)->toHaveCount(2)
        ->and($parts[0]->getContents())->toBe(['Text']);
});

it('offers both text and components to a node that takes both', function () {
    $chrome = NodeChrome::build(BuilderNode::make('n1', 'div'));

    $menu = $chrome->getContents()[3] ?? null;

    expect($menu?->getContents())->toHaveCount(4);

    // The band of table operations comes first, then the text entry, the
    // component list and the field the new text is typed into.
    $parts = $menu?->getContents() ?? [];

    expect(method_exists($parts[2] ?? null, 'getContents') ? implode(' ', array_map(
        fn ($item): string => $item->getAttributes()['x-on:click'] ?? '',
        $parts[2]->getContents(),
    )) : '')->toContain('add(');
});
