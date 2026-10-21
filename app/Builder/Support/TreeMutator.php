<?php

declare(strict_types=1);

namespace App\Builder\Support;

use App\Builder\Contracts\BuilderNode;
use App\Builder\Registries\ComponentRegistry;
use App\Builder\Registries\ThemeRegistry;

/**
 * Every operation the builder performs on a tree.
 *
 * Trees are treated as immutable: each operation returns a new tree rather
 * than mutating in place, which keeps Livewire's change detection honest.
 *
 * @phpstan-import-type Node from BuilderNode
 * @phpstan-import-type Tree from BuilderNode
 */
final class TreeMutator
{
    /**
     * Add a child to a node.
     *
     * @param  Tree  $tree
     * @param  Node  $child
     * @return Tree
     */
    public static function addChild(array $tree, string $parentId, array $child, ?int $position = null): array
    {
        $parent = $tree['nodes'][$parentId] ?? null;

        if ($parent === null) {
            return $tree;
        }

        $descriptor = ComponentRegistry::get($parent['type']);

        if ($descriptor === null || ! $descriptor->allowsChild($child['type'])) {
            return $tree;
        }

        // A node that is already in the tree is not replaced: the id would
        // be held by two nodes at once, and the tree would come apart.
        if (isset($tree['nodes'][$child['id']])) {
            return $tree;
        }

        $tree['nodes'][$child['id']] = $child;

        $siblings = array_values(array_filter(
            $parent['children'],
            static fn (string $id): bool => $id !== $child['id'],
        ));

        $position = $position ?? count($siblings);
        $position = max(0, min($position, count($siblings)));

        $siblings = array_merge(
            array_slice($siblings, 0, $position),
            [$child['id']],
            array_slice($siblings, $position),
        );

        $parent['children'] = $siblings;
        $tree['nodes'][$parentId] = $parent;

        return $tree;
    }

    /**
     * Move a node into another parent, at the given index among its children.
     *
     * This is the operation behind wire:sort. The position is the index the
     * item ended up at among the destination's children, so the item is
     * detached from its old parent first and then inserted at that index.
     *
     * @param  Tree  $tree
     * @return Tree
     */
    public static function move(array $tree, string $itemId, string $parentId, int $position): array
    {
        $node = $tree['nodes'][$itemId] ?? null;
        $parent = $tree['nodes'][$parentId] ?? null;

        if ($node === null || $parent === null) {
            return $tree;
        }

        // A node may not be moved into itself or into one of its own
        // descendants, which would detach the branch from the tree.
        if ($itemId === $parentId || in_array($parentId, self::descendantsOf($tree, $itemId), true)) {
            return $tree;
        }

        $descriptor = ComponentRegistry::get($parent['type']);

        if ($descriptor === null || ! $descriptor->allowsChild($node['type'])) {
            return $tree;
        }

        $tree = self::detach($tree, $itemId);

        $parent = $tree['nodes'][$parentId];
        $siblings = array_values(array_filter(
            $parent['children'],
            static fn (string $id): bool => $id !== $itemId,
        ));

        // The position counts draggable items only, so the text segments
        // between them are skipped when working out where to insert.
        $items = array_values(array_filter(
            $siblings,
            fn (string $id): bool => ! self::isText($tree, $id),
        ));

        $before = $items[$position] ?? null;
        $at = $before !== null
            ? (int) array_search($before, $siblings, true)
            : count($siblings);

        $siblings = array_merge(
            array_slice($siblings, 0, $at),
            [$itemId],
            array_slice($siblings, $at),
        );

        $parent['children'] = $siblings;
        $tree['nodes'][$parentId] = $parent;

        return $tree;
    }

    /**
     * Remove a node and everything below it.
     *
     * @param  Tree  $tree
     * @return Tree
     */
    public static function remove(array $tree, string $nodeId): array
    {
        if ($tree['root'] === $nodeId) {
            return $tree;
        }

        $tree = self::detach($tree, $nodeId);

        foreach (self::descendantsOf($tree, $nodeId) as $descendantId) {
            unset($tree['nodes'][$descendantId]);
        }

        unset($tree['nodes'][$nodeId]);

        return $tree;
    }

    /**
     * Update the text of a text segment.
     *
     * @param  Tree  $tree
     * @return Tree
     */
    public static function setText(array $tree, string $nodeId, ?string $text): array
    {
        $node = $tree['nodes'][$nodeId] ?? null;

        if ($node === null || $node['type'] !== BuilderNode::TYPE_TEXT) {
            return $tree;
        }

        $node['text'] = $text;
        $tree['nodes'][$nodeId] = $node;

        return $tree;
    }

    /**
     * Append a text segment to a node.
     *
     * @param  Tree  $tree
     * @return Tree
     */
    public static function addText(array $tree, string $parentId, string $text, string $id): array
    {
        $parent = $tree['nodes'][$parentId] ?? null;

        if ($parent === null) {
            return $tree;
        }

        $segment = BuilderNode::make($id, BuilderNode::TYPE_TEXT, $text);

        $tree['nodes'][$id] = $segment;
        $parent['children'][] = $id;
        $tree['nodes'][$parentId] = $parent;

        return $tree;
    }

    /**
     * The text segments of a node, in order.
     *
     * @param  Tree  $tree
     * @return array<int, array{id: string, text: string}>
     */
    public static function textSegments(array $tree, string $nodeId): array
    {
        $node = $tree['nodes'][$nodeId] ?? null;

        if ($node === null) {
            return [];
        }

        $segments = [];

        foreach ($node['children'] as $childId) {
            $child = $tree['nodes'][$childId] ?? null;

            if ($child === null || $child['type'] !== BuilderNode::TYPE_TEXT) {
                continue;
            }

            $segments[] = [
                'id' => $childId,
                'text' => $child['text'] ?? '',
            ];
        }

        return $segments;
    }

    /**
     * Whether a node is a text segment.
     *
     * @param  Tree  $tree
     */
    public static function isText(array $tree, string $nodeId): bool
    {
        $node = $tree['nodes'][$nodeId] ?? null;

        return $node !== null && $node['type'] === BuilderNode::TYPE_TEXT;
    }

    /**
     * Set a single attribute on a node.
     *
     * The name has to pass the guard, so a saved component can never carry
     * an event handler, a style or a Livewire directive.
     *
     * @param  Tree  $tree
     * @return Tree
     */
    public static function setAttribute(array $tree, string $nodeId, string $name, string $value): array
    {
        $node = $tree['nodes'][$nodeId] ?? null;

        if ($node === null) {
            return $tree;
        }

        if (! AttributeGuard::allows($name)) {
            return $tree;
        }

        $node['attrs'][$name] = $value;

        $tree['nodes'][$nodeId] = $node;

        return $tree;
    }

    /**
     * Remove an attribute from a node.
     *
     * @param  Tree  $tree
     * @return Tree
     */
    public static function removeAttribute(array $tree, string $nodeId, string $name): array
    {
        $node = $tree['nodes'][$nodeId] ?? null;

        if ($node === null) {
            return $tree;
        }

        unset($node['attrs'][$name]);

        $tree['nodes'][$nodeId] = $node;

        return $tree;
    }

    /**
     * Add a theme variant to a node.
     *
     * A group holds a list of variants, so a node can carry more than one
     * variant of the same group. Adding a variant the node already has
     * leaves it alone.
     *
     * @param  Tree  $tree
     * @return Tree
     */
    public static function addTheme(array $tree, string $nodeId, string $group, string $variant): array
    {
        $node = $tree['nodes'][$nodeId] ?? null;

        if ($node === null) {
            return $tree;
        }

        if (! ThemeRegistry::allowsName($group) || ! ThemeRegistry::allowsName($variant)) {
            return $tree;
        }

        $variants = $node['theme'][$group] ?? [];

        if (! in_array($variant, $variants, true)) {
            $variants[] = $variant;
        }

        $node['theme'][$group] = $variants;

        $tree['nodes'][$nodeId] = $node;

        return $tree;
    }

    /**
     * Take a theme variant off a node.
     *
     * The group goes when its last variant does.
     *
     * @param  Tree  $tree
     * @return Tree
     */
    public static function removeTheme(array $tree, string $nodeId, string $group, string $variant): array
    {
        $node = $tree['nodes'][$nodeId] ?? null;

        if ($node === null) {
            return $tree;
        }

        $variants = array_values(array_filter(
            $node['theme'][$group] ?? [],
            static fn (string $applied): bool => $applied !== $variant,
        ));

        if ($variants === []) {
            unset($node['theme'][$group]);
        } else {
            $node['theme'][$group] = $variants;
        }

        $tree['nodes'][$nodeId] = $node;

        return $tree;
    }

    /**
     * Copy a node, and everything below it, next to the original.
     *
     * @param  Tree  $tree
     * @param  callable(string): string  $idFactory
     * @return Tree
     */
    public static function duplicate(array $tree, string $nodeId, callable $idFactory): array
    {
        $node = $tree['nodes'][$nodeId] ?? null;
        $parentId = self::parentOf($tree, $nodeId);

        if ($node === null || $parentId === null) {
            return $tree;
        }

        $copied = [];

        $copy = self::copyWithNewIds($tree, $nodeId, $idFactory, $copied);
        $copied[$copy['id']] = $copy;

        // Everything below the copy goes into the tree first, so the copy
        // itself can be added as a child of its parent.
        foreach ($copied as $id => $copiedNode) {
            if ($id !== $copy['id']) {
                $tree['nodes'][$id] = $copiedNode;
            }
        }

        $position = self::positionOf($tree, $nodeId);

        return self::addChild($tree, $parentId, $copy, ($position ?? 0) + 1);
    }

    /**
     * Find a node by id.
     *
     * @param  Tree  $tree
     * @return Node|null
     */
    public static function find(array $tree, string $nodeId): ?array
    {
        return $tree['nodes'][$nodeId] ?? null;
    }

    /**
     * The id of a node's parent, or null for the root.
     *
     * @param  Tree  $tree
     */
    public static function parentOf(array $tree, string $nodeId): ?string
    {
        foreach ($tree['nodes'] as $id => $node) {
            if ($id === $nodeId) {
                continue;
            }

            if (in_array($nodeId, $node['children'], true)) {
                return $id;
            }
        }

        return null;
    }

    /**
     * The index of a node among its siblings.
     *
     * @param  Tree  $tree
     */
    public static function positionOf(array $tree, string $nodeId): ?int
    {
        $parentId = self::parentOf($tree, $nodeId);

        if ($parentId === null) {
            return null;
        }

        $position = array_search($nodeId, $tree['nodes'][$parentId]['children'], true);

        return $position === false ? null : $position;
    }

    /**
     * The ids of every node below the given one.
     *
     * @param  Tree  $tree
     * @return array<int, string>
     */
    public static function descendantsOf(array $tree, string $nodeId): array
    {
        $node = $tree['nodes'][$nodeId] ?? null;

        if ($node === null) {
            return [];
        }

        $descendants = [];

        foreach ($node['children'] as $childId) {
            $descendants[] = $childId;
            $descendants = array_merge($descendants, self::descendantsOf($tree, $childId));
        }

        return $descendants;
    }

    /**
     * Move a node one slot up or down among its siblings.
     *
     * @param  Tree  $tree
     * @return Tree
     */
    public static function shift(array $tree, string $nodeId, int $offset): array
    {
        $parentId = self::parentOf($tree, $nodeId);
        $position = self::positionOf($tree, $nodeId);

        if ($parentId === null || $position === null) {
            return $tree;
        }

        $target = $position + $offset;

        if ($target < 0) {
            return $tree;
        }

        return self::move($tree, $nodeId, $parentId, $target);
    }

    /**
     * Copy a node and its descendants, giving every one of them a fresh id.
     *
     * @param  Tree  $tree
     * @param  callable(string): string  $idFactory
     * @param  array<string, Node>  $collected
     * @return Node
     */
    private static function copyWithNewIds(array $tree, string $nodeId, callable $idFactory, array &$collected): array
    {
        $copy = $tree['nodes'][$nodeId];
        $copy['id'] = $idFactory($nodeId);

        $children = [];

        foreach ($copy['children'] as $childId) {
            if (! isset($tree['nodes'][$childId])) {
                continue;
            }

            $childCopy = self::copyWithNewIds($tree, $childId, $idFactory, $collected);

            $children[] = $childCopy['id'];
            $collected[$childCopy['id']] = $childCopy;
        }

        $copy['children'] = $children;

        return $copy;
    }

    /**
     * Unlink a node from whichever parent holds it.
     *
     * @param  Tree  $tree
     * @return Tree
     */
    private static function detach(array $tree, string $nodeId): array
    {
        foreach ($tree['nodes'] as $id => $node) {
            if (! in_array($nodeId, $node['children'], true)) {
                continue;
            }

            $node['children'] = array_values(array_filter(
                $node['children'],
                static fn (string $childId): bool => $childId !== $nodeId,
            ));

            $tree['nodes'][$id] = $node;
        }

        return $tree;
    }
}
