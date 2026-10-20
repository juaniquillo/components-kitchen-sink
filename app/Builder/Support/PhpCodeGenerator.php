<?php

declare(strict_types=1);

namespace App\Builder\Support;

use App\Builder\Contracts\BuilderNode;
use App\Builder\Registries\ComponentRegistry;

/**
 * Turns a builder tree into the PHP that reproduces it.
 *
 * The output is meant to be pasted straight into a project:
 *
 *     ComponentBuilder::make(ComponentEnum::DIV)
 *         ->setAttributes([
 *             'id' => 'hero',
 *         ])
 *         ->setContents([
 *             ComponentBuilder::make(ComponentEnum::H1)
 *                 ->setContent('Welcome'),
 *         ]);
 *
 * @phpstan-import-type Node from BuilderNode
 * @phpstan-import-type Tree from BuilderNode
 */
final class PhpCodeGenerator
{
    private const INDENT = '    ';

    /**
     * A complete, pasteable snippet including the imports.
     *
     * @param  Tree  $tree
     */
    public function snippet(array $tree): string
    {
        return implode("\n", [
            'use Juaniquillo\BackendComponents\Builders\ComponentBuilder;',
            'use Juaniquillo\BackendComponents\Enums\ComponentEnum;',
            '',
            '$component = '.$this->expression($tree, $tree['root']).';',
        ]);
    }

    /**
     * The builder chain for a node, without any surrounding assignment.
     *
     * @param  Tree  $tree
     */
    public function expression(array $tree, string $nodeId): string
    {
        $node = $tree['nodes'][$nodeId] ?? null;

        if ($node === null) {
            return '// unknown node: '.$nodeId;
        }

        $type = ComponentRegistry::enumCase($node['type']);

        if ($type === null) {
            return '// unknown component type: '.$node['type'];
        }

        $lines = ['ComponentBuilder::make(ComponentEnum::'.$type.')'];

        $attributes = $this->attributes($node);
        if ($attributes !== []) {
            $lines[] = '->setAttributes([';
            foreach ($attributes as $name => $value) {
                $lines[] = self::INDENT."'".$name."' => ".$this->literal($value).',';
            }
            $lines[] = '])';
        }

        if ($node['text'] !== null && $node['text'] !== '' && $node['type'] === BuilderNode::TYPE_TEXT) {
            $lines[] = '->setContent('.$this->literal($node['text']).')';
        }

        $children = $this->children($tree, $node);

        if ($children !== []) {
            $lines[] = '->setContents([';
            foreach ($children as $child) {
                $lines[] = self::INDENT.$this->childExpression($tree, $child).',';
            }
            $lines[] = '])';
        }

        return implode("\n", $lines);
    }

    /**
     * The children of a node that actually exist in the tree.
     *
     * @param  Tree  $tree
     * @param  Node  $node
     * @return array<int, string>
     */
    private function children(array $tree, array $node): array
    {
        $children = [];

        foreach ($node['children'] as $childId) {
            if (isset($tree['nodes'][$childId])) {
                $children[] = $childId;
            }
        }

        return $children;
    }

    /**
     * A child expression, indented one level deeper than its parent.
     *
     * @param  Tree  $tree
     */
    private function childExpression(array $tree, string $nodeId): string
    {
        $node = $tree['nodes'][$nodeId] ?? null;

        if ($node !== null && $node['type'] === BuilderNode::TYPE_TEXT) {
            return $this->literal($node['text'] ?? '');
        }

        return str_replace("\n", "\n".self::INDENT, $this->expression($tree, $nodeId));
    }

    /**
     * The attributes of a node, minus anything the builder injected itself.
     *
     * @param  Node  $node
     * @return array<string, string>
     */
    private function attributes(array $node): array
    {
        $attributes = [];

        foreach ($node['attrs'] as $name => $value) {
            if ($this->isBuilderAttribute($name)) {
                continue;
            }

            $attributes[$name] = $value;
        }

        return $attributes;
    }

    /**
     * Whether an attribute was injected by the builder rather than chosen by
     * the user.
     *
     * The builder's own attributes never reach the tree, so this only has to
     * catch a hand edited file that slipped past the guard. Everything else
     * the user typed belongs in the generated code.
     */
    private function isBuilderAttribute(string $name): bool
    {
        return str_starts_with($name, 'wire:')
            || str_starts_with($name, 'x-');
    }

    /**
     * A PHP literal for a value.
     *
     * var_export() is used because it round-trips: single quotes and
     * backslashes are escaped, and newlines stay literal, which is still
     * valid inside a single quoted PHP string.
     *
     * @param  string|int|array<int, string>  $value
     */
    private function literal(string|int|array $value): string
    {
        if (is_array($value)) {
            $parts = [];

            foreach ($value as $item) {
                $parts[] = $this->literal($item);
            }

            return '['.implode(', ', $parts).']';
        }

        return var_export($value, true);
    }
}
