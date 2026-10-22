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

it('scaffolds a whole table under a node', function () {
    $tree = tableTree();

    // t1 table, t2 the head, t3 the head row, t4 the body, t11 and t18 the
    // body rows, and a cell with its text segment after each.
    expect($tree['nodes']['n1']['children'])->toBe(['t1'])
        ->and($tree['nodes']['t1']['children'])->toBe(['t2', 't4'])
        ->and($tree['nodes']['t2']['children'])->toBe(['t3'])
        ->and($tree['nodes']['t3']['children'])->toBe(['t5', 't7', 't9'])
        ->and($tree['nodes']['t4']['children'])->toBe(['t11', 't18'])
        ->and($tree['nodes']['t11']['children'])->toBe(['t12', 't14', 't16'])
        ->and($tree['nodes']['t18']['children'])->toBe(['t19', 't21', 't23']);

    expect($tree['nodes']['t5']['type'])->toBe('table.th')
        ->and($tree['nodes']['t5']['text'])->toBeNull()
        ->and($tree['nodes']['t6']['type'])->toBe(BuilderNode::TYPE_TEXT)
        ->and($tree['nodes']['t6']['text'])->toBe('—')
        ->and($tree['nodes']['t12']['type'])->toBe('table.td')
        ->and($tree['nodes']['t12']['children'])->toBe(['t13'])
        ->and($tree['nodes']['t12']['text'])->toBeNull()
        ->and($tree['nodes']['t11']['type'])->toBe('table.tr');
});

it('refuses a table where the parent does not take one', function () {
    $tree = $this->tree;

    $scaffolded = TreeMutator::scaffoldTable(
        $tree,
        'n3',
        2,
        2,
        fn (string $id): string => $id.'x',
    );

    expect($scaffolded['nodes']['n3']['children'])->toBe([])
        ->and(count($scaffolded['nodes']))->toBe(count($tree['nodes']));
});

it('refuses a scaffold smaller than one row or column', function () {
    $counter = 0;

    $tree = TreeMutator::scaffoldTable(
        $this->tree,
        'n1',
        0,
        2,
        function (string $id) use (&$counter): string {
            $counter++;

            return 't'.$counter;
        },
    );

    expect($tree['nodes']['n1']['children'])->toBe(['n2', 'n3']);
});

it('copies a row below itself without the texts of its cells', function () {
    $tree = tableTree();

    $tree = TreeMutator::addText($tree, 't12', 'Alpha', 'x1');

    $tree = TreeMutator::addRowBelow($tree, 't11', tableIdFactory());

    // t25 is the copy, below its source, and t18 stays behind it: three
    // cells, none of them with a text segment, and the segments they
    // inherited are gone.
    expect($tree['nodes']['t4']['children'])->toBe(['t11', 't25', 't18'])
        ->and($tree['nodes']['t25']['children'])->toBe(['t26', 't29', 't31'])
        ->and($tree['nodes']['t26']['children'])->toBe([])
        ->and($tree['nodes']['t29']['children'])->toBe([])
        ->and($tree['nodes']['t31']['children'])->toBe([]);

    // The source row is exactly as it was.
    expect($tree['nodes']['t12']['children'])->toBe(['t13', 'x1'])
        ->and($tree['nodes']['x1']['text'])->toBe('Alpha');
});

it('keeps what a copied cell holds besides its text', function () {
    $tree = tableTree();

    $tree = TreeMutator::addChild($tree, 't12', BuilderNode::make('x1', 'inline.span'));
    $tree = TreeMutator::addText($tree, 'x1', 'Inside', 'x2');

    $tree = TreeMutator::addRowBelow($tree, 't11', tableIdFactory());

    // t26 is the copy of t12, and the span that lives in the source cell is
    // in the copy with its own text: only the direct segments are taken.
    expect($tree['nodes']['t26']['children'])->toBe(['t28'])
        ->and($tree['nodes']['t28']['type'])->toBe('inline.span')
        ->and($tree['nodes']['t29']['type'])->toBe(BuilderNode::TYPE_TEXT)
        ->and($tree['nodes']['t29']['text'])->toBe('Inside');
});

it('refuses to copy something that is not a row', function () {
    $tree = tableTree();

    $unchanged = TreeMutator::addRowBelow($tree, 't2', tableIdFactory());

    expect($unchanged['nodes'])->toBe($tree['nodes']);
});

it('adds a column to every row of a table', function () {
    $tree = TreeMutator::addColumn(tableTree(), 't11', tableIdFactory());

    // t25 and its segment go into the head row, t27 and t31 into the data
    // ones, and the placeholder is written the way a cell's text is held.
    expect($tree['nodes']['t3']['children'])->toBe(['t5', 't7', 't9', 't25'])
        ->and($tree['nodes']['t25']['type'])->toBe('table.th')
        ->and($tree['nodes']['t25']['children'])->toBe(['t26'])
        ->and($tree['nodes']['t26']['text'])->toBe('—')
        ->and($tree['nodes']['t11']['children'])->toBe(['t12', 't14', 't16', 't27'])
        ->and($tree['nodes']['t27']['type'])->toBe('table.td')
        ->and($tree['nodes']['t27']['children'])->toBe(['t28'])
        ->and($tree['nodes']['t18']['children'])->toBe(['t19', 't21', 't23', 't29']);
});

it('refuses a column when the row is not in a table', function () {
    $unchanged = TreeMutator::addColumn($this->tree, 'n3', tableIdFactory());

    expect($unchanged['nodes']['n3']['children'])->toBe($this->tree['nodes']['n3']['children']);
});

it('removes the column a cell belongs to', function () {
    $tree = TreeMutator::deleteColumn(tableTree(), 't14');

    // The second column is gone from every row, with its segments, and the
    // cells that were behind it came forward.
    expect($tree['nodes']['t3']['children'])->toBe(['t5', 't9'])
        ->and($tree['nodes']['t11']['children'])->toBe(['t12', 't16'])
        ->and($tree['nodes']['t18']['children'])->toBe(['t19', 't23'])
        ->and($tree['nodes'])->not->toHaveKey('t7')
        ->and($tree['nodes'])->not->toHaveKey('t14')
        ->and($tree['nodes'])->not->toHaveKey('t21')
        ->and($tree['nodes'])->not->toHaveKey('t15');
});

it('refuses the column of a table whose cells span rows or columns', function () {
    $tree = tableTree();

    $tree = TreeMutator::setAttribute($tree, 't16', 'colspan', '2');

    $unchanged = TreeMutator::deleteColumn($tree, 't14');

    expect($unchanged['nodes']['t3']['children'])->toBe($tree['nodes']['t3']['children'])
        ->and($unchanged['nodes']['t11']['children'])->toBe($tree['nodes']['t11']['children'])
        ->and($unchanged['nodes']['t18']['children'])->toBe($tree['nodes']['t18']['children']);
});
