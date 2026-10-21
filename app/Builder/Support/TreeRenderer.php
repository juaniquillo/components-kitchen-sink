<?php

declare(strict_types=1);

namespace App\Builder\Support;

use App\Builder\Contracts\BuilderNode;
use App\Builder\Registries\ComponentRegistry;
use Juaniquillo\BackendComponents\Enums\ComponentEnum;
use Juaniquillo\BackendComponents\MainBackendComponent;

/**
 * Renders a builder tree using the backend-component package.
 *
 * The tree is turned into real component objects, so the canvas is the
 * genuine article — the same code the generator emits, rendered by the same
 * views.
 *
 * The wire:sort attributes are injected as ordinary component attributes.
 * The package's views render attributes onto the element, which is what makes
 * every node addressable for drag and drop without any custom markup.
 *
 * @phpstan-import-type Node from BuilderNode
 * @phpstan-import-type Tree from BuilderNode
 */
final class TreeRenderer
{
    /**
     * @param  array<string, array<string, string>>  $themes  The themes, by group and variant.
     */
    public function __construct(private readonly array $themes = []) {}

    /**
     * The group every container belongs to, so nodes can be dragged between them.
     */
    public const GROUP = 'builder';

    /**
     * The SortableJS options a container is sorted with.
     *
     * The chrome is the only child of a container that is not a sortable
     * item, and it must not be a drag candidate: SortableJS works out what
     * to drag by walking up from the handle to the first child of the
     * container that matches its draggable selector, so a draggable chrome
     * would be dragged instead of the component it belongs to.
     */
    private const SORT_CONFIG = "{ draggable: '>:not(.bw-chrome)' }";

    /**
     * Parents that will not accept a span, so a component that cannot hold
     * the chrome of its own is left without one.
     *
     * @var array<int, string>
     */
    private const WRAPPER_BLOCKED_PARENTS = [
        'form.select',
        'form.optgroup',
        'table.colgroup',
        'table.table',
    ];

    /**
     * Render a whole tree to HTML.
     *
     * A clean render leaves out everything the builder adds — the chrome, the
     * reserved row, the minimum sizes and the wire attributes — so it is
     * exactly what the generated code produces.
     *
     * @param  Tree  $tree
     */
    public function render(array $tree, ?string $selectedId = null, bool $clean = false): string
    {
        return $this->build($tree, $tree['root'], $selectedId, $clean)->toHtml();
    }

    /**
     * Build the component for a node and everything below it.
     *
     * @param  Tree  $tree
     */
    private function build(array $tree, string $nodeId, ?string $selectedId, bool $clean, ?string $parentType = null): MainBackendComponent
    {
        $node = $tree['nodes'][$nodeId];

        $hostsOwnChrome = $this->canHoldChrome($node);
        $canBeWrapped = $this->canBeWrapped($parentType);

        // The root of the tree has nowhere to be moved to, so it is left
        // without a handle.
        $draggable = $node['id'] !== $tree['root'];

        // A component that cannot hold a chrome of its own is given one, so
        // that it can be wrapped in something that can.
        $chrome = ($clean || (! $hostsOwnChrome && ! $canBeWrapped))
            ? null
            : NodeChrome::build($node, $draggable);

        if ($chrome !== null && ! $hostsOwnChrome) {
            return $this->buildWrapped($tree, $node, $chrome, $selectedId, $clean);
        }

        $component = new MainBackendComponent($node['type']);

        $component->setAttributes($this->attributes($tree, $node, $selectedId, $clean));
        $component->setContents($this->contents($tree, $node, $selectedId, $clean, $chrome));

        return $component;
    }

    /**
     * Wrap a component that cannot hold the chrome, so it still gets a toolbar.
     *
     * The wrapper is the sortable item, and the component sits inside it.
     *
     * @param  Tree  $tree
     * @param  Node  $node
     */
    private function buildWrapped(array $tree, array $node, MainBackendComponent $chrome, ?string $selectedId, bool $clean): MainBackendComponent
    {
        $wrapper = new MainBackendComponent(ComponentEnum::SPAN);

        $wrapper->setAttributes([
            'wire:sort:item' => $node['id'],
            'wire:key' => $node['id'],
            'class' => 'bw-wrap',
            'style' => 'position: relative; display: inline-block; padding-top: 1.75rem;',
        ]);

        $inner = new MainBackendComponent($node['type']);

        $innerAttributes = [
            ...$this->themedAttributes($node),
            'data-node-id' => $node['id'],
            'wire:click.stop' => "select('".$node['id']."')",
        ];

        if ($selectedId === $node['id']) {
            $innerAttributes['data-selected'] = 'true';
        }

        $style = $this->style($node);

        if ($style !== null && ! isset($innerAttributes['style'])) {
            $innerAttributes['style'] = $style;
        }

        $inner->setAttributes($innerAttributes);

        $inner->setContents($this->contents($tree, $node, $selectedId, $clean, null));

        $wrapper->setContents([$inner, $chrome]);

        return $wrapper;
    }

    /**
     * The contents of a node: the chrome, then its text segments and children
     * in the order they were arranged.
     *
     * @param  Tree  $tree
     * @param  Node  $node
     * @return array<int, string|MainBackendComponent>
     */
    private function contents(array $tree, array $node, ?string $selectedId, bool $clean, ?MainBackendComponent $chrome): array
    {
        $contents = [];

        if ($chrome !== null) {
            $contents[] = $chrome;
        }

        foreach ($node['children'] as $childId) {
            $child = $tree['nodes'][$childId] ?? null;

            if ($child === null) {
                continue;
            }

            if ($child['type'] === BuilderNode::TYPE_TEXT) {
                $contents[] = $child['text'] ?? '';

                continue;
            }

            $contents[] = $this->build($tree, $childId, $selectedId, $clean, $node['type']);
        }

        return $contents;
    }

    /**
     * The attributes of a node, with the builder's own attributes injected.
     *
     * The order matters: Livewire's sort binding walks the attributes in DOM
     * order and stops at wire:sort:group, so wire:sort has to come first.
     *
     * @param  Tree  $tree
     * @param  Node  $node
     * @return array<string, string>
     */
    private function attributes(array $tree, array $node, ?string $selectedId, bool $clean): array
    {
        if ($clean) {
            return $this->themedAttributes($node);
        }

        $attributes = [];

        if ($node['id'] !== $tree['root']) {
            $attributes['wire:sort:item'] = $node['id'];
        }

        $attributes['wire:key'] = $node['id'];

        if ($this->isContainer($node)) {
            $attributes['wire:sort'] = 'handleSort';
            $attributes['wire:sort:group'] = self::GROUP;
            $attributes['wire:sort:group-id'] = $node['id'];
            // It has to come after wire:sort:group, because Livewire's sort
            // binding walks the attributes in DOM order and stops there.
            $attributes['wire:sort:config'] = self::SORT_CONFIG;
        }

        $attributes['wire:click.stop.prevent'] = "select('".$node['id']."')";
        $attributes['data-node-id'] = $node['id'];

        if ($selectedId === $node['id']) {
            $attributes['data-selected'] = 'true';
        }

        if (! $this->canHoldChrome($node)) {
            // Without a toolbar of its own the node would have no handle,
            // so it is its own handle and can be dragged from anywhere.
            $attributes['wire:sort:handle'] = '';
        }

        $style = $this->style($node);

        if ($style !== null) {
            $attributes['style'] = $style;
        }

        return array_merge($attributes, $this->themedAttributes($node));
    }

    /**
     * The attributes of a node with the classes of its themes merged in.
     *
     * The theme classes come after the ones the user typed, which is the
     * order the package merges them in.
     *
     * @param  Node  $node
     * @return array<string, string>
     */
    private function themedAttributes(array $node): array
    {
        $attributes = $node['attrs'];

        $classes = $this->themeClasses($node);

        if ($classes === '') {
            return $attributes;
        }

        $attributes['class'] = trim(($attributes['class'] ?? '').' '.$classes);

        return $attributes;
    }

    /**
     * The classes of the themes applied to a node.
     *
     * A group holds a list of variants, and every one of them contributes
     * its classes, in the order they were applied. A theme that is not in
     * the registry is skipped: it may have been removed after the node was
     * given it.
     *
     * @param  Node  $node
     */
    private function themeClasses(array $node): string
    {
        $classes = [];

        foreach ($node['theme'] as $group => $variants) {
            foreach ($variants as $variant) {
                $classes[] = $this->themes[$group][$variant] ?? '';
            }
        }

        return trim(implode(' ', array_filter($classes)));
    }

    /**
     * The style the builder adds to a node in the canvas.
     *
     * @param  Node  $node
     */
    private function style(array $node): ?string
    {
        $style = $this->chromeStyle($node).$this->boxStyle($node);

        return $style === '' ? null : $style;
    }

    /**
     * The style that keeps the chrome of a node away from its content.
     *
     * @param  Node  $node
     */
    private function chromeStyle(array $node): string
    {
        if (! $this->canHoldChrome($node)) {
            return '';
        }

        // The chrome is positioned against the node it belongs to, and the
        // node keeps a row free for it so the chrome never lands on the
        // content or on a neighbouring node's chrome.
        return 'position: relative; padding-top: 1.75rem;';
    }

    /**
     * The style that gives an element with no content a box to be seen in.
     *
     * @param  Node  $node
     */
    private function boxStyle(array $node): string
    {
        if (! ComponentRegistry::isInline($node['type']) || $node['children'] !== []) {
            return '';
        }

        // An empty inline element has no box at all, so it is given one to
        // make it visible and to give its toolbar something to sit against.
        return 'display: inline-block; min-width: 4rem; min-height: 1.5rem;';
    }

    /**
     * Whether a node holds the chrome of its own.
     *
     * @param  Node  $node
     */
    private function canHoldChrome(array $node): bool
    {
        return NodeChrome::canHold($node['type']);
    }

    /**
     * Whether a wrapper is allowed where the node is going.
     */
    private function canBeWrapped(?string $parentType): bool
    {
        return ! in_array($parentType, self::WRAPPER_BLOCKED_PARENTS, true);
    }

    /**
     * Whether a node holds children, and so is a drop target.
     *
     * @param  Node  $node
     */
    private function isContainer(array $node): bool
    {
        $descriptor = ComponentRegistry::get($node['type']);

        return $descriptor !== null && $descriptor->acceptsChildren();
    }
}
