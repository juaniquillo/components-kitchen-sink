<?php

declare(strict_types=1);

use App\Builder\Contracts\BuilderNode;
use App\Builder\Support\PhpCodeGenerator;

beforeEach(function () {
    $this->generator = new PhpCodeGenerator;
});

it('generates a builder chain for the root component', function () {
    $tree = BuilderNode::tree('n1', 'div');

    expect($this->generator->expression($tree, 'n1'))
        ->toBe('ComponentBuilder::make(ComponentEnum::DIV)');
});

it('writes a theme as a setThemes call', function () {
    $tree = BuilderNode::tree('n1', 'div');
    $tree['nodes']['n1']['children'] = ['n2'];
    $tree['nodes']['n2'] = BuilderNode::make('n2', 'inline.button');
    $tree['nodes']['n2']['theme'] = ['action' => ['primary']];

    $generator = new PhpCodeGenerator(['action' => ['primary' => 'bg-indigo-600 text-white']]);

    expect($generator->expression($tree, 'n2'))
        ->toContain('->setThemes([')
        ->toContain("'action' => 'primary',");
});

it('writes several variants of one group as a list', function () {
    $tree = BuilderNode::tree('n1', 'div');
    $tree['nodes']['n1']['children'] = ['n2'];
    $tree['nodes']['n2'] = BuilderNode::make('n2', 'inline.button');
    $tree['nodes']['n2']['theme'] = ['padding' => ['top-sm', 'bottom-sm']];

    $generator = new PhpCodeGenerator(['padding' => ['top-sm' => 'pt-3', 'bottom-sm' => 'pb-3']]);

    $expression = $generator->expression($tree, 'n2');

    expect($expression)->toContain('->setThemes([')
        ->toContain("'padding' => [")
        ->toContain("'top-sm',")
        ->toContain("'bottom-sm',");
});

it('keeps the theme files out of the snippet', function () {
    $tree = BuilderNode::tree('n1', 'div');
    $tree['nodes']['n1']['children'] = ['n2'];
    $tree['nodes']['n2'] = BuilderNode::make('n2', 'inline.button');
    $tree['nodes']['n2']['theme'] = ['action' => ['primary']];

    $generator = new PhpCodeGenerator(['action' => ['primary' => 'bg-indigo-600 text-white']]);

    // The files travel in the export, so the component is all a snippet is.
    expect($generator->snippet($tree))
        ->toContain("'action' => 'primary',")
        ->toContain('LocalThemeComponentBuilder as ComponentBuilder')
        ->not->toContain('resources/views/_themes')
        ->not->toContain("'primary' => 'bg-indigo-600 text-white',");
});

it('writes a theme as a file the local manager reads', function () {
    $generator = new PhpCodeGenerator(['action' => ['primary' => 'bg-indigo-600 text-white']]);

    $file = $generator->themeFile('action');

    expect($file)->toStartWith("<?php\n\nreturn [")
        ->toContain("    'primary' => 'bg-indigo-600 text-white',")
        ->toEndWith("];\n");
});

it('uses the local builder even when there is no theme', function () {
    $tree = BuilderNode::tree('n1', 'div');

    $snippet = (new PhpCodeGenerator)->snippet($tree);

    expect($snippet)->toContain('LocalThemeComponentBuilder as ComponentBuilder')
        ->not->toContain('setThemes')
        ->not->toContain('Theme files');
});

it('leaves a theme that no node uses out of the snippet', function () {
    $tree = BuilderNode::tree('n1', 'div');

    $generator = new PhpCodeGenerator(['action' => ['primary' => 'bg-indigo-600']]);

    // The snippet never carries themes any more; the archive is where they
    // are written, so an unused group is no different from a used one.
    expect($generator->snippet($tree))->not->toContain('action.blade.php')
        ->not->toContain('bg-indigo-600');
});

it('generates attributes, text and children', function () {
    $tree = treeWithText('n1', 'div', 'Hello');
    $tree['nodes']['n1']['attrs'] = ['id' => 'hero'];

    $expression = $this->generator->expression($tree, 'n1');

    expect($expression)->toContain('->setAttributes([')
        ->toContain("'id' => 'hero',")
        ->toContain('->setContents([')
        ->toContain("'Hello',");
});

it('writes a text segment as a plain string', function () {
    $tree = treeWithText('n1', 'div', 'Hello');

    expect($this->generator->expression($tree, 'n1'))
        ->toContain("'Hello',")
        ->not->toContain('ComponentEnum::TEXT');
});

it('mixes text and components in one setContents', function () {
    $tree = BuilderNode::tree('n1', 'paragraph');

    $tree['nodes']['n1']['children'] = ['t1', 'n2', 't2'];
    $tree['nodes']['t1'] = BuilderNode::make('t1', BuilderNode::TYPE_TEXT, 'First text');
    $tree['nodes']['n2'] = BuilderNode::make('n2', 'inline.link');
    $tree['nodes']['n2']['children'] = ['t3'];
    $tree['nodes']['t3'] = BuilderNode::make('t3', BuilderNode::TYPE_TEXT, ' This is a link');
    $tree['nodes']['n2']['attrs'] = ['href' => 'https://google.com'];
    $tree['nodes']['t2'] = BuilderNode::make('t2', BuilderNode::TYPE_TEXT, 'Second Text');

    $expression = $this->generator->expression($tree, 'n1');

    expect($expression)->toContain("'First text',")
        ->toContain('ComponentBuilder::make(ComponentEnum::LINK)')
        ->toContain("' This is a link',")
        ->toContain("'Second Text',")
        ->toContain("'href' => 'https://google.com',");
});

it('nests children inside setContents', function () {
    $tree = BuilderNode::tree('n1', 'div');
    $tree['nodes']['n1']['children'] = ['n2'];

    $tree['nodes']['n2'] = BuilderNode::make('n2', 'headers.h1');
    $tree['nodes']['n2']['children'] = ['t1'];
    $tree['nodes']['t1'] = BuilderNode::make('t1', BuilderNode::TYPE_TEXT, 'Title');

    $expression = $this->generator->expression($tree, 'n1');

    expect($expression)->toContain('->setContents([')
        ->toContain('ComponentBuilder::make(ComponentEnum::H1)')
        ->toContain("'Title',");
});

it('indents nested children one level deeper', function () {
    $tree = BuilderNode::tree('n1', 'div');
    $tree['nodes']['n1']['children'] = ['n2'];
    $tree['nodes']['n2'] = BuilderNode::make('n2', 'headers.h1');

    $lines = explode("\n", $this->generator->expression($tree, 'n1'));

    expect($lines)->toContain('    ComponentBuilder::make(ComponentEnum::H1),');
});

it('emits the attributes the user chose', function () {
    $tree = BuilderNode::tree('n1', 'div');
    $tree['nodes']['n1']['attrs'] = [
        'id' => 'hero',
        'data-testid' => 'greeting',
        'aria-label' => 'Greeting',
    ];

    $expression = $this->generator->expression($tree, 'n1');

    expect($expression)->toContain("'id' => 'hero',")
        ->toContain("'data-testid' => 'greeting',")
        ->toContain("'aria-label' => 'Greeting',");
});

it('leaves a directive out of the generated code', function () {
    $tree = BuilderNode::tree('n1', 'div');
    $tree['nodes']['n1']['attrs'] = [
        'id' => 'hero',
        'wire:click' => 'deleteUser',
        'x-data' => '{ open: false }',
    ];

    $expression = $this->generator->expression($tree, 'n1');

    expect($expression)->toContain("'id' => 'hero',")
        ->not->toContain('wire:click')
        ->not->toContain('x-data');
});

it('escapes quotes and backslashes in text', function () {
    $tree = treeWithText('n1', 'paragraph', "It's a \\ backslash");

    expect($this->generator->expression($tree, 'n1'))
        ->toContain("'It\\'s a \\\\ backslash',");
});

it('produces a pasteable snippet with the imports', function () {
    $tree = BuilderNode::tree('n1', 'div');

    $snippet = $this->generator->snippet($tree);

    expect($snippet)->toContain('use Juaniquillo\BackendComponents\Builders\LocalThemeComponentBuilder as ComponentBuilder;')
        ->toContain('use Juaniquillo\BackendComponents\Enums\ComponentEnum;')
        ->toContain('$component = ComponentBuilder::make(ComponentEnum::DIV);');
});
