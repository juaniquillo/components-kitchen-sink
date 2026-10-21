<?php

declare(strict_types=1);

use App\Builder\Contracts\BuilderNode;
use App\Builder\Support\TreeMutator;

beforeEach(function () {
    // n1 (div)
    //   n2 (div)
    //     n4 (paragraph)
    //   n3 (paragraph)
    $this->tree = [
        'root' => 'n1',
        'nodes' => [
            'n1' => BuilderNode::make('n1', 'div'),
            'n2' => BuilderNode::make('n2', 'div'),
            'n3' => BuilderNode::make('n3', 'paragraph'),
            'n4' => BuilderNode::make('n4', 'paragraph'),
        ],
    ];

    $this->tree['nodes']['n1']['children'] = ['n2', 'n3'];
    $this->tree['nodes']['n2']['children'] = ['n4'];
});

it('adds a child at the end of its parent', function () {
    $tree = TreeMutator::addChild($this->tree, 'n2', BuilderNode::make('n5', 'paragraph'));

    expect($tree['nodes']['n2']['children'])->toBe(['n4', 'n5'])
        ->and($tree['nodes'])->toHaveKey('n5');
});

it('adds a child at a given position', function () {
    $tree = TreeMutator::addChild($this->tree, 'n1', BuilderNode::make('n5', 'paragraph'), 0);

    expect($tree['nodes']['n1']['children'])->toBe(['n5', 'n2', 'n3']);
});

it('refuses a child the parent may not contain', function () {
    $tree = TreeMutator::addChild($this->tree, 'n2', BuilderNode::make('n5', 'table.td'));

    expect($tree['nodes']['n2']['children'])->toBe(['n4'])
        ->and($tree['nodes'])->not->toHaveKey('n5');
});

it('refuses a child whose id is already in the tree', function () {
    // The id would be held by two nodes at once, and the tree would come
    // apart.
    $tree = TreeMutator::addChild($this->tree, 'n2', BuilderNode::make('n4', 'paragraph'));

    expect($tree['nodes']['n2']['children'])->toBe(['n4'])
        ->and($tree['nodes']['n4']['type'])->toBe('paragraph');
});

it('moves a node into another parent', function () {
    $tree = TreeMutator::move($this->tree, 'n3', 'n2', 0);

    expect($tree['nodes']['n1']['children'])->toBe(['n2'])
        ->and($tree['nodes']['n2']['children'])->toBe(['n3', 'n4']);
});

it('moves a node within its own parent', function () {
    $tree = TreeMutator::move($this->tree, 'n2', 'n1', 1);

    expect($tree['nodes']['n1']['children'])->toBe(['n3', 'n2']);
});

it('clamps a position that is out of range', function () {
    $tree = TreeMutator::move($this->tree, 'n3', 'n2', 99);

    expect($tree['nodes']['n2']['children'])->toBe(['n4', 'n3']);
});

it('refuses to move a node into its own descendant', function () {
    $tree = TreeMutator::move($this->tree, 'n2', 'n4', 0);

    expect($tree['nodes']['n1']['children'])->toBe(['n2', 'n3'])
        ->and($tree['nodes']['n2']['children'])->toBe(['n4']);
});

it('refuses to move a node into a parent that may not contain it', function () {
    $tree = TreeMutator::move($this->tree, 'n2', 'n3', 0);

    expect($tree['nodes']['n1']['children'])->toBe(['n2', 'n3'])
        ->and($tree['nodes']['n3']['children'])->toBe([]);
});

it('removes a node and everything below it', function () {
    $tree = TreeMutator::remove($this->tree, 'n2');

    expect($tree['nodes']['n1']['children'])->toBe(['n3'])
        ->and($tree['nodes'])->not->toHaveKey('n2')
        ->and($tree['nodes'])->not->toHaveKey('n4');
});

it('never removes the root', function () {
    $tree = TreeMutator::remove($this->tree, 'n1');

    expect($tree['nodes']['n1']['children'])->toBe(['n2', 'n3']);
});

it('sets and clears the text of a text segment', function () {
    $tree = TreeMutator::addText($this->tree, 'n3', 'Hello', 't1');

    expect($tree['nodes']['t1']['text'])->toBe('Hello')
        ->and($tree['nodes']['n3']['children'])->toBe(['t1']);

    $tree = TreeMutator::setText($tree, 't1', null);

    expect($tree['nodes']['t1']['text'])->toBeNull();
});

it('will not set the text of a component', function () {
    // The text of a component is made of segments, not one blob.
    $tree = TreeMutator::setText($this->tree, 'n3', 'Hello');

    expect($tree['nodes']['n3']['text'])->toBeNull();
});

it('accepts text in a parent that takes it', function () {
    $tree = TreeMutator::addText($this->tree, 'n3', 'Hello', 't1');

    expect($tree['nodes']['n3']['children'])->toBe(['t1'])
        ->and($tree['nodes']['t1']['text'])->toBe('Hello');
});

it('accepts text in a component that only takes text', function () {
    // A textarea takes no children, so the text cannot arrive as a child
    // component. It is content, and the component takes content.
    $tree = BuilderNode::tree('n1', 'form.textarea');

    $tree = TreeMutator::addText($tree, 'n1', 'Hello', 't1');

    expect($tree['nodes']['n1']['children'])->toBe(['t1'])
        ->and($tree['nodes']['t1']['text'])->toBe('Hello');
});

it('refuses text in a parent that only takes children', function () {
    // A list only holds items, so a run of text in one is hoisted out by the
    // browser and the tree the builder holds is no longer the tree in the page.
    $tree = [
        'root' => 'n1',
        'nodes' => [
            'n1' => BuilderNode::make('n1', 'lists.ul'),
            'n2' => BuilderNode::make('n2', 'lists.li'),
        ],
    ];

    $tree['nodes']['n1']['children'] = ['n2'];

    $tree = TreeMutator::addText($tree, 'n1', 'Hoisted', 't1');

    expect($tree['nodes']['n1']['children'])->toBe(['n2'])
        ->and($tree['nodes'])->not->toHaveKey('t1');
});

it('refuses text in a table row', function () {
    // The parser foster-parents a run of text out of a table, which is the
    // worst case: the text would not even stay next to the row.
    $tree = [
        'root' => 'n1',
        'nodes' => ['n1' => BuilderNode::make('n1', 'table.tr')],
    ];

    $tree = TreeMutator::addText($tree, 'n1', 'Hoisted', 't1');

    expect($tree['nodes']['n1']['children'])->toBe([])
        ->and($tree['nodes'])->not->toHaveKey('t1');
});

it('refuses text in a component that holds nothing', function () {
    $tree = BuilderNode::tree('n1', 'form.text');

    $tree = TreeMutator::addText($tree, 'n1', 'Nope', 't1');

    expect($tree['nodes']['n1']['children'])->toBe([])
        ->and($tree['nodes'])->not->toHaveKey('t1');
});

it('refuses a text segment whose id is already in the tree', function () {
    $tree = TreeMutator::addText($this->tree, 'n3', 'First', 't1');
    $tree = TreeMutator::addText($tree, 'n3', 'Second', 't1');

    expect($tree['nodes']['t1']['text'])->toBe('First')
        ->and($tree['nodes']['n3']['children'])->toBe(['t1']);
});

it('lists the text segments of a node', function () {
    $tree = TreeMutator::addText($this->tree, 'n3', 'First', 't1');
    $tree = TreeMutator::addText($tree, 'n3', 'Second', 't2');

    expect(TreeMutator::textSegments($tree, 'n3'))->toBe([
        ['id' => 't1', 'text' => 'First'],
        ['id' => 't2', 'text' => 'Second'],
    ]);
});

it('keeps a text segment out of the draggable items', function () {
    $tree = $this->tree;
    $tree['nodes']['t1'] = BuilderNode::make('t1', BuilderNode::TYPE_TEXT, 'First');
    $tree['nodes']['n1']['children'] = ['t1', 'n2', 'n3'];

    // The position counts draggable items, so the text segment is skipped
    // and the node lands in front of the other component, not in front of
    // the text.
    $tree = TreeMutator::move($tree, 'n3', 'n1', 0);

    expect($tree['nodes']['n1']['children'])->toBe(['t1', 'n3', 'n2']);
});

it('sets an attribute of any name the guard allows', function () {
    $tree = TreeMutator::setAttribute($this->tree, 'n1', 'id', 'hero');
    $tree = TreeMutator::setAttribute($tree, 'n1', 'aria-label', 'Greeting');
    $tree = TreeMutator::setAttribute($tree, 'n1', 'data-testid', 'greeting');

    expect($tree['nodes']['n1']['attrs'])->toBe([
        'id' => 'hero',
        'aria-label' => 'Greeting',
        'data-testid' => 'greeting',
    ]);
});

it('refuses an attribute the guard blocks', function () {
    $tree = TreeMutator::setAttribute($this->tree, 'n1', 'id', 'hero');
    $tree = TreeMutator::setAttribute($tree, 'n1', 'onclick', 'alert(1)');

    expect($tree['nodes']['n1']['attrs'])->toBe(['id' => 'hero']);
});

it('keeps an attribute whose value is empty', function () {
    $tree = TreeMutator::setAttribute($this->tree, 'n1', 'id', 'hero');
    $tree = TreeMutator::setAttribute($tree, 'n1', 'disabled', '');

    expect($tree['nodes']['n1']['attrs'])->toBe(['id' => 'hero', 'disabled' => '']);
});

it('removes an attribute', function () {
    $tree = TreeMutator::setAttribute($this->tree, 'n1', 'id', 'hero');
    $tree = TreeMutator::removeAttribute($tree, 'n1', 'id');

    expect($tree['nodes']['n1']['attrs'])->toBe([]);
});

it('finds the parent and the position of a node', function () {
    expect(TreeMutator::parentOf($this->tree, 'n4'))->toBe('n2')
        ->and(TreeMutator::parentOf($this->tree, 'n1'))->toBeNull()
        ->and(TreeMutator::positionOf($this->tree, 'n3'))->toBe(1)
        ->and(TreeMutator::positionOf($this->tree, 'n4'))->toBe(0);
});

it('lists the descendants of a node', function () {
    expect(TreeMutator::descendantsOf($this->tree, 'n1'))->toBe(['n2', 'n4', 'n3'])
        ->and(TreeMutator::descendantsOf($this->tree, 'n4'))->toBe([]);
});

it('shifts a node up and down among its siblings', function () {
    $tree = TreeMutator::shift($this->tree, 'n3', -1);

    expect($tree['nodes']['n1']['children'])->toBe(['n3', 'n2']);

    $tree = TreeMutator::shift($tree, 'n3', 1);

    expect($tree['nodes']['n1']['children'])->toBe(['n2', 'n3']);
});

it('does not shift a node past the ends of its list', function () {
    $tree = TreeMutator::shift($this->tree, 'n2', -1);

    expect($tree['nodes']['n1']['children'])->toBe(['n2', 'n3']);
});

it('duplicates a node with fresh ids', function () {
    $counter = 0;

    $tree = TreeMutator::duplicate($this->tree, 'n2', function (string $id) use (&$counter): string {
        $counter++;

        return $id.'-copy'.$counter;
    });

    expect($tree['nodes']['n1']['children'])->toBe(['n2', 'n2-copy1', 'n3'])
        ->and($tree['nodes']['n2-copy1']['children'])->toBe(['n4-copy2'])
        ->and($tree['nodes'])->toHaveKey('n4-copy2');
});
