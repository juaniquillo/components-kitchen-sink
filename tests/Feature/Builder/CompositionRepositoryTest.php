<?php

declare(strict_types=1);

use App\Builder\Contracts\BuilderNode;
use App\Builder\Support\CompositionRepository;
use Illuminate\Support\Facades\Storage;

beforeEach(function () {
    config()->set('builder.directory', 'builder-tests');

    $this->repository = new CompositionRepository;
});

afterEach(function () {
    foreach (Storage::disk('local')->files('builder-tests') as $file) {
        Storage::disk('local')->delete($file);
    }
});

it('saves a composition as a json file', function () {
    $this->repository->save('Hero section', BuilderNode::tree('n1', 'div'));

    expect(Storage::disk('local')->exists('builder-tests/hero-section.json'))->toBeTrue();

    $contents = json_decode(
        Storage::disk('local')->get('builder-tests/hero-section.json'),
        true,
    );

    expect($contents)->toHaveKey('version')
        ->toHaveKey('name')
        ->toHaveKey('saved_at')
        ->toHaveKey('tree')
        ->and($contents['name'])->toBe('Hero section')
        ->and($contents['tree']['root'])->toBe('n1');
});

it('reads a composition back', function () {
    $tree = BuilderNode::tree('n1', 'div');
    $tree['nodes']['n1']['children'] = ['n2'];
    $tree['nodes']['n2'] = BuilderNode::make('n2', 'headers.h1', 'Title');

    $this->repository->save('Hero', $tree);

    expect($this->repository->find('hero'))->toBe($tree);
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
    Storage::disk('local')->put('builder-tests/broken.json', json_encode([
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
    Storage::disk('local')->put('builder-tests/hostile.json', json_encode([
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
    Storage::disk('local')->put('builder-tests/themed.json', json_encode([
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
        ->and($tree['nodes']['n1'])->not->toHaveKey('theme');
});
