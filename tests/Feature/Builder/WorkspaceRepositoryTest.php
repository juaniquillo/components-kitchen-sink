<?php

declare(strict_types=1);

use App\Builder\Contracts\BuilderNode;
use App\Builder\Support\WorkspaceRepository;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

beforeEach(function () {
    $this->repository = new WorkspaceRepository;
});

/**
 * The path a workspace file is written to.
 */
function workspacePath(string $id): string
{
    return builderTestDirectory().'/'.$id.'.json';
}

/**
 * Write a hand-made workspace file, the way a hand edit would.
 *
 * @param  array<string, mixed>  $envelope
 */
function writeWorkspaceFile(string $id, array $envelope): void
{
    Storage::disk('local')->put(workspacePath($id), json_encode($envelope));
}

it('writes a workspace as a json file named by its id', function () {
    $id = $this->repository->create('Hero section', BuilderNode::tree('n1', 'div'));

    expect(WorkspaceRepository::validId($id))->toBeTrue()
        ->and(Storage::disk('local')->exists(workspacePath($id)))->toBeTrue();

    $contents = json_decode(Storage::disk('local')->get(workspacePath($id)), true);

    expect($contents)->toHaveKey('id')
        ->toHaveKey('version')
        ->toHaveKey('name')
        ->toHaveKey('saved_at')
        ->toHaveKey('tree')
        ->toHaveKey('themes')
        ->and($contents['id'])->toBe($id)
        ->and($contents['name'])->toBe('Hero section')
        ->and($contents['tree']['root'])->toBe('n1');
});

it('names a workspace that is made without a name', function () {
    $id = $this->repository->create('', BuilderNode::tree('n1', 'div'));

    expect($this->repository->find($id)['name'])->toBe(WorkspaceRepository::DEFAULT_NAME);
});

it('keeps a name to a length a header can show', function () {
    $id = $this->repository->create(str_repeat('a', 300), BuilderNode::tree('n1', 'div'));

    expect(mb_strlen($this->repository->find($id)['name']))->toBe(120);
});

it('keeps the themes with the workspace', function () {
    $tree = BuilderNode::tree('n1', 'div');
    $tree['nodes']['n1']['children'] = ['n2'];
    $tree['nodes']['n2'] = BuilderNode::make('n2', 'inline.button');
    $tree['nodes']['n2']['theme'] = ['action' => ['primary']];

    $themes = ['action' => ['primary' => 'bg-indigo-600 text-white']];

    $id = $this->repository->create('Themed', $tree, $themes);

    $found = $this->repository->find($id);

    expect($found)->not->toBeNull()
        ->and($found['name'])->toBe('Themed')
        ->and($found['themes'])->toBe($themes)
        ->and($found['tree']['nodes']['n2']['theme'])->toBe(['action' => ['primary']]);
});

it('keeps several variants of one group', function () {
    $tree = BuilderNode::tree('n1', 'div');
    $tree['nodes']['n1']['children'] = ['n2'];
    $tree['nodes']['n2'] = BuilderNode::make('n2', 'inline.button');
    $tree['nodes']['n2']['theme'] = ['padding' => ['top-sm', 'bottom-sm']];

    $themes = ['padding' => ['top-sm' => 'pt-3', 'bottom-sm' => 'pb-3']];

    $id = $this->repository->create('Themed', $tree, $themes);

    expect($this->repository->find($id)['tree']['nodes']['n2']['theme'])
        ->toBe(['padding' => ['top-sm', 'bottom-sm']]);
});

it('reads a group that held a single variant back as a list', function () {
    // A group used to hold a single variant, so a file written before a
    // group could hold several still has to be read.
    $id = (string) Str::ulid();

    writeWorkspaceFile($id, [
        'id' => $id,
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
    ]);

    expect($this->repository->find($id)['tree']['nodes']['n2']['theme'])
        ->toBe(['action' => ['primary']]);
});

it('reads a workspace back', function () {
    $tree = BuilderNode::tree('n1', 'div');
    $tree['nodes']['n1']['children'] = ['n2'];
    $tree['nodes']['n2'] = BuilderNode::make('n2', 'headers.h1', 'Title');

    $id = $this->repository->create('Hero', $tree);

    expect($this->repository->find($id)['tree'])->toBe($tree);
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

    $id = $this->repository->create('Twice', $tree);

    $found = $this->repository->find($id)['tree'];

    expect($found['nodes']['n1']['children'])->toBe(['n2', 'n3'])
        ->and($found['nodes']['n3']['children'])->toBe([]);
});

it('breaks a cycle in a workspace', function () {
    $tree = BuilderNode::tree('n1', 'div');
    $tree['nodes']['n1']['children'] = ['n2'];
    $tree['nodes']['n2'] = BuilderNode::make('n2', 'div');
    $tree['nodes']['n2']['children'] = ['n1'];

    $id = $this->repository->create('Cycle', $tree);

    $found = $this->repository->find($id)['tree'];

    expect($found['nodes']['n1']['children'])->toBe(['n2'])
        ->and($found['nodes']['n2']['children'])->toBe([]);
});

it('reads a text segment back', function () {
    $tree = BuilderNode::tree('n1', 'div');
    $tree['nodes']['n1']['children'] = ['n2'];
    $tree['nodes']['n2'] = BuilderNode::make('n2', 'paragraph');
    $tree['nodes']['n2']['children'] = ['t1'];
    $tree['nodes']['t1'] = BuilderNode::make('t1', BuilderNode::TYPE_TEXT, 'Hello world');

    $id = $this->repository->create('Hero', $tree);

    $found = $this->repository->find($id)['tree'];

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

    $id = $this->repository->create('List', $tree);

    $found = $this->repository->find($id)['tree'];

    expect($found['nodes']['n2']['children'])->toBe([])
        ->and($found['nodes'])->not->toHaveKey('t1');
});

it('keeps a text segment in a component that only takes text', function () {
    $tree = BuilderNode::tree('n1', 'div');
    $tree['nodes']['n1']['children'] = ['n2'];
    $tree['nodes']['n2'] = BuilderNode::make('n2', 'form.textarea');
    $tree['nodes']['n2']['children'] = ['t1'];
    $tree['nodes']['t1'] = BuilderNode::make('t1', BuilderNode::TYPE_TEXT, 'Hello');

    $id = $this->repository->create('Textarea', $tree);

    $found = $this->repository->find($id)['tree'];

    expect($found['nodes']['n2']['children'])->toBe(['t1'])
        ->and($found['nodes']['t1']['text'])->toBe('Hello');
});

it('drops a text segment from a component that holds nothing', function () {
    $tree = BuilderNode::tree('n1', 'div');
    $tree['nodes']['n1']['children'] = ['n2'];
    $tree['nodes']['n2'] = BuilderNode::make('n2', 'form.text');
    $tree['nodes']['n2']['children'] = ['t1'];
    $tree['nodes']['t1'] = BuilderNode::make('t1', BuilderNode::TYPE_TEXT, 'Nope');

    $id = $this->repository->create('Input', $tree);

    $found = $this->repository->find($id)['tree'];

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

    $id = $this->repository->create('Buttons', $tree);

    $found = $this->repository->find($id)['tree'];

    expect($found['nodes']['n2']['children'])->toBe([])
        ->and($found['nodes'])->not->toHaveKey('n3');
});

it('deletes a workspace', function () {
    $id = $this->repository->create('Hero', BuilderNode::tree('n1', 'div'));

    expect($this->repository->delete($id))->toBeTrue()
        ->and($this->repository->find($id))->toBeNull()
        ->and($this->repository->exists($id))->toBeFalse()
        ->and($this->repository->delete($id))->toBeFalse();
});

it('knows a workspace id from everything else', function () {
    $id = (string) Str::ulid();

    expect(WorkspaceRepository::validId($id))->toBeTrue()
        ->and(WorkspaceRepository::validId(strtolower($id)))->toBeTrue()
        ->and(WorkspaceRepository::validId('builder'))->toBeFalse()
        ->and(WorkspaceRepository::validId('../../etc/passwd'))->toBeFalse()
        ->and(WorkspaceRepository::validId(''))->toBeFalse()
        ->and(WorkspaceRepository::validId($id.'x'))->toBeFalse();
});

it('takes the id out of a link or out of the id itself', function () {
    $id = (string) Str::ulid();

    expect(WorkspaceRepository::idFromLink('https://builder.test/builder/'.$id))->toBe($id)
        ->and(WorkspaceRepository::idFromLink('https://builder.test/builder/'.$id.'/'))->toBe($id)
        ->and(WorkspaceRepository::idFromLink('https://builder.test/builder/'.$id.'?tab=canvas'))->toBe($id)
        ->and(WorkspaceRepository::idFromLink($id))->toBe($id)
        ->and(WorkspaceRepository::idFromLink('  '.$id.'  '))->toBe($id)
        ->and(WorkspaceRepository::idFromLink('https://builder.test/builder'))->toBeNull()
        ->and(WorkspaceRepository::idFromLink('not a link'))->toBeNull()
        ->and(WorkspaceRepository::idFromLink(''))->toBeNull();
});

it('finds nothing under an id that names no workspace', function () {
    expect($this->repository->find((string) Str::ulid()))->toBeNull()
        ->and($this->repository->find('../../etc/passwd'))->toBeNull()
        ->and($this->repository->exists('../../etc/passwd'))->toBeFalse()
        ->and($this->repository->delete('../../etc/passwd'))->toBeFalse();
});

it('refuses to write a workspace under an id that is not a ULID', function () {
    $this->repository->save('../../etc/passwd', 'Escape', BuilderNode::tree('n1', 'div'));
})->throws(RuntimeException::class);

it('discards a workspace that holds an unknown component', function () {
    $id = (string) Str::ulid();

    writeWorkspaceFile($id, [
        'id' => $id,
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
    ]);

    expect($this->repository->find($id))->toBeNull();
});

it('discards attributes the guard blocks', function () {
    $id = (string) Str::ulid();

    writeWorkspaceFile($id, [
        'id' => $id,
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
    ]);

    $found = $this->repository->find($id);

    expect($found)->not->toBeNull()
        ->and($found['tree']['nodes']['n1']['attrs'])->toBe(['id' => 'ok']);
});

it('discards a theme variant that does not exist', function () {
    $id = (string) Str::ulid();

    writeWorkspaceFile($id, [
        'id' => $id,
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
    ]);

    $found = $this->repository->find($id);

    expect($found)->not->toBeNull()
        ->and($found['tree']['nodes']['n1']['theme'])->toBe([]);
});
