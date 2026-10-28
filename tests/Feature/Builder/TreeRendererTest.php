<?php

declare(strict_types=1);

use App\Builder\Contracts\BuilderNode;
use App\Builder\Support\TreeRenderer;
use Juaniquillo\BackendComponents\Builders\LocalThemeComponentBuilder;
use Juaniquillo\BackendComponents\Enums\ComponentEnum;

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
    expect($html)->toContain('bw-wrap')
        ->toContain('wire:sort:item="n2"')
        ->toContain('wire:sort:handle')
        ->toContain('<input');
});

it('wraps a textarea, whose contents are text and not markup', function () {
    $html = (new TreeRenderer)->render(BuilderNode::tree('n1', 'form.textarea'));

    // A textarea is RCDATA: a chrome inside one would be read out as text, so
    // the toolbar has to ride outside it, on the wrapper. The chrome is the
    // first element of the wrapper, like the bar of any other node is.
    expect($html)->toContain('bw-wrap')
        ->toContain('bw-chrome')
        ->toContain('<textarea')
        ->and(strpos($html, 'bw-chrome'))->toBeLessThan(strpos($html, '<textarea'));
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
    expect(substr_count($html, 'bw-wrap'))->toBe(1)
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

it('puts the classes of a theme on the node', function () {
    $tree = BuilderNode::tree('n1', 'div');
    $tree['nodes']['n1']['children'] = ['n2'];
    $tree['nodes']['n2'] = BuilderNode::make('n2', 'inline.button');
    $tree['nodes']['n2']['theme'] = ['action' => ['primary']];

    $html = (new TreeRenderer(['action' => ['primary' => 'bg-indigo-600 text-white']]))->render($tree);

    expect($html)->toContain('bg-indigo-600 text-white');
});

it('puts the classes of every variant of a group on the node', function () {
    $tree = BuilderNode::tree('n1', 'div');
    $tree['nodes']['n1']['children'] = ['n2'];
    $tree['nodes']['n2'] = BuilderNode::make('n2', 'inline.button');
    $tree['nodes']['n2']['theme'] = ['padding' => ['top-sm', 'bottom-sm']];

    $html = (new TreeRenderer(['padding' => ['top-sm' => 'pt-3', 'bottom-sm' => 'pb-3']]))->render($tree);

    expect($html)->toContain('pt-3')
        ->toContain('pb-3');
});

it('puts the same classes on the node as the package does', function () {
    // The canvas resolves the themes itself, so it has to agree with what
    // the package does with the theme file the generated code points at.
    $tree = BuilderNode::tree('n1', 'div');
    $tree['nodes']['n1']['children'] = ['n2'];
    $tree['nodes']['n2'] = BuilderNode::make('n2', 'inline.button');
    $tree['nodes']['n2']['theme'] = ['padding' => ['top-sm', 'bottom-sm']];

    $preview = (new TreeRenderer(['padding' => ['top-sm' => 'pt-3', 'bottom-sm' => 'pb-3']]))
        ->render($tree, null, clean: true);

    $button = LocalThemeComponentBuilder::make(ComponentEnum::BUTTON)
        ->setThemes(['padding' => ['top-sm', 'bottom-sm']]);

    $rendered = (string) $this->blade('{{ $button }}', ['button' => $button]);

    preg_match('/class="([^"]*)"/', $rendered, $matches);

    expect($matches[1])->toContain('pt-3')
        ->toContain('pb-3')
        ->and($preview)->toContain($matches[1]);
});

it('puts the classes of a theme on the clean preview as well', function () {
    $tree = BuilderNode::tree('n1', 'div');
    $tree['nodes']['n1']['children'] = ['n2'];
    $tree['nodes']['n2'] = BuilderNode::make('n2', 'inline.button');
    $tree['nodes']['n2']['theme'] = ['action' => ['primary']];

    $html = (new TreeRenderer(['action' => ['primary' => 'bg-indigo-600 text-white']]))->render($tree, null, clean: true);

    expect($html)->toContain('bg-indigo-600 text-white');
});

it('scopes the id-like attributes of a clean render', function () {
    // The canvas and its preview sit on the page at once, and a label
    // points at the first element that carries its `for`, so the second
    // copy holds ids of its own.
    $tree = BuilderNode::tree('n1', 'div');
    $tree['nodes']['n1']['children'] = ['n2', 'n3', 'n4', 'n5', 'n6'];
    $tree['nodes']['n2'] = BuilderNode::make('n2', 'form.label');
    $tree['nodes']['n2']['attrs'] = ['for' => 'name'];
    $tree['nodes']['n3'] = BuilderNode::make('n3', 'form.text');
    $tree['nodes']['n3']['attrs'] = ['id' => 'name', 'list' => 'options'];
    $tree['nodes']['n4'] = BuilderNode::make('n4', 'form.datalist');
    $tree['nodes']['n4']['attrs'] = ['id' => 'options'];
    $tree['nodes']['n5'] = BuilderNode::make('n5', 'table.th');
    $tree['nodes']['n5']['attrs'] = ['headers' => 'a b'];
    $tree['nodes']['n6'] = BuilderNode::make('n6', 'inline.button');
    $tree['nodes']['n6']['attrs'] = ['id' => ''];

    $html = (new TreeRenderer([], 'bw-preview-'))->render($tree, null, clean: true);

    expect($html)->toContain('for="bw-preview-name"')
        ->toContain('id="bw-preview-name"')
        ->toContain('list="bw-preview-options"')
        ->toContain('id="bw-preview-options"')
        ->toContain('headers="bw-preview-a bw-preview-b"')
        ->toContain('id=""')
        ->not->toContain('id="name"')
        ->not->toContain('for="name"')
        ->not->toContain('id="bw-preview-"');
});

it('leaves the ids of a canvas render alone', function () {
    // The canvas is what the user has built, so it shows the ids the
    // generated code will carry.
    $tree = BuilderNode::tree('n1', 'div');
    $tree['nodes']['n1']['children'] = ['n2', 'n3'];
    $tree['nodes']['n2'] = BuilderNode::make('n2', 'form.label');
    $tree['nodes']['n2']['attrs'] = ['for' => 'name'];
    $tree['nodes']['n3'] = BuilderNode::make('n3', 'form.text');
    $tree['nodes']['n3']['attrs'] = ['id' => 'name'];

    $html = (new TreeRenderer)->render($tree, null);

    expect($html)->toContain('for="name"')
        ->toContain('id="name"')
        ->not->toContain('bw-preview-');
});

it('leaves a node alone when the theme is not in the registry', function () {
    $tree = BuilderNode::tree('n1', 'div');
    $tree['nodes']['n1']['children'] = ['n2'];
    $tree['nodes']['n2'] = BuilderNode::make('n2', 'inline.button');
    $tree['nodes']['n2']['theme'] = ['action' => ['primary']];

    $html = (new TreeRenderer)->render($tree);

    expect($html)->not->toContain('bg-indigo-600');
});

it('keeps the chrome of a button node out of the button', function () {
    $tree = BuilderNode::tree('n1', 'div');
    $tree['nodes']['n1']['children'] = ['n2'];
    $tree['nodes']['n2'] = BuilderNode::make('n2', 'inline.button');

    $html = (new TreeRenderer)->render($tree);

    $dom = new DOMDocument;
    $dom->loadHTML($html, LIBXML_NOERROR);

    $xpath = new DOMXPath($dom);

    $chrome = $xpath->query('//*[@*[name()="wire:key"]="chrome-n2"]')->item(0);
    $button = $xpath->query('//*[@*[name()="data-node-id"]="n2"]')->item(0);

    // The chrome's controls are buttons, and a button cannot hold a button:
    // the browser takes the inner ones out of the chrome, and the chrome's
    // Alpine scope is lost with them.
    expect($chrome)->not->toBeNull()
        ->and($button)->not->toBeNull()
        ->and($chrome->parentNode)->not->toBe($button)
        ->and($button->getElementsByTagName('button')->length)->toBe(0)
        ->and($xpath->query('.//button', $chrome)->length)->toBeGreaterThan(0);
});

it('gives an empty button node a box to be seen in', function () {
    $tree = BuilderNode::tree('n1', 'div');
    $tree['nodes']['n1']['children'] = ['n2'];
    $tree['nodes']['n2'] = BuilderNode::make('n2', 'inline.button');

    $html = (new TreeRenderer)->render($tree);

    // The button sits inside a wrapper now, so it needs a box of its own.
    expect($html)->toContain('min-width: 4rem')
        ->toContain('min-height: 1.5rem');
});

it('marks a wrapped node as selected', function () {
    $tree = BuilderNode::tree('n1', 'div');
    $tree['nodes']['n1']['children'] = ['n2'];
    $tree['nodes']['n2'] = BuilderNode::make('n2', 'inline.button');

    $html = (new TreeRenderer)->render($tree, 'n2');

    // The inner element keeps its marker for the outline and the drawer,
    // and the wrapper carries it too, because the chrome of the wrapped
    // node hangs there and has to stay visible while it is selected.
    expect(substr_count($html, 'data-selected="true"'))->toBe(2);
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
        ->toContain('paragraph')
        // Every chrome is named after the type of its node, and the label
        // is text case in the markup: the uppercase comes from CSS.
        ->toContain('Paragraph');
});

it('keeps the chrome in the flow of its node', function () {
    $tree = BuilderNode::tree('n1', 'div');

    $html = (new TreeRenderer)->render($tree);

    // The chrome is a bar in the flow of its node, so the node itself needs
    // no positioning to keep it off the content. The chrome takes a
    // positioning of its own to stack its bar over what its node holds.
    expect($html)->not->toContain('position: relative');

    preg_match('/class="([^"]*\bbw-chrome\b[^"]*)"/', $html, $matches);

    expect($matches[1] ?? '')->toContain('relative');
});

it('wraps the chrome of a node that lays its children out across a row', function () {
    // A flex row puts the first child of a node in the row itself, so a
    // chrome in the flow would become part of the row. The node is
    // wrapped, and its chrome rides in the wrapper above it, in the flow.
    $tree = BuilderNode::tree('n1', 'div');
    $tree['nodes']['n1']['attrs'] = ['class' => 'gap-2 items-center flex'];

    $html = (new TreeRenderer)->render($tree);

    // The chrome is a bar in the flow of the wrapper, so the node itself
    // needs no positioning to keep it off the content.
    expect($html)->not->toContain('position: relative');

    // The Alpine of the chrome holds a > character, so the class can only
    // be found without excluding it.
    preg_match('/wire:key="chrome-n1".*?class="([^"]*)"/s', $html, $matches);

    expect($matches[1] ?? '')->toContain('relative z-20 mb-1')
        ->not->toContain('absolute');

    // The wrapper is a block of its own and the sortable item, and the
    // node sits inside it.
    expect($html)->toContain('bw-wrap')
        ->toContain('display: block;')
        ->toContain('wire:sort:item="n1"');
});

it('keeps a wrapped spread container a drop target and its children sortable', function () {
    // A node that lays its children out across a row is wrapped so its
    // chrome can ride above the row. The wrapper is the sortable item of
    // the parent, and the node inside it has to hold the group its own
    // children sort in. Without those attributes on the node, a drag
    // inside it would be caught by the nearest container above it, and
    // the whole node would be dragged in the place of the child.
    $tree = BuilderNode::tree('n1', 'div');
    $tree['nodes']['n1']['children'] = ['n2'];
    $tree['nodes']['n2'] = BuilderNode::make('n2', 'div');
    $tree['nodes']['n2']['attrs'] = ['class' => 'flex'];
    $tree['nodes']['n2']['children'] = ['n3'];
    $tree['nodes']['n3'] = BuilderNode::make('n3', 'inline.button');

    $html = (new TreeRenderer)->render($tree);

    // The root, the wrapped spread container and the wrapped button all
    // take children, so each one holds a group of its own.
    expect(substr_count($html, 'wire:sort="handleSort"'))->toBe(3)
        ->and($html)->toContain('wire:sort:group-id="n2"')
        // The wrapper is the item the parent sorts, and the node itself is
        // not an item of anything.
        ->and(substr_count($html, 'wire:sort:item="n2"'))->toBe(1);
});

it('keeps a wrapped select a live container as well', function () {
    // A select cannot hold the chrome, so it is wrapped, and it takes
    // children all the same: its options have to stay sortable within it
    // instead of a drag on one of them taking the whole select.
    $tree = BuilderNode::tree('n1', 'div');
    $tree['nodes']['n1']['children'] = ['n2'];
    $tree['nodes']['n2'] = BuilderNode::make('n2', 'form.select');
    $tree['nodes']['n2']['children'] = ['n3'];
    $tree['nodes']['n3'] = BuilderNode::make('n3', 'form.option');

    $html = (new TreeRenderer)->render($tree);

    expect($html)->toContain('wire:sort:item="n2"')
        ->toContain('wire:sort:group-id="n2"')
        ->toContain('wire:sort:item="n3"');
});

it('leaves the sort family out of a clean render of a spread node', function () {
    $tree = BuilderNode::tree('n1', 'div');
    $tree['nodes']['n1']['attrs'] = ['class' => 'flex'];

    $html = (new TreeRenderer)->render($tree, null, clean: true);

    expect($html)->not->toContain('wire:sort')
        ->not->toContain('bw-wrap');
});

it('floats the chrome of a node inside a row of the parent', function () {
    // A node inside the row of a flex row cannot be wrapped: a wrapper
    // would take the place of the node in the row, and its grow and
    // alignment would be lost. So its chrome anchors to its bottom right
    // corner, and the node is positioned for the chrome to anchor to.
    $tree = BuilderNode::tree('n1', 'div');
    $tree['nodes']['n1']['attrs'] = ['class' => 'items-center flex'];
    $tree['nodes']['n1']['children'] = ['n2'];
    $tree['nodes']['n2'] = BuilderNode::make('n2', 'div');
    $tree['nodes']['n2']['attrs'] = ['class' => 'gap-2 flex'];

    $html = (new TreeRenderer)->render($tree);

    expect($html)->toContain('position: relative');

    preg_match('/wire:key="chrome-n2".*?class="([^"]*)"/s', $html, $matches);

    expect($matches[1] ?? '')->toContain('absolute bottom-0 right-0');
});

it('keeps the chrome in the flow of a node that stacks its children', function () {
    // A flex column stacks its children the way a block does, so its chrome
    // lands above the content like the bar of any other node.
    $tree = BuilderNode::tree('n1', 'div');
    $tree['nodes']['n1']['attrs'] = ['class' => 'flex flex-col gap-2'];

    $html = (new TreeRenderer)->render($tree);

    expect($html)->not->toContain('position: relative');

    preg_match('/class="([^"]*\bbw-chrome\b[^"]*)"/', $html, $matches);

    expect($matches[1] ?? '')->toContain('relative')
        ->not->toContain('absolute');
});

it('takes the theme classes into account when it places a chrome', function () {
    $tree = BuilderNode::tree('n1', 'div');
    $tree['nodes']['n1']['theme'] = ['layout' => ['spread']];

    $html = (new TreeRenderer(['layout' => ['spread' => 'grid gap-2']]))->render($tree);

    // The theme classes decide where the first child lands like the typed
    // ones do, so a node with a spread theme is wrapped as well.
    expect($html)->not->toContain('position: relative')
        ->toContain('display: block;');

    preg_match('/wire:key="chrome-n1".*?class="([^"]*)"/s', $html, $matches);

    expect($matches[1] ?? '')->toContain('relative z-20 mb-1')
        ->not->toContain('absolute');
});

it('wraps the chrome of a node with a grid class of its own', function () {
    // The class of a grid rides in a typed attribute, exactly like the
    // classes of a theme do, so the same spread detection has to catch it.
    $tree = BuilderNode::tree('n1', 'div');
    $tree['nodes']['n1']['attrs'] = ['class' => 'grid grid-cols-3 gap-2'];

    $html = (new TreeRenderer)->render($tree);

    expect($html)->not->toContain('position: relative')
        ->toContain('display: block;');

    preg_match('/wire:key="chrome-n1".*?class="([^"]*)"/s', $html, $matches);

    expect($matches[1] ?? '')->toContain('relative z-20 mb-1')
        ->not->toContain('absolute');
});

it('wraps the chrome of a node with a column class of its own', function () {
    // Columns flow across the node in their own way, so they get the same
    // treatment as the flex rows and the grids.
    $tree = BuilderNode::tree('n1', 'div');
    $tree['nodes']['n1']['attrs'] = ['class' => 'columns-2 gap-4'];

    $html = (new TreeRenderer)->render($tree);

    expect($html)->not->toContain('position: relative')
        ->toContain('display: block;');

    preg_match('/wire:key="chrome-n1".*?class="([^"]*)"/s', $html, $matches);

    expect($matches[1] ?? '')->toContain('relative z-20 mb-1')
        ->not->toContain('absolute');
});

it('follows a row with a chrome row of its own', function () {
    // A row only holds cells, and a chrome in the flow of the row would
    // take a column of the table, so the chrome is a table row of its own
    // right after the row.
    $tree = BuilderNode::tree('n1', 'table.table');
    $tree['nodes']['n1']['children'] = ['n2'];
    $tree['nodes']['n2'] = BuilderNode::make('n2', 'table.tbody');
    $tree['nodes']['n2']['children'] = ['n3'];
    $tree['nodes']['n3'] = BuilderNode::make('n3', 'table.tr');
    $tree['nodes']['n3']['children'] = ['n4'];
    $tree['nodes']['n4'] = BuilderNode::make('n4', 'table.td');

    $html = (new TreeRenderer)->render($tree);

    // The row never needs positioning and holds no chrome, and the chrome
    // row follows the row's last cell, so the row itself takes no column
    // for it.
    expect($html)->not->toContain('position: relative')
        ->and($html)->toContain('wire:key="chrome-n3"');

    $row = strpos($html, 'wire:key="n3"');
    $rowEnd = strpos($html, '</tr>', $row);
    $chrome = strpos($html, 'wire:key="chrome-n3"');

    expect($row)->toBeGreaterThan(-1)
        ->and($rowEnd)->toBeGreaterThan($row)
        ->and($chrome)->toBeGreaterThan($rowEnd);
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
