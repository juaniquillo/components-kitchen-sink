<?php

declare(strict_types=1);

namespace App\Livewire\Builder;

use App\Builder\Components\ComponentDescriptor;
use App\Builder\Contracts\BuilderNode;
use App\Builder\Contracts\SavedComposition;
use App\Builder\Registries\ComponentRegistry;
use App\Builder\Registries\ThemeRegistry;
use App\Builder\Support\AttributeGuard;
use App\Builder\Support\ClassList;
use App\Builder\Support\CompositionRepository;
use App\Builder\Support\PhpCodeGenerator;
use App\Builder\Support\TreeMutator;
use App\Builder\Support\TreeRenderer;
use Livewire\Attributes\Computed;
use Livewire\Component;

/**
 * The drag and drop component builder.
 *
 * The tree is the single source of truth: the canvas is rendered from it by
 * the backend-component package, and the code panel is generated from it.
 * Drag and drop is handled by Livewire's wire:sort, which reports the
 * dropped item, its new position and the container it landed in.
 *
 * @phpstan-import-type Node from BuilderNode
 * @phpstan-import-type Tree from BuilderNode
 */
class BuilderCanvas extends Component
{
    /**
     * The composed tree.
     *
     * @var Tree
     */
    public array $tree;

    /**
     * The themes the user has defined, by group and variant.
     *
     * A node holds a reference to a theme rather than a copy of its classes,
     * so one theme can be used by as many components as the user likes.
     *
     * @var array<string, array<string, string>>
     */
    public array $themes = [];

    /**
     * The group of the theme waiting to be added or changed.
     */
    public string $themeGroup = '';

    /**
     * The variant of the theme waiting to be added or changed.
     */
    public string $themeVariant = '';

    /**
     * The classes of the theme waiting to be added or changed.
     */
    public string $themeClasses = '';

    /**
     * The id of the node the inspector is showing.
     */
    public ?string $selectedId = null;

    /**
     * The id of the node the drawer is configuring.
     */
    public ?string $configuringId = null;

    /**
     * The name given to the composition when it is saved.
     */
    public string $compositionName = '';

    /**
     * The attribute name waiting to be added or updated.
     */
    public string $attributeName = '';

    /**
     * The attribute value waiting to be added or updated.
     */
    public string $attributeValue = '';

    /**
     * The attribute currently being edited, if any.
     */
    public ?string $editingAttribute = null;

    /**
     * The text waiting to be added to the node the drawer is configuring.
     */
    public string $textDraft = '';

    /**
     * Why the last attribute was refused, if it was.
     *
     * The guard refuses a name for a reason, and a field that empties itself
     * without saying why reads like a bug.
     */
    public ?string $attributeNotice = null;

    /**
     * Why the last theme was refused, if it was.
     */
    public ?string $themeNotice = null;

    /**
     * How many nodes have been created, used to hand out unique ids.
     */
    public int $nodeCounter = 0;

    public function mount(): void
    {
        $this->tree = BuilderNode::tree($this->nextId(), 'div');
        $this->selectedId = $this->tree['root'];
    }

    /**
     * Add a theme, or change the one being edited.
     */
    public function saveTheme(): void
    {
        $group = trim($this->themeGroup);
        $variant = trim($this->themeVariant);

        if (! ThemeRegistry::allowsName($group) || ! ThemeRegistry::allowsName($variant)) {
            $this->themeNotice = 'A group and a variant may only hold letters, numbers, dashes and underscores.';

            return;
        }

        $registry = (new ThemeRegistry($this->themes))->with($group, $variant, $this->themeClasses);

        if ($registry->classes($group, $variant) === '') {
            $this->themeNotice = 'A theme needs at least one class.';

            return;
        }

        $this->themes = $registry->groups();
        $this->themeNotice = null;

        $this->clearThemeInputs();
    }

    /**
     * Change the classes of a theme.
     */
    public function updateThemeClasses(string $group, string $variant, string $classes): void
    {
        if (! $this->themeExists($group, $variant)) {
            return;
        }

        $sanitized = ClassList::sanitize($classes);

        // A variant with no classes left is not a variant any more, and the
        // nodes that use it would be left pointing at nothing, so the old
        // classes are kept and the reason is shown instead.
        if ($sanitized === '') {
            $this->themeNotice = 'A theme needs at least one class.';

            return;
        }

        $this->themeNotice = null;

        $this->themes = (new ThemeRegistry($this->themes))
            ->with($group, $variant, $sanitized)
            ->groups();
    }

    /**
     * Remove a theme.
     *
     * A theme that is still used by a node is kept, because the generated
     * code would point at a variant that is not there any more.
     */
    public function deleteTheme(string $group, string $variant): void
    {
        if ($this->themeUsage($group, $variant) > 0) {
            $this->themeNotice = 'This theme is still used by a node, so it cannot be removed.';

            return;
        }

        $this->themeNotice = null;

        $this->themes = (new ThemeRegistry($this->themes))->without($group, $variant)->groups();
    }

    /**
     * Empty the theme inputs.
     */
    private function clearThemeInputs(): void
    {
        $this->themeGroup = '';
        $this->themeVariant = '';
        $this->themeClasses = '';
    }

    /**
     * Add a theme variant to a node, or take it off again.
     *
     * A group holds a list of variants, so a node can carry several of the
     * same group. Toggling is what the chips in the drawer do.
     */
    public function toggleTheme(string $nodeId, string $group, string $variant): void
    {
        if (! $this->themeExists($group, $variant)) {
            return;
        }

        if ($this->nodeUsesTheme($nodeId, $group, $variant)) {
            $this->tree = TreeMutator::removeTheme($this->tree, $nodeId, $group, $variant);

            return;
        }

        $this->tree = TreeMutator::addTheme($this->tree, $nodeId, $group, $variant);
    }

    /**
     * Take a theme variant off a node.
     */
    public function removeNodeTheme(string $nodeId, string $group, string $variant): void
    {
        $this->tree = TreeMutator::removeTheme($this->tree, $nodeId, $group, $variant);
    }

    /**
     * Whether a node carries a variant of a group.
     */
    public function nodeUsesTheme(string $nodeId, string $group, string $variant): bool
    {
        $node = TreeMutator::find($this->tree, $nodeId);

        if ($node === null) {
            return false;
        }

        return in_array($variant, $node['theme'][$group] ?? [], true);
    }

    /**
     * How many nodes use a theme.
     */
    public function themeUsage(string $group, string $variant): int
    {
        $count = 0;

        foreach ($this->tree['nodes'] as $node) {
            if (in_array($variant, $node['theme'][$group] ?? [], true)) {
                $count++;
            }
        }

        return $count;
    }

    /**
     * Turn the runtime Tailwind on or off.
     *
     * The runtime Tailwind is a script in the head of the page, so the
     * choice has to be made before the page is loaded: the session carries
     * it and the page is loaded again.
     */
    public function toggleTailwind(): void
    {
        session()->put('builder.tailwind_runtime', ! $this->tailwindRuntime());

        $this->redirect(route('builder'));
    }

    /**
     * Whether the canvas is styled by the runtime Tailwind.
     */
    #[Computed]
    public function tailwindRuntime(): bool
    {
        return (bool) session('builder.tailwind_runtime', true);
    }

    /**
     * Handle a node being dropped somewhere else.
     */
    public function handleSort(string $itemId, int $position, ?string $parentId = null): void
    {
        if ($parentId === null) {
            return;
        }

        $this->tree = TreeMutator::move($this->tree, $itemId, $parentId, $position);
        $this->selectedId = $itemId;
    }

    /**
     * Show a node in the inspector.
     */
    public function select(string $nodeId): void
    {
        if (TreeMutator::find($this->tree, $nodeId) !== null) {
            $this->selectedId = $nodeId;
        }
    }

    /**
     * Open the drawer for a node.
     */
    public function openConfig(string $nodeId): void
    {
        if (TreeMutator::find($this->tree, $nodeId) === null) {
            return;
        }

        // The draft belongs to the node it was typed for, so it is only kept
        // when the same node is opened again.
        if ($this->configuringId !== $nodeId) {
            $this->textDraft = '';
        }

        $this->selectedId = $nodeId;
        $this->configuringId = $nodeId;

        $this->cancelAttribute();
    }

    /**
     * Open the drawer for the node that is currently selected.
     *
     * The nodes that cannot hold a chrome have no button of their own, so
     * this is the way in for them.
     */
    public function configureSelected(): void
    {
        if ($this->selectedId !== null) {
            $this->openConfig($this->selectedId);
        }
    }

    /**
     * Close the drawer.
     */
    public function closeConfig(): void
    {
        $this->configuringId = null;

        $this->cancelAttribute();
    }

    /**
     * Add the attribute waiting in the inputs, or update the one being edited.
     */
    public function addAttribute(): void
    {
        $nodeId = $this->configuringId;

        if ($nodeId === null) {
            return;
        }

        $name = trim($this->attributeName);

        if ($name === '') {
            return;
        }

        if (! AttributeGuard::allows($name)) {
            $this->attributeNotice = AttributeGuard::reason($name);

            return;
        }

        $this->tree = TreeMutator::setAttribute($this->tree, $nodeId, $name, $this->attributeValue);

        $this->cancelAttribute();
    }

    /**
     * Load an attribute back into the inputs so it can be changed.
     */
    public function editAttribute(string $name): void
    {
        $node = $this->configuring();

        if ($node === null || ! array_key_exists($name, $node['attrs'])) {
            return;
        }

        $this->attributeName = $name;
        $this->attributeValue = $node['attrs'][$name];
        $this->editingAttribute = $name;
    }

    /**
     * Remove an attribute from the node being configured.
     */
    public function deleteAttribute(string $name): void
    {
        $nodeId = $this->configuringId;

        if ($nodeId === null) {
            return;
        }

        $this->tree = TreeMutator::removeAttribute($this->tree, $nodeId, $name);

        if ($this->editingAttribute === $name) {
            $this->cancelAttribute();
        }
    }

    /**
     * Empty the attribute inputs.
     */
    public function cancelAttribute(): void
    {
        $this->attributeName = '';
        $this->attributeValue = '';
        $this->editingAttribute = null;
        $this->attributeNotice = null;
    }

    /**
     * Add a component to the selected node, or to the given parent.
     */
    public function add(string $type, ?string $parentId = null): void
    {
        $descriptor = ComponentRegistry::get($type);

        if ($descriptor === null) {
            return;
        }

        $parentId ??= $this->targetParent($type);

        $node = BuilderNode::make($this->nextId(), $type);

        $this->tree = TreeMutator::addChild($this->tree, $parentId, $node);

        // The child is only added when the parent takes it, so the parent is
        // the one to ask about it.
        if (! in_array($node['id'], $this->tree['nodes'][$parentId]['children'] ?? [], true)) {
            return;
        }

        $this->selectedId = $node['id'];

        // A text element is almost always one you are about to type into, so
        // the drawer opens with the text field ready. A container is not.
        if (ComponentRegistry::isTextual($type)) {
            $this->openConfig($node['id']);
        }
    }

    /**
     * Remove a node.
     */
    public function remove(string $nodeId): void
    {
        // Everything below the node goes with it, so the drawer and the
        // selection have to be cleared for those too.
        $descendants = TreeMutator::descendantsOf($this->tree, $nodeId);

        $this->tree = TreeMutator::remove($this->tree, $nodeId);

        if ($this->selectedId === $nodeId || in_array($this->selectedId, $descendants, true)) {
            $this->selectedId = $this->tree['root'];
        }

        if ($this->configuringId === $nodeId || in_array($this->configuringId, $descendants, true)) {
            $this->closeConfig();
        }
    }

    /**
     * Append a text segment to a node.
     */
    public function addText(string $parentId, string $text): void
    {
        if (trim($text) === '') {
            return;
        }

        $this->tree = TreeMutator::addText($this->tree, $parentId, $text, $this->nextId());
    }

    /**
     * Remove a text segment.
     */
    public function removeText(string $segmentId): void
    {
        $this->tree = TreeMutator::remove($this->tree, $segmentId);
    }

    /**
     * Append a text segment to the node the drawer is configuring.
     *
     * The canvas's own add menu is only on the nodes that hold children, so
     * the drawer is the way in for a component that only takes text, such as
     * a textarea or an option. A component that takes neither refuses the
     * segment, and the tree is left alone.
     */
    public function addTextToConfiguring(): void
    {
        if ($this->configuringId === null) {
            return;
        }

        $text = trim($this->textDraft);

        if ($text === '') {
            return;
        }

        $id = $this->nextId();

        $this->tree = TreeMutator::addText($this->tree, $this->configuringId, $text, $id);

        // The segment is only added when the node takes text, so the draft is
        // only cleared when it did.
        if (isset($this->tree['nodes'][$id])) {
            $this->textDraft = '';
        }
    }

    /**
     * Copy a node.
     */
    public function duplicate(string $nodeId): void
    {
        $this->tree = TreeMutator::duplicate(
            $this->tree,
            $nodeId,
            fn (string $id): string => $this->nextId(),
        );
    }

    /**
     * Move a node one slot up among its siblings.
     */
    public function moveUp(string $nodeId): void
    {
        $this->tree = TreeMutator::shift($this->tree, $nodeId, -1);
    }

    /**
     * Move a node one slot down among its siblings.
     */
    public function moveDown(string $nodeId): void
    {
        $this->tree = TreeMutator::shift($this->tree, $nodeId, 1);
    }

    /**
     * Change the text of a node.
     */
    public function updateText(string $nodeId, string $text): void
    {
        $this->tree = TreeMutator::setText($this->tree, $nodeId, $text);
    }

    /**
     * Save the composition to disk.
     */
    public function save(): void
    {
        $name = trim($this->compositionName);

        if ($name === '') {
            $name = 'composition';
        }

        $repository = new CompositionRepository;

        $repository->save($name, $this->tree, $this->themes);

        $this->compositionName = $name;

        $this->dispatch('builder-saved');
    }

    /**
     * Load a saved composition into the canvas.
     */
    public function load(string $slug): void
    {
        $found = (new CompositionRepository)->findWithThemes($slug);

        if ($found === null) {
            return;
        }

        $this->tree = $found['tree'];
        $this->themes = $found['themes'];
        $this->selectedId = $this->tree['root'];
        $this->nodeCounter = $this->highestNodeId();

        // The drawer was showing a node of the composition that was on the
        // canvas, and that node is not necessarily in this one.
        $this->closeConfig();
    }

    /**
     * Delete a saved composition.
     */
    public function delete(string $slug): void
    {
        (new CompositionRepository)->delete($slug);
    }

    /**
     * The rendered preview of the tree.
     */
    #[Computed]
    public function preview(): string
    {
        return (new TreeRenderer($this->themes))->render($this->tree, $this->selectedId);
    }

    /**
     * The composition as it will really look, without any of the builder's
     * own additions: no chrome, no reserved row, no minimum sizes.
     */
    #[Computed]
    public function cleanPreview(): string
    {
        return (new TreeRenderer($this->themes))->render($this->tree, null, clean: true);
    }

    /**
     * The PHP that reproduces the tree.
     */
    #[Computed]
    public function code(): string
    {
        return (new PhpCodeGenerator($this->themes))->snippet($this->tree);
    }

    /**
     * Every theme that has been defined, flattened for a picker.
     *
     * @return array<int, array{group: string, variant: string, classes: string}>
     */
    #[Computed]
    public function themeVariants(): array
    {
        return (new ThemeRegistry($this->themes))->variants();
    }

    /**
     * The themes applied to the node the drawer is configuring.
     *
     * @return array<int, array{group: string, variant: string, classes: string, missing: bool}>
     */
    #[Computed]
    public function configuringThemes(): array
    {
        $node = $this->configuring();

        if ($node === null) {
            return [];
        }

        $registry = new ThemeRegistry($this->themes);

        $themes = [];

        foreach ($node['theme'] as $group => $variants) {
            foreach ($variants as $variant) {
                $classes = $registry->classes($group, $variant);

                $themes[] = [
                    'group' => $group,
                    'variant' => $variant,
                    'classes' => $classes,
                    'missing' => $classes === '',
                ];
            }
        }

        return $themes;
    }

    /**
     * The node the inspector is showing.
     *
     * @return Node|null
     */
    #[Computed]
    public function selected(): ?array
    {
        if ($this->selectedId === null) {
            return null;
        }

        return TreeMutator::find($this->tree, $this->selectedId);
    }

    /**
     * The descriptor of the selected node.
     */
    #[Computed]
    public function selectedDescriptor(): ?ComponentDescriptor
    {
        $node = $this->selected();

        if ($node === null) {
            return null;
        }

        return ComponentRegistry::get($node['type']);
    }

    /**
     * The node the drawer is configuring.
     *
     * @return Node|null
     */
    #[Computed]
    public function configuring(): ?array
    {
        if ($this->configuringId === null) {
            return null;
        }

        return TreeMutator::find($this->tree, $this->configuringId);
    }

    /**
     * The descriptor of the node the drawer is configuring.
     */
    #[Computed]
    public function configuringDescriptor(): ?ComponentDescriptor
    {
        $node = $this->configuring();

        if ($node === null) {
            return null;
        }

        return ComponentRegistry::get($node['type']);
    }

    /**
     * The palette, grouped for display.
     *
     * @return array<string, array<string, ComponentDescriptor>>
     */
    #[Computed]
    public function palette(): array
    {
        return ComponentRegistry::groups();
    }

    /**
     * The text segments of the node being configured.
     *
     * @return array<int, array{id: string, text: string}>
     */
    #[Computed]
    public function textSegments(): array
    {
        if ($this->configuringId === null) {
            return [];
        }

        return TreeMutator::textSegments($this->tree, $this->configuringId);
    }

    /**
     * The compositions saved on disk.
     *
     * @return array<int, SavedComposition>
     */
    #[Computed]
    public function saved(): array
    {
        return (new CompositionRepository)->all();
    }

    /**
     * Whether a theme is in the registry.
     */
    private function themeExists(string $group, string $variant): bool
    {
        return (new ThemeRegistry($this->themes))->has($group, $variant);
    }

    /**
     * Hand out a unique id for a new node.
     *
     * An id that the tree is already using is skipped, so a new node can
     * never take the place of one that is there.
     */
    private function nextId(): string
    {
        do {
            $this->nodeCounter++;

            $id = 'n'.$this->nodeCounter;
        } while (isset($this->tree['nodes'][$id]));

        return $id;
    }

    /**
     * The highest number the ids of the tree hold.
     *
     * The ids of a saved composition have gaps in them, because a node that
     * was deleted leaves its id behind, so the number of the nodes is not
     * enough to know where the next id can start.
     */
    private function highestNodeId(): int
    {
        $highest = 0;

        foreach (array_keys($this->tree['nodes']) as $id) {
            if (preg_match('/^n(\d+)$/', $id, $matches) === 1) {
                $highest = max($highest, (int) $matches[1]);
            }
        }

        return $highest;
    }

    /**
     * Where a newly added component should go.
     *
     * The selected node is used when it can hold the component. When it is a
     * leaf, the component goes next to it in its parent instead, so the
     * palette is always usable.
     */
    private function targetParent(string $type): string
    {
        $selected = $this->selected();

        if ($selected !== null) {
            $descriptor = ComponentRegistry::get($selected['type']);

            if ($descriptor !== null && $descriptor->allowsChild($type)) {
                return $selected['id'];
            }

            $parentId = TreeMutator::parentOf($this->tree, $selected['id']);

            if ($parentId !== null) {
                $parent = TreeMutator::find($this->tree, $parentId);
                $parentDescriptor = $parent !== null ? ComponentRegistry::get($parent['type']) : null;

                if ($parentDescriptor !== null && $parentDescriptor->allowsChild($type)) {
                    return $parentId;
                }
            }
        }

        return $this->tree['root'];
    }
}
