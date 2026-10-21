<?php

declare(strict_types=1);

namespace App\Builder\Support;

use App\Builder\Components\ComponentDescriptor;
use App\Builder\Contracts\BuilderNode;
use App\Builder\Registries\ComponentRegistry;
use App\Builder\Registries\ThemeRegistry;

/**
 * Reads and writes the on-disk format for a saved composition.
 *
 * A saved file is data, never code. Everything read back is validated against
 * the registry before it reaches the builder, so a hand-edited file cannot
 * smuggle in an unknown component or an attribute the component does not
 * accept.
 *
 * @phpstan-import-type Node from BuilderNode
 * @phpstan-import-type Tree from BuilderNode
 */
final class CompositionSerializer
{
    /**
     * Wrap a tree in the envelope that gets written to disk.
     *
     * @param  Tree  $tree
     * @param  array<string, array<string, string>>  $themes
     * @return array{version: int, name: string, saved_at: string, tree: Tree, themes: array<string, array<string, string>>}
     */
    public function envelope(string $name, array $tree, array $themes = []): array
    {
        return [
            'version' => (int) config('builder.version', 1),
            'name' => $name,
            'saved_at' => now()->format('Y-m-d\TH:i:s.uP'),
            'tree' => $tree,
            'themes' => $themes,
        ];
    }

    /**
     * Pull the themes back out of an envelope, discarding anything invalid.
     *
     * @return array<string, array<string, string>>
     */
    public function themes(mixed $envelope): array
    {
        if (! is_array($envelope)) {
            return [];
        }

        return ThemeRegistry::sanitize($envelope['themes'] ?? []);
    }

    /**
     * Pull the tree back out of an envelope, discarding anything invalid.
     *
     * @return Tree|null
     */
    public function tree(mixed $envelope): ?array
    {
        if (! is_array($envelope) || ! isset($envelope['tree'])) {
            return null;
        }

        return $this->sanitize($envelope['tree'], $this->themes($envelope));
    }

    /**
     * Validate a tree, dropping anything the builder would not accept.
     *
     * @param  array<string, array<string, string>>  $themes
     * @return Tree|null
     */
    public function sanitize(mixed $tree, array $themes = []): ?array
    {
        if (! is_array($tree) || ! isset($tree['root'], $tree['nodes'])) {
            return null;
        }

        if (! is_string($tree['root']) || ! is_array($tree['nodes'])) {
            return null;
        }

        $nodes = [];

        foreach ($tree['nodes'] as $id => $node) {
            if (! is_string($id) || ! is_array($node)) {
                continue;
            }

            $sanitized = $this->sanitizeNode($id, $node, $themes);

            if ($sanitized !== null) {
                $nodes[$id] = $sanitized;
            }
        }

        if (! isset($nodes[$tree['root']])) {
            return null;
        }

        // Drop links to nodes that do not exist, and break any cycles.
        $nodes = $this->prune($nodes, $tree['root']);

        return [
            'root' => $tree['root'],
            'nodes' => $nodes,
        ];
    }

    /**
     * Validate a single node.
     *
     * @param  array<string, array<string, string>>  $themes
     * @return Node|null
     */
    private function sanitizeNode(string $id, mixed $node, array $themes): ?array
    {
        if (! is_array($node) || ! isset($node['type']) || ! is_string($node['type'])) {
            return null;
        }

        $text = null;

        if (isset($node['text']) && is_string($node['text'])) {
            $text = $node['text'];
        }

        if ($node['type'] === BuilderNode::TYPE_TEXT) {
            return [
                'id' => $id,
                'type' => BuilderNode::TYPE_TEXT,
                'text' => $text ?? '',
                'attrs' => [],
                'children' => [],
                'theme' => [],
            ];
        }

        $descriptor = ComponentRegistry::get($node['type']);

        if ($descriptor === null) {
            return null;
        }

        return [
            'id' => $id,
            'type' => $node['type'],
            'text' => $text,
            'attrs' => $this->sanitizeAttributes($node['attrs'] ?? []),
            'children' => $this->sanitizeChildren($node['children'] ?? []),
            'theme' => $this->sanitizeReferences($node['theme'] ?? [], $themes),
        ];
    }

    /**
     * Keep only the theme references that point at a theme that is there.
     *
     * A reference names a group and the variants of it. Both are checked,
     * and so is every variant, because a saved file is data and could have
     * been written by hand.
     *
     * A group used to hold a single variant, so a file written before a
     * group could hold several is still read: the single variant becomes a
     * list of one.
     *
     * @param  array<string, array<string, string>>  $themes
     * @return array<string, array<int, string>>
     */
    private function sanitizeReferences(mixed $references, array $themes): array
    {
        if (! is_array($references)) {
            return [];
        }

        $result = [];

        foreach ($references as $group => $variants) {
            if (! is_string($group)) {
                continue;
            }

            if (! ThemeRegistry::allowsName($group)) {
                continue;
            }

            foreach ((array) $variants as $variant) {
                if (! is_string($variant) || ! ThemeRegistry::allowsName($variant)) {
                    continue;
                }

                if (! isset($themes[$group][$variant])) {
                    continue;
                }

                $result[$group][] = $variant;
            }
        }

        return $result;
    }

    /**
     * Keep only the attributes that pass the guard.
     *
     * A saved file is data, so it could have been written by hand. The guard
     * is what stops a hand written file from smuggling in an event handler or
     * a Livewire directive.
     *
     * @return array<string, string>
     */
    private function sanitizeAttributes(mixed $attributes): array
    {
        if (! is_array($attributes)) {
            return [];
        }

        $result = [];

        foreach ($attributes as $name => $value) {
            if (! is_string($name) || ! is_string($value)) {
                continue;
            }

            if (! AttributeGuard::allows($name)) {
                continue;
            }

            $result[$name] = $value;
        }

        return $result;
    }

    /**
     * Keep only the child ids that are strings.
     *
     * @return array<int, string>
     */
    private function sanitizeChildren(mixed $children): array
    {
        if (! is_array($children)) {
            return [];
        }

        $result = [];

        foreach ($children as $child) {
            if (is_string($child)) {
                $result[] = $child;
            }
        }

        return $result;
    }

    /**
     * Remove links to missing nodes, break any cycles and keep every node in
     * the hands of one parent only.
     *
     * A node that is held by two parents would be drawn twice, and the page
     * would not be the tree the builder holds: the two copies carry the same
     * id, and the browser takes the invalid nesting apart.
     *
     * @param  array<string, Node>  $nodes
     * @return array<string, Node>
     */
    private function prune(array $nodes, string $root): array
    {
        foreach ($nodes as $id => $node) {
            $children = [];

            $descriptor = ComponentRegistry::get($node['type']);

            foreach ($node['children'] as $childId) {
                if (! isset($nodes[$childId]) || $childId === $id) {
                    continue;
                }

                if (! $this->allowsChild($descriptor, $nodes[$childId]['type'])) {
                    continue;
                }

                $children[] = $childId;
            }

            $nodes[$id]['children'] = $children;
        }

        // The walk from the root is what decides: the first parent to reach
        // a node keeps it, and a node that is never reached is not part of
        // the tree.
        $claimed = [$root => true];
        $queue = [$root];

        while ($queue !== []) {
            $id = array_shift($queue);
            $children = [];

            foreach ($nodes[$id]['children'] as $childId) {
                if (isset($claimed[$childId])) {
                    continue;
                }

                $claimed[$childId] = true;
                $children[] = $childId;
                $queue[] = $childId;
            }

            $nodes[$id]['children'] = $children;
        }

        $pruned = [];

        foreach ($nodes as $id => $node) {
            if (isset($claimed[$id])) {
                $pruned[$id] = $node;
            }
        }

        return $pruned;
    }

    /**
     * Whether a parent may hold the given child.
     *
     * A component the parent does not accept would be taken out of it by the
     * browser, and the tree would come apart. A text segment is content and
     * not a component, so the parser never moves it: it only has to be
     * somewhere that holds content at all.
     */
    private function allowsChild(?ComponentDescriptor $descriptor, string $childType): bool
    {
        if ($descriptor === null) {
            return true;
        }

        if ($childType === BuilderNode::TYPE_TEXT) {
            return $descriptor->acceptsContent();
        }

        return $descriptor->allowsChild($childType);
    }
}
