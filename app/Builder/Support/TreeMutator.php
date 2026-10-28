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
     * The text a table cell is given when it is created.
     *
     * An empty cell has no box to click on, so a placeholder is what keeps
     * a fresh table touchable before anything is typed in it.
     */
    public const CELL_TEXT = '—';

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
     * A text segment is content, so it may only go where the component takes
     * text. The segments of a list, a table row or a void element are refused
     * here: the browser would hoist the text out of its parent, and the tree
     * the builder holds would no longer be the tree in the page.
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

        $descriptor = ComponentRegistry::get($parent['type']);

        if ($descriptor === null || ! $descriptor->allowsChildType(BuilderNode::TYPE_TEXT)) {
            return $tree;
        }

        // A text segment that is already in the tree is not replaced, for the
        // same reason a component is not.
        if (isset($tree['nodes'][$id])) {
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

        $copied = self::renameIdentities($copied);

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
     * Give the identity attributes of a copied branch values of their own.
     *
     * A copy that kept the id of its original would put two elements with
     * the same id on the canvas and in the generated code, and a label
     * would point at the first of them. An id is given the id of the node
     * it landed on as a suffix, and the references that point at ids of
     * the branch — a label at its input, a cell at its headers — follow
     * along. References that point outside the branch stay as they are:
     * a copy of a label is still a label for the same input.
     *
     * @param  array<string, Node>  $copied
     * @return array<string, Node>
     */
    private static function renameIdentities(array $copied): array
    {
        $map = [];

        foreach ($copied as $newId => $copiedNode) {
            if (isset($copiedNode['attrs']['id'])) {
                $map[$copiedNode['attrs']['id']] = $copiedNode['attrs']['id'].'-'.$newId;
            }
        }

        foreach ($copied as $newId => $copiedNode) {
            foreach (BuilderNode::ID_ATTRIBUTES as $attribute) {
                if (! isset($copiedNode['attrs'][$attribute])) {
                    continue;
                }

                if ($attribute === 'headers') {
                    $copiedNode['attrs'][$attribute] = implode(' ', array_map(
                        fn (string $token): string => $map[$token] ?? $token,
                        explode(' ', $copiedNode['attrs'][$attribute]),
                    ));

                    continue;
                }

                $value = $copiedNode['attrs'][$attribute];
                $copiedNode['attrs'][$attribute] = $map[$value] ?? $value;
            }

            $copied[$newId] = $copiedNode;
        }

        return $copied;
    }

    /**
     * Copy a table row below itself, without the texts of its cells.
     *
     * A row that is added is wanted for its structure: the texts of the
     * source row belong to their own column, so the direct text segments
     * of the copy's cells are taken out and whatever else a cell holds is
     * kept.
     *
     * @param  Tree  $tree
     * @param  callable(string): string  $idFactory
     * @return Tree
     */
    public static function addRowBelow(array $tree, string $rowId, callable $idFactory): array
    {
        $row = $tree['nodes'][$rowId] ?? null;

        if ($row === null || $row['type'] !== 'table.tr') {
            return $tree;
        }

        $parentId = self::parentOf($tree, $rowId);

        if ($parentId === null) {
            return $tree;
        }

        $siblings = $tree['nodes'][$parentId]['children'];

        $tree = self::duplicate($tree, $rowId, $idFactory);

        // The copy's id is the one its parent did not hold before, and when
        // the duplication refused there is no such id.
        $copyId = array_values(array_diff(
            $tree['nodes'][$parentId]['children'] ?? [],
            $siblings,
        ))[0] ?? null;

        if ($copyId === null) {
            return $tree;
        }

        $copy = $tree['nodes'][$copyId];

        foreach ($copy['children'] as $cellId) {
            $cell = $tree['nodes'][$cellId] ?? null;

            if ($cell === null || ! in_array($cell['type'], ['table.th', 'table.td'], true)) {
                continue;
            }

            $texts = array_values(array_filter(
                $cell['children'],
                fn (string $childId): bool => self::isText($tree, $childId),
            ));

            if ($texts === []) {
                continue;
            }

            $tree['nodes'][$cellId]['children'] = array_values(array_diff($cell['children'], $texts));

            foreach ($texts as $textId) {
                unset($tree['nodes'][$textId]);
            }
        }

        return $tree;
    }

    /**
     * Add a column at the end of every row of a table.
     *
     * A column is a run of cells along the rows, so the operation is
     * anchored on a row and reaches across the table it belongs to: every
     * row gets a cell, and the head keeps header cells while the body keeps
     * data ones.
     *
     * @param  Tree  $tree
     * @param  callable(string): string  $idFactory
     * @return Tree
     */
    public static function addColumn(array $tree, string $rowId, callable $idFactory): array
    {
        $tableId = self::tableOfRow($tree, $rowId);

        if ($tableId === null) {
            return $tree;
        }

        foreach (self::rowsOfTable($tree, $tableId) as $row) {
            $type = $row['inHead'] ? 'table.th' : 'table.td';

            $cellId = $idFactory($row['id']);
            $textId = $idFactory($cellId);

            $cell = BuilderNode::make($cellId, $type);

            $tree = self::addChild($tree, $row['id'], $cell);

            // The placeholder is a segment of its own, and segments go in
            // the way every other text of a cell does.
            $tree = self::addText($tree, $cellId, self::CELL_TEXT, $textId);
        }

        return $tree;
    }

    /**
     * Remove the column a cell belongs to.
     *
     * The cell's place among the cells of its row is the column, and the
     * cell at the same place is taken out of every row of the table. A
     * column is only well defined while each row keeps one cell per column,
     * so a table with cells that span rows or columns is left alone.
     *
     * @param  Tree  $tree
     * @return Tree
     */
    public static function deleteColumn(array $tree, string $cellId): array
    {
        $cell = $tree['nodes'][$cellId] ?? null;

        if ($cell === null || ! in_array($cell['type'], ['table.th', 'table.td'], true)) {
            return $tree;
        }

        $rowId = self::parentOf($tree, $cellId);
        $row = $rowId !== null ? ($tree['nodes'][$rowId] ?? null) : null;

        if ($row === null || $row['type'] !== 'table.tr') {
            return $tree;
        }

        $tableId = self::tableOfRow($tree, $rowId);

        if ($tableId === null || self::cellsSpan($tree, $tableId)) {
            return $tree;
        }

        $position = null;
        $cells = 0;

        foreach ($row['children'] as $childId) {
            $child = $tree['nodes'][$childId] ?? null;

            if ($child === null || ! in_array($child['type'], ['table.th', 'table.td'], true)) {
                continue;
            }

            if ($childId === $cellId) {
                $position = $cells;
            }

            $cells++;
        }

        if ($position === null) {
            return $tree;
        }

        foreach (self::rowsOfTable($tree, $tableId) as $rowEntry) {
            $rowNode = $tree['nodes'][$rowEntry['id']];

            $cells = array_values(array_filter(
                $rowNode['children'],
                fn (string $childId): bool => in_array($tree['nodes'][$childId]['type'] ?? '', ['table.th', 'table.td'], true),
            ));

            if (isset($cells[$position])) {
                $tree = self::remove($tree, $cells[$position]);
            }
        }

        return $tree;
    }

    /**
     * Add a whole table under a node.
     *
     * A table is the one component that is not assembled part by part: the
     * head and the body with their rows and cells are added in one go, every
     * cell carrying the placeholder text, so the skeleton is there to be
     * edited and not to be clicked into shape first.
     *
     * @param  Tree  $tree
     * @param  callable(string): string  $idFactory
     * @return Tree
     */
    public static function scaffoldTable(array $tree, string $parentId, int $columns, int $rows, callable $idFactory, string $cellText = self::CELL_TEXT): array
    {
        if ($columns < 1 || $rows < 1) {
            return $tree;
        }

        $tableId = $idFactory($parentId);
        $headSectionId = $idFactory($tableId);
        $headRowId = $idFactory($headSectionId);
        $bodySectionId = $idFactory($tableId);

        $nodes = [];

        $table = BuilderNode::make($tableId, 'table.table');
        $headSection = BuilderNode::make($headSectionId, 'table.thead');
        $headRow = BuilderNode::make($headRowId, 'table.tr');

        for ($i = 0; $i < $columns; $i++) {
            $cellId = $idFactory($headRowId);
            $textId = $idFactory($cellId);

            $nodes[$textId] = BuilderNode::make($textId, BuilderNode::TYPE_TEXT, $cellText);

            $cell = BuilderNode::make($cellId, 'table.th');
            $cell['children'] = [$textId];
            $nodes[$cellId] = $cell;

            $headRow['children'][] = $cellId;
        }

        $bodySection = BuilderNode::make($bodySectionId, 'table.tbody');

        for ($i = 0; $i < $rows; $i++) {
            $rowId = $idFactory($bodySectionId);
            $row = BuilderNode::make($rowId, 'table.tr');
            $row['children'] = [];

            for ($j = 0; $j < $columns; $j++) {
                $cellId = $idFactory($rowId);
                $textId = $idFactory($cellId);

                $nodes[$textId] = BuilderNode::make($textId, BuilderNode::TYPE_TEXT, $cellText);

                $cell = BuilderNode::make($cellId, 'table.td');
                $cell['children'] = [$textId];
                $nodes[$cellId] = $cell;

                $row['children'][] = $cellId;
            }

            $bodySection['children'][] = $rowId;
            $nodes[$rowId] = $row;
        }

        $table['children'] = [$headSectionId, $bodySectionId];
        $headSection['children'] = [$headRowId];

        $nodes[$tableId] = $table;
        $nodes[$headSectionId] = $headSection;
        $nodes[$headRowId] = $headRow;
        $nodes[$bodySectionId] = $bodySection;

        // The parent is the one place the compound can be refused, because
        // every part of the table is allowed by the one before it.
        $tree = self::addChild($tree, $parentId, $table);

        if (! isset($tree['nodes'][$tableId])) {
            return $tree;
        }

        foreach ($nodes as $id => $node) {
            $tree['nodes'][$id] = $node;
        }

        return $tree;
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

    /**
     * The id of the table a node of a table belongs to, or null when there
     * is none.
     *
     * Cells and rows are the parts of a table wherever they sit, so the
     * walk stops at the first table above the node.
     *
     * @param  Tree  $tree
     */
    private static function tableOfRow(array $tree, string $nodeId): ?string
    {
        $parentId = self::parentOf($tree, $nodeId);

        while ($parentId !== null) {
            if ($tree['nodes'][$parentId]['type'] === 'table.table') {
                return $parentId;
            }

            $parentId = self::parentOf($tree, $parentId);
        }

        return null;
    }

    /**
     * The rows of a table, and whether each one is in the head.
     *
     * A row normally sits in a section, but a table may hold a row of its
     * own, so the direct rows are collected too.
     *
     * @param  Tree  $tree
     * @return array<int, array{id: string, inHead: bool}>
     */
    private static function rowsOfTable(array $tree, string $tableId): array
    {
        $rows = [];

        foreach ($tree['nodes'][$tableId]['children'] as $sectionId) {
            $section = $tree['nodes'][$sectionId] ?? null;

            if ($section === null) {
                continue;
            }

            if ($section['type'] === 'table.tr') {
                $rows[] = ['id' => $sectionId, 'inHead' => false];

                continue;
            }

            foreach ($section['children'] as $rowId) {
                $row = $tree['nodes'][$rowId] ?? null;

                if ($row !== null && $row['type'] === 'table.tr') {
                    $rows[] = ['id' => $rowId, 'inHead' => $section['type'] === 'table.thead'];
                }
            }
        }

        return $rows;
    }

    /**
     * Whether a cell of a table spans rows or columns.
     *
     * While any cell does, the columns of the table are not a run of cells
     * the rows share, and a column operation would take the wrong cells.
     *
     * @param  Tree  $tree
     */
    private static function cellsSpan(array $tree, string $tableId): bool
    {
        foreach (self::descendantsOf($tree, $tableId) as $descendantId) {
            $node = $tree['nodes'][$descendantId] ?? null;

            if ($node === null || ! in_array($node['type'], ['table.th', 'table.td'], true)) {
                continue;
            }

            if (isset($node['attrs']['colspan']) || isset($node['attrs']['rowspan'])) {
                return true;
            }
        }

        return false;
    }
}
