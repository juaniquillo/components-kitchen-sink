<?php

declare(strict_types=1);

namespace App\Builder\Support;

use App\Builder\Contracts\BuilderNode;
use App\Builder\Registries\ComponentRegistry;

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
     * @return array{version: int, name: string, saved_at: string, tree: Tree}
     */
    public function envelope(string $name, array $tree): array
    {
        return [
            'version' => (int) config('builder.version', 1),
            'name' => $name,
            'saved_at' => now()->format('Y-m-d\TH:i:s.uP'),
            'tree' => $tree,
        ];
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

        return $this->sanitize($envelope['tree']);
    }

    /**
     * Validate a tree, dropping anything the builder would not accept.
     *
     * @return Tree|null
     */
    public function sanitize(mixed $tree): ?array
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

            $sanitized = $this->sanitizeNode($id, $node);

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
     * @return Node|null
     */
    private function sanitizeNode(string $id, mixed $node): ?array
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
        ];
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
     * Remove links to missing nodes and break cycles.
     *
     * @param  array<string, Node>  $nodes
     * @return array<string, Node>
     */
    private function prune(array $nodes, string $root): array
    {
        foreach ($nodes as $id => $node) {
            $children = [];

            foreach ($node['children'] as $childId) {
                if (! isset($nodes[$childId]) || $childId === $id) {
                    continue;
                }

                if (in_array($childId, $this->ancestorsOf($nodes, $id), true)) {
                    continue;
                }

                $children[] = $childId;
            }

            $nodes[$id]['children'] = $children;
        }

        // A node that is not reachable from the root is not part of the tree.
        $reachable = $this->descendantsOf($nodes, $root);
        $reachable[] = $root;

        $pruned = [];

        foreach ($nodes as $id => $node) {
            if (in_array($id, $reachable, true)) {
                $pruned[$id] = $node;
            }
        }

        return $pruned;
    }

    /**
     * The ids above a node, used to spot cycles.
     *
     * @param  array<string, Node>  $nodes
     * @return array<int, string>
     */
    private function ancestorsOf(array $nodes, string $nodeId): array
    {
        $ancestors = [];

        foreach ($nodes as $id => $node) {
            if (in_array($nodeId, $node['children'], true)) {
                $ancestors[] = $id;
                $ancestors = array_merge($ancestors, $this->ancestorsOf($nodes, $id));
            }
        }

        return $ancestors;
    }

    /**
     * The ids below a node.
     *
     * @param  array<string, Node>  $nodes
     * @return array<int, string>
     */
    private function descendantsOf(array $nodes, string $nodeId): array
    {
        $descendants = [];

        foreach ($nodes[$nodeId]['children'] ?? [] as $childId) {
            if (! isset($nodes[$childId]) || in_array($childId, $descendants, true)) {
                continue;
            }

            $descendants[] = $childId;
            $descendants = array_merge($descendants, $this->descendantsOf($nodes, $childId));
        }

        return $descendants;
    }
}
