<?php

declare(strict_types=1);

namespace App\Builder\Contracts;

/**
 * The builder's canonical tree format.
 *
 * The tree is a flat map of nodes keyed by id, with each node holding the
 * ids of its children. A flat map keeps the type free of recursion, makes
 * moving a node a matter of editing two lists, and stays small when saved
 * to disk.
 *
 * This is deliberately *not* the package's own toArray() shape: that one
 * repeats environment specific theme paths on every node and mixes text in
 * with child components. Keeping our own shape means saved compositions stay
 * portable and small, and the text of a node is always unambiguous.
 *
 * @phpstan-type Node array{
 *     id: string,
 *     type: string,
 *     text: string|null,
 *     attrs: array<string, string>,
 *     children: array<int, string>,
 *     theme: array<string, array<int, string>>,
 * }
 * @phpstan-type Tree array{
 *     root: string,
 *     nodes: array<string, Node>,
 * }
 */
final class BuilderNode
{
    /**
     * The pseudo type of a text segment.
     *
     * A text segment is a node like any other, so it can be edited, removed
     * and reordered alongside the components it sits between.
     */
    public const TYPE_TEXT = 'text';

    /**
     * The attributes that name an element by id.
     *
     * The renderer scopes them so two renders of one tree keep their own
     * elements, and the mutator renames them when a node is copied, so a
     * duplicate does not put two elements with the same id on the page.
     * Most of them name one element, and `headers` names the cells a
     * table cell points at, as a list of ids separated by spaces.
     *
     * @var array<int, string>
     */
    public const ID_ATTRIBUTES = ['id', 'for', 'list', 'headers'];

    /**
     * Create a node with sensible defaults.
     *
     * @return Node
     */
    public static function make(string $id, string $type, ?string $text = null): array
    {
        return [
            'id' => $id,
            'type' => $type,
            'text' => $text,
            'attrs' => [],
            'children' => [],
            'theme' => [],
        ];
    }

    /**
     * Create a tree holding a single node.
     *
     * @return Tree
     */
    public static function tree(string $id, string $type, ?string $text = null): array
    {
        $node = self::make($id, $type, $text);

        return [
            'root' => $id,
            'nodes' => [$id => $node],
        ];
    }

    /**
     * Whether the given value looks like a node.
     */
    public static function isNode(mixed $value): bool
    {
        return is_array($value)
            && isset($value['id'], $value['type'])
            && is_string($value['id'])
            && is_string($value['type']);
    }
}
