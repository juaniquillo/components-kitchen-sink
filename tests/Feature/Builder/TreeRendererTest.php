<?php

declare(strict_types=1);

use App\Builder\Contracts\BuilderNode;
use App\Builder\Support\TreeRenderer;

it('renders a node with the text it holds', function () {
    $tree = treeWithText('n1', 'paragraph', 'Hello');

    $html = (new TreeRenderer)->render($tree);

    expect($html)->toContain('<p')
        ->toContain('Hello')
        ->toContain('</p>');
});

it('renders children inside their parent', function () {
    $tree = BuilderNode::tree('n1', 'div');
    $tree['nodes']['n1']['children'] = ['n2'];
    $tree['nodes']['n2'] = BuilderNode::make('n2', 'headers.h1');
    $tree['nodes']['n2']['children'] = ['t1'];
    $tree['nodes']['t1'] = BuilderNode::make('t1', BuilderNode::TYPE_TEXT, 'Title');

    $html = (new TreeRenderer)->render($tree);

    expect($html)->toContain('<div')
        ->toContain('<h1')
        ->toContain('Title');
});

it('marks the root as a container but never as an item', function () {
    $tree = BuilderNode::tree('n1', 'div');
    $tree['nodes']['n1']['children'] = ['n2'];
    $tree['nodes']['n2'] = BuilderNode::make('n2', 'paragraph');

    $html = (new TreeRenderer)->render($tree);

    expect($html)->toContain('wire:sort="handleSort"')
        ->toContain('wire:sort:group-id="n1"')
        ->not->toContain('wire:sort:item="n1"');
});

it('keeps an empty container a drop target', function () {
    $tree = BuilderNode::tree('n1', 'div');

    $html = (new TreeRenderer)->render($tree);

    expect($html)->toContain('wire:sort="handleSort"')
        ->toContain('wire:sort:group-id="n1"');
});

it('marks a leaf as an item but not as a container', function () {
    $tree = BuilderNode::tree('n1', 'div');
    $tree['nodes']['n1']['children'] = ['n2'];
    $tree['nodes']['n2'] = BuilderNode::make('n2', 'form.text');

    $html = (new TreeRenderer)->render($tree);

    expect($html)->toContain('wire:sort:item="n2"')
        ->not->toContain('wire:sort:group-id="n2"');
});

it('keeps the chrome out of the drag candidates of a container', function () {
    $tree = BuilderNode::tree('n1', 'div');
    $tree['nodes']['n1']['children'] = ['n2'];
    $tree['nodes']['n2'] = BuilderNode::make('n2', 'form.text');

    $html = (new TreeRenderer)->render($tree);

    // The chrome is the only child of a container that is not a sortable
    // item, so the sort has to be told to leave it alone. Otherwise the
    // toolbar is dragged instead of the component it belongs to.
    expect($html)->toContain('wire:sort:config')
        ->toContain('bw-chrome')
        ->and(substr_count($html, 'wire:sort:config'))->toBe(1);
});

it('leaves the root of the tree without a handle', function () {
    $tree = BuilderNode::tree('n1', 'div');
    $tree['nodes']['n1']['children'] = ['n2'];
    $tree['nodes']['n2'] = BuilderNode::make('n2', 'paragraph');

    $html = (new TreeRenderer)->render($tree);

    // The root has nowhere to be moved to, so only the child has a handle.
    expect(substr_count($html, 'wire:sort:handle'))->toBe(1);
});

it('wraps a component that cannot hold a chrome, so it still gets a toolbar', function () {
    $tree = BuilderNode::tree('n1', 'div');
    $tree['nodes']['n1']['children'] = ['n2'];
    $tree['nodes']['n2'] = BuilderNode::make('n2', 'form.text');

    $html = (new TreeRenderer)->render($tree);

    // The wrapper is the sortable item, and the component sits inside it.
    expect($html)->toContain('bw-void')
        ->toContain('wire:sort:item="n2"')
        ->toContain('wire:sort:handle')
        ->toContain('<input');
});

it('does not wrap a component where a span would be invalid', function () {
    $tree = BuilderNode::tree('n1', 'div');
    $tree['nodes']['n1']['children'] = ['n2'];
    $tree['nodes']['n2'] = BuilderNode::make('n2', 'form.select');
    $tree['nodes']['n2']['children'] = ['n3'];
    $tree['nodes']['n3'] = BuilderNode::make('n3', 'form.option');

    $html = (new TreeRenderer)->render($tree);

    // The select can be wrapped, but a span inside it would be pulled out
    // by the parser, so the option is left as it is.
    expect(substr_count($html, 'bw-void'))->toBe(1)
        ->and($html)->toContain('wire:sort:item="n3"');
});

it('gives an empty inline element a box to be seen and grabbed', function () {
    $tree = BuilderNode::tree('n1', 'div');
    $tree['nodes']['n1']['children'] = ['n2'];
    $tree['nodes']['n2'] = BuilderNode::make('n2', 'inline.span');

    $html = (new TreeRenderer)->render($tree);

    expect($html)->toContain('min-width: 4rem')
        ->toContain('display: inline-block');
});

it('leaves an inline element that has content alone', function () {
    $tree = BuilderNode::tree('n1', 'div');
    $tree['nodes']['n1']['children'] = ['n2'];
    $tree['nodes']['n2'] = BuilderNode::make('n2', 'inline.span');
    $tree['nodes']['n2']['children'] = ['t1'];
    $tree['nodes']['t1'] = BuilderNode::make('t1', BuilderNode::TYPE_TEXT, 'Hello');

    $html = (new TreeRenderer)->render($tree);

    expect($html)->not->toContain('min-width: 4rem');
});

it('marks the selected node', function () {
    $tree = BuilderNode::tree('n1', 'div');

    $html = (new TreeRenderer)->render($tree, 'n1');

    expect($html)->toContain('data-selected="true"');
});

it('escapes the text it renders', function () {
    $tree = treeWithText('n1', 'paragraph', '<script>alert(1)</script>');

    $html = (new TreeRenderer)->render($tree);

    expect($html)->not->toContain('<script>')
        ->toContain('&lt;script&gt;');
});

it('renders the chrome with a drag handle, a configure button and an add menu', function () {
    $tree = BuilderNode::tree('n1', 'div');
    $tree['nodes']['n1']['children'] = ['n2'];
    $tree['nodes']['n2'] = BuilderNode::make('n2', 'paragraph');

    $html = (new TreeRenderer)->render($tree);

    // The attributes are escaped, so the quotes come back as entities.
    expect($html)->toContain('wire:sort:handle')
        ->toContain('openConfig')
        ->toContain('add(')
        ->toContain('paragraph');
});

it('gives a node the position the chrome is placed against', function () {
    $tree = BuilderNode::tree('n1', 'div');

    $html = (new TreeRenderer)->render($tree);

    expect($html)->toContain('position: relative');
});

it('swallows the default on a node, so a link is not activated by a click', function () {
    $tree = BuilderNode::tree('n1', 'div');
    $tree['nodes']['n1']['children'] = ['n2'];
    $tree['nodes']['n2'] = BuilderNode::make('n2', 'inline.link');
    $tree['nodes']['n2']['attrs'] = ['href' => 'https://example.com'];

    $html = (new TreeRenderer)->render($tree);

    expect($html)->toContain('wire:click.stop.prevent');
});

it('renders a clean copy with none of the builders additions', function () {
    $tree = treeWithText('n1', 'div', 'Hello');
    $tree['nodes']['n1']['attrs'] = ['id' => 'hero'];

    $html = (new TreeRenderer)->render($tree, 'n1', clean: true);

    expect($html)->toContain('Hello')
        ->toContain('id="hero"')
        ->not->toContain('wire:sort')
        ->not->toContain('wire:click')
        ->not->toContain('data-node-id')
        ->not->toContain('position: relative')
        ->not->toContain('padding-top');
});
