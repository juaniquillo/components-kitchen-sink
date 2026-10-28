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
 * A renderer given an id scope hands out that scope to the id-like
 * attributes the tree carries, so a tree can be rendered twice on one page
 * — a canvas and its preview — without one copy taking the way of the
 * other. Only a second copy is rendered with one: the code generator and
 * the canvas keep the ids the user typed.
 *
 * @phpstan-import-type Node from BuilderNode
 * @phpstan-import-type Tree from BuilderNode
 */
final class TreeRenderer
{
    /**
     * @param  array<string, array<string, string>>  $themes  The themes, by group and variant.
     */
    public function __construct(private readonly array $themes = [], private readonly ?string $idScope = null) {}

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
     * @param  bool  $parentFlowsAcross  Whether the parent lays its children
     *                                   out across its own row, so a wrapper around the node would take the
     *                                   place of the node in that row.
     */
    private function build(array $tree, string $nodeId, ?string $selectedId, bool $clean, ?string $parentType = null, bool $parentFlowsAcross = false): MainBackendComponent
    {
        $node = $tree['nodes'][$nodeId];

        $hostsOwnChrome = $this->canHoldChrome($node);
        $canBeWrapped = $this->canBeWrapped($parentType);

        // The root of the tree has nowhere to be moved to, so it is left
        // without a handle.
        $draggable = $node['id'] !== $tree['root'];

        // A node whose own display lays its children out across its row —
        // a flex, a grid, columns — cannot hold a bar in its flow: the bar
        // would become a part of that row. When its own parent does not
        // flow across as well, the node is wrapped, and its chrome rides
        // in the wrapper above it, the way the chrome of any other node
        // does. Only a node inside another flowing row keeps a floating
        // chrome anchored to its bottom right corner, because a wrapper
        // there would take the place of the node in the row of the parent,
        // and its grow and alignment would be lost with it.
        $flowsAcross = $hostsOwnChrome && $this->flowsAcross($node);
        $overlay = $flowsAcross && ($parentFlowsAcross || ! $canBeWrapped);

        // A component that cannot hold a chrome of its own is given one, so
        // that it can be wrapped in something that can. A row is the other
        // exception: its chrome is a row of its own, which the section the
        // row belongs to renders after it.
        $chrome = ($clean
            || $node['type'] === 'table.tr'
            || (! $hostsOwnChrome && ! $canBeWrapped))
            ? null
            : NodeChrome::build(
                $node,
                $draggable,
                $this->childTypes($tree, $node),
                $overlay,
            );

        if ($chrome !== null && ! $hostsOwnChrome) {
            return $this->buildWrapped($tree, $node, $chrome, $selectedId, $clean);
        }

        if ($chrome !== null && $flowsAcross && ! $overlay) {
            return $this->buildWrapped($tree, $node, $chrome, $selectedId, $clean, block: true);
        }

        $component = new MainBackendComponent($node['type']);

        $component->setAttributes($this->attributes($tree, $node, $selectedId, $clean, $overlay));
        $component->setContents($this->contents($tree, $node, $selectedId, $clean, $chrome));

        return $component;
    }

    /**
     * The types of the children a node holds, for the chrome of its menu.
     *
     * @param  Tree  $tree
     * @param  Node  $node
     * @return array<int, string>
     */
    private function childTypes(array $tree, array $node): array
    {
        $types = [];

        foreach ($node['children'] as $childId) {
            if (isset($tree['nodes'][$childId])) {
                $types[] = $tree['nodes'][$childId]['type'];
            }
        }

        return $types;
    }

    /**
     * Wrap a component that cannot hold the chrome, so it still gets a toolbar.
     *
     * The wrapper is the sortable item, and the component sits inside it.
     *
     * @param  Tree  $tree
     * @param  array<string, mixed>  $node
     * @param  bool  $block  Whether the wrapper is a block of its own, so a
     *                       node that lays its children out across a row gets its chrome in a row
     *                       above it instead of a floating toolbar anchored to its corner.
     */
    private function buildWrapped(array $tree, array $node, MainBackendComponent $chrome, ?string $selectedId, bool $clean, bool $block = false): MainBackendComponent
    {
        // The wrapper of a node that cannot hold the chrome is a span, so
        // it can sit in the middle of a line of text like the component it
        // wraps does. The wrapper of a node that flows across its own row
        // is a block, so the chrome of the node is a row of its own above
        // the row of the node.
        $wrapper = new MainBackendComponent($block ? ComponentEnum::DIV : ComponentEnum::SPAN);

        $wrapperAttributes = [
            'wire:sort:item' => $node['id'],
            'wire:key' => $node['id'],
            'class' => 'bw-wrap',
            'style' => $block ? 'display: block;' : 'display: inline-block;',
        ];

        // The chrome of the wrapped node hangs on the wrapper, so the
        // selection is visible to it as well. The inner element keeps its
        // own marker for the outline and the drawer.
        if ($selectedId === $node['id']) {
            $wrapperAttributes['data-selected'] = 'true';
        }

        $wrapper->setAttributes($wrapperAttributes);

        $inner = new MainBackendComponent($node['type']);

        $innerAttributes = [
            // A wrapped node that takes children stays a container the
            // canvas can drop into and whose children can be sorted: the
            // wrapper is the sortable item of the parent, and the node
            // inside it holds the group its own children sort in. Without
            // these, a drag inside the node would be caught by the nearest
            // container above it, and the whole node would be dragged.
            ...$this->sortAttributes($node),
            ...$this->themedAttributes($node),
            'data-node-id' => $node['id'],
            // The same prevention the nodes that hold a chrome of their
            // own carry: a click in the canvas selects, and does not
            // toggle the checkbox the click landed on or submit the form
            // the button it landed on belongs to.
            'wire:click.stop.prevent' => "select('".$node['id']."')",
        ];

        if ($selectedId === $node['id']) {
            $innerAttributes['data-selected'] = 'true';
        }

        $style = $this->style($node);

        if ($style !== null) {
            $existing = $node['attrs']['style'] ?? '';

            $innerAttributes['style'] = $existing === ''
                ? $style
                : rtrim(rtrim($existing, '; '), '; ').'; '.$style;
        }

        $inner->setAttributes($innerAttributes);

        $inner->setContents($this->contents($tree, $node, $selectedId, $clean, null));

        $wrapper->setContents([$chrome, $inner]);

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

            $contents[] = $this->build($tree, $childId, $selectedId, $clean, $node['type'], $this->flowsAcross($node));

            // A row's chrome cannot live in the row: a row holds nothing
            // but cells, and a chrome cell in the flow of a row would take
            // a column of the table. So the row is followed by a chrome row
            // of its own.
            if ($child['type'] === 'table.tr' && ! $clean) {
                $contents[] = NodeChrome::build(
                    $child,
                    $childId !== $tree['root'],
                    $this->childTypes($tree, $child),
                );
            }
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
     * @param  bool  $positioned  Whether the node carries a floating chrome
     *                            that anchors to its bottom right corner, and so needs a positioning
     *                            of its own to anchor to.
     * @return array<string, string>
     */
    private function attributes(array $tree, array $node, ?string $selectedId, bool $clean, bool $positioned = false): array
    {
        if ($clean) {
            return $this->themedAttributes($node);
        }

        $attributes = [];

        if ($node['id'] !== $tree['root']) {
            $attributes['wire:sort:item'] = $node['id'];
        }

        $attributes = [
            ...$attributes,
            'wire:key' => $node['id'],
            ...$this->sortAttributes($node),
            'wire:click.stop.prevent' => "select('".$node['id']."')",
            'data-node-id' => $node['id'],
        ];

        if ($selectedId === $node['id']) {
            $attributes['data-selected'] = 'true';
        }

        if (! $this->canHoldChrome($node)) {
            // Without a toolbar of its own the node would have no handle,
            // so it is its own handle and can be dragged from anywhere.
            $attributes['wire:sort:handle'] = '';
        }

        $attributes = array_merge($attributes, $this->themedAttributes($node));

        $style = $this->style($node, $positioned);

        if ($style !== null) {
            // A style on the node's own attributes stays first, and the
            // builder's is appended, so the positioning the chrome needs
            // never rewrites what the node already says.
            $existing = $node['attrs']['style'] ?? '';

            $attributes['style'] = $existing === ''
                ? $style
                : rtrim(rtrim($existing, '; '), '; ').'; '.$style;
        }

        return $attributes;
    }

    /**
     * The attributes that make a node a live container in the canvas.
     *
     * They are kept in one place because the order matters: Livewire's
     * sort binding walks the attributes in DOM order and stops at
     * wire:sort:group, so wire:sort has to come first.
     *
     * @param  Node  $node
     * @return array<string, string>
     */
    private function sortAttributes(array $node): array
    {
        if (! $this->isContainer($node)) {
            return [];
        }

        return [
            'wire:sort' => 'handleSort',
            'wire:sort:group' => self::GROUP,
            'wire:sort:group-id' => $node['id'],
            'wire:sort:config' => self::SORT_CONFIG,
        ];
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
        $attributes = $this->scopedAttributes($node['attrs']);

        $classes = $this->themeClasses($node);

        if ($classes === '') {
            return $attributes;
        }

        $attributes['class'] = trim(($attributes['class'] ?? '').' '.$classes);

        return $attributes;
    }

    /**
     * The attributes of a node, with the id-like ones under the renderer's
     * scope.
     *
     * When a tree is rendered twice on one page — a canvas and its preview
     * — the ids of the second copy would take the way of the first: a label
     * points at the first element with its `for`, which would be the copy
     * that came before it. The renderer that carries a scope prefixes the
     * value of every id-like attribute with it. An empty value is left
     * alone: there is nothing to scope.
     *
     * @param  array<string, string>  $attributes
     * @return array<string, string>
     */
    private function scopedAttributes(array $attributes): array
    {
        if ($this->idScope === null) {
            return $attributes;
        }

        $scoped = [];

        foreach ($attributes as $name => $value) {
            if (in_array($name, BuilderNode::ID_ATTRIBUTES, true) && trim($value) !== '') {
                $ids = $name === 'headers'
                    ? array_map($this->scopedId(...), preg_split('/\s+/', trim($value), -1, PREG_SPLIT_NO_EMPTY) ?: [])
                    : [$this->scopedId(trim($value))];

                $scoped[$name] = implode(' ', $ids);

                continue;
            }

            $scoped[$name] = $value;
        }

        return $scoped;
    }

    /**
     * One id under the renderer's scope.
     */
    private function scopedId(string $id): string
    {
        return $this->idScope.$id;
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
     * A node with a floating chrome anchored to its bottom right corner is
     * positioned, so the chrome has something to anchor to. A node whose
     * chrome rides in a wrapper above it stays the element it is. An empty
     * inline element still needs a box to be seen in.
     *
     * @param  Node  $node
     * @param  bool  $positioned  Whether the node carries a floating chrome.
     */
    private function style(array $node, bool $positioned = false): ?string
    {
        $style = $this->boxStyle($node);

        if ($positioned) {
            $style = 'position: relative; '.trim($style);
        }

        return $style === '' ? null : $style;
    }

    /**
     * Whether a node lays its children out across its own row.
     *
     * The classes of the node's own attributes and themes decide where its
     * first child — with the chrome generally that first child — lands. A
     * flex row, a grid or a column layout puts every child in its own
     * track flowing across the node, so a chrome in the flow would become
     * a part of that row or grid instead of sitting above the content.
     * Such a cluster is covered by the tokens below, and a flex that
     * stacks its children stays a flow that a bar fits into.
     *
     * @param  Node  $node
     */
    private function flowsAcross(array $node): bool
    {
        $classes = trim(($node['attrs']['class'] ?? '').' '.$this->themeClasses($node));

        if ($classes === '') {
            return false;
        }

        $tokens = preg_split('/\s+/', $classes, -1, PREG_SPLIT_NO_EMPTY) ?: [];

        $across = [
            'flex',
            'inline-flex',
            'grid',
            'inline-grid',
            'columns',
        ];

        // The multi column layout names its column count, so the token has
        // to match the prefix as well.
        $spreads = (bool) array_intersect($tokens, $across) || (bool) array_filter(
            $tokens,
            fn (string $token): bool => str_starts_with($token, 'columns-'),
        );

        $stacks = (bool) array_intersect($tokens, ['flex-col', 'flex-col-reverse']);

        return $spreads && ! $stacks;
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
