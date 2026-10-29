<?php

declare(strict_types=1);

namespace App\Builder\Support;

use App\Builder\Contracts\BuilderNode;

/**
 * The undo and redo stacks of a composition.
 *
 * A checkpoint is an opaque string: the composition decides what one holds,
 * and this class only knows how to keep them in order. The limit, the rule
 * that a change which changed nothing is not remembered, and the clearing of
 * the redo all live here instead of in every action of the canvas.
 *
 * @phpstan-type Snapshot array{
 *     tree: Tree,
 *     themes: array<string, array<string, string>>,
 *     selectedId: string|null,
 * }
 *
 * @phpstan-import-type Tree from BuilderNode
 */
final class CompositionHistory
{
    /**
     * How many changes the undo remembers.
     *
     * A session is a long run of small changes, and a stack that grows
     * without end would drag every request behind it.
     */
    public const LIMIT = 25;

    /**
     * @param  array<int, string>  $undo  Oldest first; the next undo is the last entry.
     * @param  array<int, string>  $redo  The next redo is the last entry.
     */
    public function __construct(
        private array $undo = [],
        private array $redo = [],
        private int $limit = self::LIMIT,
    ) {}

    /**
     * Turn a composition state into the string a checkpoint holds.
     *
     * @param  Snapshot  $state
     */
    public static function encode(array $state): string
    {
        $snapshot = json_encode([
            $state['tree'],
            $state['themes'],
            $state['selectedId'],
        ]);

        return $snapshot === false ? '' : $snapshot;
    }

    /**
     * Read a checkpoint back, or null when it is not one of ours.
     *
     * @return Snapshot|null
     */
    public static function decode(string $snapshot): ?array
    {
        $state = json_decode($snapshot, true);

        if (! is_array($state) || count($state) !== 3) {
            return null;
        }

        [$tree, $themes, $selectedId] = $state;

        if (! is_array($tree) || ! is_array($themes)) {
            return null;
        }

        /** @var Tree $tree */
        /** @var array<string, array<string, string>> $themes */

        return [
            'tree' => $tree,
            'themes' => $themes,
            'selectedId' => is_string($selectedId) ? $selectedId : null,
        ];
    }

    /**
     * Remember the state a change replaced, when the change replaced one.
     *
     * A new change also takes the redo with it: there is nothing to bring
     * back over it.
     */
    public function record(string $before, string $after): void
    {
        if ($before === $after) {
            return;
        }

        $this->undo[] = $before;
        $this->redo = [];

        if (count($this->undo) > $this->limit) {
            array_shift($this->undo);
        }
    }

    /**
     * Take the composition back to the state before the last change.
     *
     * The state being replaced is offered to the redo, so the way forward is
     * always the state the undo took away.
     */
    public function undo(string $current): ?string
    {
        if ($this->undo === []) {
            return null;
        }

        $this->redo[] = $current;

        return array_pop($this->undo);
    }

    /**
     * Bring back the state the last undo took away.
     */
    public function redo(string $current): ?string
    {
        if ($this->redo === []) {
            return null;
        }

        $this->undo[] = $current;

        return array_pop($this->redo);
    }

    public function canUndo(): bool
    {
        return $this->undo !== [];
    }

    public function canRedo(): bool
    {
        return $this->redo !== [];
    }

    /**
     * The stacks, to be handed back to the component that owns them.
     *
     * @return array{0: array<int, string>, 1: array<int, string>}
     */
    public function stacks(): array
    {
        return [$this->undo, $this->redo];
    }
}
