<?php

declare(strict_types=1);

use App\Builder\Contracts\BuilderNode;
use App\Builder\Support\CompositionRepository;
use Illuminate\Support\Facades\Storage;

beforeEach(function () {
    $this->repository = new CompositionRepository;
});

afterEach(function () {
    foreach (Storage::disk('local')->files(builderTestDirectory()) as $file) {
        Storage::disk('local')->delete($file);
    }
});

it('saves a composition as a json file', function () {
    $this->repository->save('Hero section', BuilderNode::tree('n1', 'div'));

    expect(Storage::disk('local')->exists(builderTestDirectory().'/hero-section.json'))->toBeTrue();

    $contents = json_decode(
        Storage::disk('local')->get(builderTestDirectory().'/hero-section.json'),
        true,
    );

    expect($contents)->toHaveKey('version')
        ->toHaveKey('name')
        ->toHaveKey('saved_at')
        ->toHaveKey('tree')
        ->toHaveKey('themes')
        ->and($contents['name'])->toBe('Hero section')
        ->and($contents['tree']['root'])->toBe('n1');
});

it('keeps the themes with the composition', function () {
    $tree = BuilderNode::tree('n1', 'div');
    $tree['nodes']['n1']['children'] = ['n2'];
    $tree['nodes']['n2'] = BuilderNode::make('n2', 'inline.button');
    $tree['nodes']['n2']['theme'] = ['action' => ['primary']];

    $themes = ['action' => ['primary' => 'bg-indigo-600 text-white']];

    $this->repository->save('Themed', $tree, $themes);

    $found = $this->repository->findWithThemes('themed');

    expect($found)->not->toBeNull()
        ->and($found['themes'])->toBe($themes)
        ->and($found['tree']['nodes']['n2']['theme'])->toBe(['action' => ['primary']]);
});

it('keeps several variants of one group', function () {
    $tree = BuilderNode::tree('n1', 'div');
    $tree['nodes']['n1']['children'] = ['n2'];
    $tree['nodes']['n2'] = BuilderNode::make('n2', 'inline.button');
    $tree['nodes']['n2']['theme'] = ['padding' => ['top-sm', 'bottom-sm']];

    $themes = ['padding' => ['top-sm' => 'pt-3', 'bottom-sm' => 'pb-3']];

    $this->repository->save('Themed', $tree, $themes);

    $found = $this->repository->findWithThemes('themed');

    expect($found['tree']['nodes']['n2']['theme'])->toBe(['padding' => ['top-sm', 'bottom-sm']]);
});

it('reads a group that held a single variant back as a list', function () {
    // A group used to hold a single variant, so a file written before a
    // group could hold several still has to be read.
    Storage::disk('local')->put(builderTestDirectory().'/themed.json', json_encode([
        'version' => 1,
        'name' => 'Themed',
        'saved_at' => now()->toIso8601String(),
        'themes' => ['action' => ['primary' => 'bg-indigo-600']],
        'tree' => [
            'root' => 'n1',
            'nodes' => [
                'n1' => [
                    'id' => 'n1',
                    'type' => 'div',
                    'text' => null,
                    'attrs' => [],
                    'children' => ['n2'],
                    'theme' => [],
                ],
                'n2' => [
                    'id' => 'n2',
                    'type' => 'inline.button',
                    'text' => null,
                    'attrs' => [],
                    'children' => [],
                    'theme' => ['action' => 'primary'],
                ],
            ],
        ],
    ]));

    $found = $this->repository->findWithThemes('themed');

    expect($found['tree']['nodes']['n2']['theme'])->toBe(['action' => ['primary']]);
});

it('reads a composition back', function () {
    $tree = BuilderNode::tree('n1', 'div');
    $tree['nodes']['n1']['children'] = ['n2'];
    $tree['nodes']['n2'] = BuilderNode::make('n2', 'headers.h1', 'Title');

    $this->repository->save('Hero', $tree);

    expect($this->repository->find('hero'))->toBe($tree);
});

it('keeps a node in the hands of one parent only', function () {
    // A file that holds the same node twice is not a tree, and the node
    // would be drawn twice. The second parent may hold the child, so only
    // the rule that a node has one parent can stop it.
    $tree = BuilderNode::tree('n1', 'div');
    $tree['nodes']['n1']['children'] = ['n2', 'n3'];
    $tree['nodes']['n2'] = BuilderNode::make('n2', 'paragraph');
    $tree['nodes']['n3'] = BuilderNode::make('n3', 'div');
    $tree['nodes']['n3']['children'] = ['n2'];

    $this->repository->save('Twice', $tree);

    $found = $this->repository->find('twice');

    expect($found['nodes']['n1']['children'])->toBe(['n2', 'n3'])
        ->and($found['nodes']['n3']['children'])->toBe([]);
});

it('breaks a cycle in a composition', function () {
    $tree = BuilderNode::tree('n1', 'div');
    $tree['nodes']['n1']['children'] = ['n2'];
    $tree['nodes']['n2'] = BuilderNode::make('n2', 'div');
    $tree['nodes']['n2']['children'] = ['n1'];

    $this->repository->save('Cycle', $tree);

    $found = $this->repository->find('cycle');

    expect($found['nodes']['n1']['children'])->toBe(['n2'])
        ->and($found['nodes']['n2']['children'])->toBe([]);
});

it('reads a text segment back', function () {
    $tree = BuilderNode::tree('n1', 'div');
    $tree['nodes']['n1']['children'] = ['n2'];
    $tree['nodes']['n2'] = BuilderNode::make('n2', 'paragraph');
    $tree['nodes']['n2']['children'] = ['t1'];
    $tree['nodes']['t1'] = BuilderNode::make('t1', BuilderNode::TYPE_TEXT, 'Hello world');

    $this->repository->save('Hero', $tree);

    $found = $this->repository->find('hero');

    expect($found['nodes']['n2']['children'])->toBe(['t1'])
        ->and($found['nodes'])->toHaveKey('t1')
        ->and($found['nodes']['t1']['type'])->toBe(BuilderNode::TYPE_TEXT)
        ->and($found['nodes']['t1']['text'])->toBe('Hello world');
});

it('drops a text segment from a container that only takes children', function () {
    // A list has somewhere to put an item and nowhere to put a run of text,
    // so a text segment saved in one is not read back.
    $tree = BuilderNode::tree('n1', 'div');
    $tree['nodes']['n1']['children'] = ['n2'];
    $tree['nodes']['n2'] = BuilderNode::make('n2', 'lists.ul');
    $tree['nodes']['n2']['children'] = ['t1'];
    $tree['nodes']['t1'] = BuilderNode::make('t1', BuilderNode::TYPE_TEXT, 'Item');

    $this->repository->save('List', $tree);

    $found = $this->repository->find('list');

    expect($found['nodes']['n2']['children'])->toBe([])
        ->and($found['nodes'])->not->toHaveKey('t1');
});

it('keeps a text segment in a component that only takes text', function () {
    $tree = BuilderNode::tree('n1', 'div');
    $tree['nodes']['n1']['children'] = ['n2'];
    $tree['nodes']['n2'] = BuilderNode::make('n2', 'form.textarea');
    $tree['nodes']['n2']['children'] = ['t1'];
    $tree['nodes']['t1'] = BuilderNode::make('t1', BuilderNode::TYPE_TEXT, 'Hello');

    $this->repository->save('Textarea', $tree);

    $found = $this->repository->find('textarea');

    expect($found['nodes']['n2']['children'])->toBe(['t1'])
        ->and($found['nodes']['t1']['text'])->toBe('Hello');
});

it('drops a text segment from a component that holds nothing', function () {
    $tree = BuilderNode::tree('n1', 'div');
    $tree['nodes']['n1']['children'] = ['n2'];
    $tree['nodes']['n2'] = BuilderNode::make('n2', 'form.text');
    $tree['nodes']['n2']['children'] = ['t1'];
    $tree['nodes']['t1'] = BuilderNode::make('t1', BuilderNode::TYPE_TEXT, 'Nope');

    $this->repository->save('Input', $tree);

    $found = $this->repository->find('input');

    expect($found['nodes']['n2']['children'])->toBe([])
        ->and($found['nodes'])->not->toHaveKey('t1');
});

it('drops a component the parent does not accept', function () {
    // A button inside a button would be taken out of it by the browser, so
    // the tree would come apart.
    $tree = BuilderNode::tree('n1', 'div');
    $tree['nodes']['n1']['children'] = ['n2'];
    $tree['nodes']['n2'] = BuilderNode::make('n2', 'inline.button');
    $tree['nodes']['n2']['children'] = ['n3'];
    $tree['nodes']['n3'] = BuilderNode::make('n3', 'inline.button');

    $this->repository->save('Buttons', $tree);

    $found = $this->repository->find('buttons');

    expect($found['nodes']['n2']['children'])->toBe([])
        ->and($found['nodes'])->not->toHaveKey('n3');
});

it('lists the saved compositions newest first', function () {
    $this->repository->save('First', BuilderNode::tree('n1', 'div'));

    $this->repository->save('Second', BuilderNode::tree('n1', 'div'));

    $saved = $this->repository->all();

    expect($saved)->toHaveCount(2)
        ->and($saved[0]->name)->toBe('Second')
        ->and($saved[1]->name)->toBe('First')
        ->and($saved[0]->slug)->toBe('second');
});

it('deletes a composition', function () {
    $this->repository->save('Hero', BuilderNode::tree('n1', 'div'));

    expect($this->repository->delete('hero'))->toBeTrue()
        ->and($this->repository->find('hero'))->toBeNull()
        ->and($this->repository->delete('hero'))->toBeFalse();
});

it('turns a name into a safe file name', function () {
    expect($this->repository->normalise('Hero Section'))->toBe('hero-section')
        ->and($this->repository->normalise('  My / Weird.. Name  '))->toBe('my-weird-name')
        ->and($this->repository->normalise('!!!'))->toBeNull()
        ->and($this->repository->normalise(''))->toBeNull();
});

it('refuses a name that would escape the directory', function () {
    expect($this->repository->normalise('../../etc/passwd'))->not->toBe('../../etc/passwd');
});

it('discards a file that holds an unknown component', function () {
    Storage::disk('local')->put(builderTestDirectory().'/broken.json', json_encode([
        'version' => 1,
        'name' => 'Broken',
        'saved_at' => '2026-01-01T00:00:00+00:00',
        'tree' => [
            'root' => 'n1',
            'nodes' => [
                'n1' => [
                    'id' => 'n1',
                    'type' => 'not.a.component',
                    'text' => null,
                    'attrs' => [],
                    'theme' => [],
                    'children' => [],
                ],
            ],
        ],
    ]));

    expect($this->repository->find('broken'))->toBeNull();
});

it('discards attributes the guard blocks', function () {
    Storage::disk('local')->put(builderTestDirectory().'/hostile.json', json_encode([
        'version' => 1,
        'name' => 'Hostile',
        'saved_at' => '2026-01-01T00:00:00+00:00',
        'tree' => [
            'root' => 'n1',
            'nodes' => [
                'n1' => [
                    'id' => 'n1',
                    'type' => 'div',
                    'text' => null,
                    'attrs' => ['id' => 'ok', 'onclick' => 'alert(1)', 'style' => 'x', 'wire:click' => 'deleteUser'],
                    'children' => [],
                ],
            ],
        ],
    ]));

    $tree = $this->repository->find('hostile');

    expect($tree)->not->toBeNull()
        ->and($tree['nodes']['n1']['attrs'])->toBe(['id' => 'ok']);
});

it('discards a theme variant that does not exist', function () {
    Storage::disk('local')->put(builderTestDirectory().'/themed.json', json_encode([
        'version' => 1,
        'name' => 'Themed',
        'saved_at' => '2026-01-01T00:00:00+00:00',
        'tree' => [
            'root' => 'n1',
            'nodes' => [
                'n1' => [
                    'id' => 'n1',
                    'type' => 'div',
                    'text' => null,
                    'attrs' => [],
                    'theme' => ['color' => 'not-a-variant'],
                    'children' => [],
                ],
            ],
        ],
    ]));

    $tree = $this->repository->find('themed');

    expect($tree)->not->toBeNull()
        ->and($tree['nodes']['n1']['theme'])->toBe([]);
});
