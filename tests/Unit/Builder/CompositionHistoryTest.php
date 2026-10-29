<?php

declare(strict_types=1);

use App\Builder\Contracts\BuilderNode;
use App\Builder\Support\CompositionHistory;

/**
 * A checkpoint that holds nothing but its own marker.
 */
function historyCheckpoint(string $marker): string
{
    return CompositionHistory::encode([
        'tree' => BuilderNode::tree('n1', 'div'),
        'themes' => ['marker' => ['value' => $marker]],
        'selectedId' => 'n1',
    ]);
}

it('round trips a composition state', function () {
    $state = [
        'tree' => BuilderNode::tree('n1', 'div'),
        'themes' => ['action' => ['primary' => 'bg-blue-500']],
        'selectedId' => 'n1',
    ];

    expect(CompositionHistory::decode(CompositionHistory::encode($state)))->toBe($state);
});

it('decodes a checkpoint that holds no selection', function () {
    $state = [
        'tree' => BuilderNode::tree('n1', 'div'),
        'themes' => [],
        'selectedId' => null,
    ];

    expect(CompositionHistory::decode(CompositionHistory::encode($state)))->toBe($state);
});

it('refuses a checkpoint it did not write', function () {
    expect(CompositionHistory::decode('not json'))->toBeNull()
        ->and(CompositionHistory::decode('{}'))->toBeNull()
        ->and(CompositionHistory::decode('[]'))->toBeNull()
        ->and(CompositionHistory::decode('[1,2,3]'))->toBeNull()
        ->and(CompositionHistory::decode('["n1",[],"n1"]'))->toBeNull()
        ->and(CompositionHistory::decode('[1,2,3,4]'))->toBeNull();
});

it('remembers the state a change replaced', function () {
    $history = new CompositionHistory;

    $history->record(historyCheckpoint('before'), historyCheckpoint('after'));

    expect($history->canUndo())->toBeTrue()
        ->and($history->canRedo())->toBeFalse()
        ->and($history->stacks()[0])->toBe([historyCheckpoint('before')]);
});

it('does not remember a change that changed nothing', function () {
    $history = new CompositionHistory;

    $history->record(historyCheckpoint('same'), historyCheckpoint('same'));

    expect($history->canUndo())->toBeFalse();
});

it('takes the redo away when a new change is remembered', function () {
    $history = new CompositionHistory;
    $history->record(historyCheckpoint('zero'), historyCheckpoint('one'));

    expect($history->undo(historyCheckpoint('one')))->toBe(historyCheckpoint('zero'))
        ->and($history->canRedo())->toBeTrue();

    $history->record(historyCheckpoint('zero'), historyCheckpoint('two'));

    expect($history->canRedo())->toBeFalse();
});

it('undoes back through the changes and redos forward again', function () {
    $history = new CompositionHistory;
    $history->record(historyCheckpoint('zero'), historyCheckpoint('one'));
    $history->record(historyCheckpoint('one'), historyCheckpoint('two'));

    expect($history->undo(historyCheckpoint('two')))->toBe(historyCheckpoint('one'))
        ->and($history->undo(historyCheckpoint('one')))->toBe(historyCheckpoint('zero'))
        ->and($history->undo(historyCheckpoint('zero')))->toBeNull();

    expect($history->redo(historyCheckpoint('zero')))->toBe(historyCheckpoint('one'))
        ->and($history->redo(historyCheckpoint('one')))->toBe(historyCheckpoint('two'))
        ->and($history->redo(historyCheckpoint('two')))->toBeNull();
});

it('keeps only the most recent changes', function () {
    $history = new CompositionHistory;

    for ($index = 0; $index < CompositionHistory::LIMIT + 5; $index++) {
        $history->record(historyCheckpoint('state-'.$index), historyCheckpoint('state-'.($index + 1)));
    }

    $undo = $history->stacks()[0];

    expect($undo)->toHaveCount(CompositionHistory::LIMIT)
        ->and($undo[0])->toBe(historyCheckpoint('state-5'));
});
