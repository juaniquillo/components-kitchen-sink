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
use App\Builder\Support\CompositionHistory;
use App\Builder\Support\CompositionRepository;
use App\Builder\Support\NodeChrome;
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
     * What the drawer is showing, or null when it is closed.
     *
     * A node being configured, the table form for a node, or one of the
     * tools. The node the mode works on lives in `$drawerTarget`.
     */
    public ?DrawerMode $drawer = null;

    /**
     * The id of the node the drawer is working on, when the mode needs one.
     *
     * The node being configured, or the node a table goes under. The tools
     * belong to no node, so they carry no target.
     */
    public ?string $drawerTarget = null;

    /**
     * The message the last refused action left behind, and where it belongs.
     *
     * One notice at a time: a new refusal replaces the one before it, and
     * every surface renders only the notice that is its own. A successful
     * action of the same surface takes it away again.
     *
     * @var array{surface: string, message: string}|null
     */
    public ?array $notice = null;

    /**
     * The number of columns the table form is set to.
     */
    public int $newTableColumns = 3;

    /**
     * The number of body rows the table form is set to.
     */
    public int $newTableRows = 2;

    /**
     * The name given to the composition when it is saved.
     */
    public string $compositionName = '';

    /**
     * Whether the preview dialog holds its copy of the composition.
     *
     * The clean preview is a second full render of the tree on every
     * update of the canvas, so the dialog only carries it when the user
     * is looking at it: opening the dialog asks for it, closing it takes
     * it away again.
     */
    public bool $previewRequested = false;

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
     * How many nodes have been created, used to hand out unique ids.
     */
    public int $nodeCounter = 0;

    /**
     * A fingerprint of the composition as the last save, load or reset left
     * it.
     *
     * The composition is dirty when the tree or the themes are not the ones
     * that were left behind, and the browser is told about it, so it can warn
     * before the page goes away.
     */
    public string $fingerprint = '';

    /**
     * The states the undo can bring back, oldest first.
     *
     * A snapshot is the whole of what a composition is — its tree, its
     * themes, the node being looked at and the name it is saved under —
     * as json, so an undo puts back exactly what the change replaced.
     *
     * @var array<int, string>
     */
    public array $undoStack = [];

    /**
     * The states a redo can bring back, newest first.
     *
     * @var array<int, string>
     */
    public array $redoStack = [];

    public function mount(): void
    {
        $this->tree = BuilderNode::tree($this->nextId(), 'div');
        $this->selectedId = $this->tree['root'];

        $this->fingerprint = $this->currentFingerprint();
    }

    /**
     * Say why an action was refused, in the place that asked for it.
     */
    private function notify(NoticeSurface $surface, string $message): void
    {
        $this->notice = ['surface' => $surface->value, 'message' => $message];
    }

    /**
     * Take a notice away, or every notice when no surface is named.
     */
    private function clearNotice(?NoticeSurface $surface = null): void
    {
        if ($surface === null || ($this->notice['surface'] ?? null) === $surface->value) {
            $this->notice = null;
        }
    }

    /**
     * The message waiting for a surface, if it is the one showing.
     */
    public function noticeOn(NoticeSurface $surface): ?string
    {
        return $this->notice !== null && $this->notice['surface'] === $surface->value
            ? $this->notice['message']
            : null;
    }

    /**
     * Add a theme, or change the one being edited.
     */
    public function saveTheme(): void
    {
        $group = trim($this->themeGroup);
        $variant = trim($this->themeVariant);

        if (! ThemeRegistry::allowsName($group) || ! ThemeRegistry::allowsName($variant)) {
            $this->notify(NoticeSurface::THEME, 'A group and a variant may only hold letters, numbers, dashes and underscores.');

            return;
        }

        $registry = (new ThemeRegistry($this->themes))->with($group, $variant, $this->themeClasses);

        if ($registry->classes($group, $variant) === '') {
            $this->notify(NoticeSurface::THEME, 'A theme needs at least one class.');

            return;
        }

        $this->transact(function () use ($registry): void {
            $this->themes = $registry->groups();
        });

        $this->clearNotice(NoticeSurface::THEME);

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
            $this->notify(NoticeSurface::THEME, 'A theme needs at least one class.');

            return;
        }

        $this->clearNotice(NoticeSurface::THEME);

        $this->transact(function () use ($group, $variant, $sanitized): void {
            $this->themes = (new ThemeRegistry($this->themes))
                ->with($group, $variant, $sanitized)
                ->groups();
        });
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
            $this->notify(NoticeSurface::THEME, 'This theme is still used by a node, so it cannot be removed.');

            return;
        }

        $this->clearNotice(NoticeSurface::THEME);

        $this->transact(function () use ($group, $variant): void {
            $this->themes = (new ThemeRegistry($this->themes))->without($group, $variant)->groups();
        });
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

        $removing = $this->nodeUsesTheme($nodeId, $group, $variant);

        $this->transact(function () use ($nodeId, $group, $variant, $removing): void {
            $this->tree = $removing
                ? TreeMutator::removeTheme($this->tree, $nodeId, $group, $variant)
                : TreeMutator::addTheme($this->tree, $nodeId, $group, $variant);
        });
    }

    /**
     * Take a theme variant off a node.
     */
    public function removeNodeTheme(string $nodeId, string $group, string $variant): void
    {
        $this->transact(function () use ($nodeId, $group, $variant): void {
            $this->tree = TreeMutator::removeTheme($this->tree, $nodeId, $group, $variant);
        });
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
     * Handle a node being dropped somewhere else.
     */
    public function handleSort(string $itemId, int $position, ?string $parentId = null): void
    {
        if ($parentId === null) {
            return;
        }

        $refusal = $this->sortRefusal($itemId, $parentId);

        if ($refusal !== null) {
            // The drop is refused, but the node it was about is still the
            // one the user is looking at.
            $this->selectedId = $itemId;
            $this->notify(NoticeSurface::CANVAS, $refusal);

            return;
        }

        $this->transact(function () use ($itemId, $parentId, $position): void {
            $this->tree = TreeMutator::move($this->tree, $itemId, $parentId, $position);
            $this->selectedId = $itemId;
        });

        $this->clearNotice(NoticeSurface::CANVAS);
    }

    /**
     * Why a dropped node cannot go where it landed, if it cannot.
     */
    private function sortRefusal(string $itemId, string $parentId): ?string
    {
        $item = TreeMutator::find($this->tree, $itemId);
        $parent = TreeMutator::find($this->tree, $parentId);

        if ($item === null || $parent === null) {
            return 'That node is not on the canvas any more.';
        }

        if ($itemId === $parentId || in_array($parentId, TreeMutator::descendantsOf($this->tree, $itemId), true)) {
            return 'A node cannot be moved into itself.';
        }

        $descriptor = ComponentRegistry::get($parent['type']);

        if ($descriptor !== null && $descriptor->allowsChild($item['type'])) {
            return null;
        }

        $itemDescriptor = ComponentRegistry::get($item['type']);

        $itemLabel = $itemDescriptor !== null ? $itemDescriptor->label : $item['type'];
        $parentLabel = $descriptor !== null ? $descriptor->label : $parent['type'];

        return 'A '.$parentLabel.' cannot hold a '.$itemLabel.'.';
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

        $this->clearNotice(NoticeSurface::NODE);

        // The draft belongs to the node it was typed for, so it is only kept
        // when the same node is opened again.
        if ($this->drawer !== DrawerMode::NODE || $this->drawerTarget !== $nodeId) {
            $this->textDraft = '';
        }

        $this->selectedId = $nodeId;

        $this->openDrawer(DrawerMode::NODE, $nodeId);

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
        $this->clearConfiguring();

        // The tools panel goes away with the drawer being closed.
        $this->drawer = null;
        $this->drawerTarget = null;
    }

    /**
     * Drop whatever node the drawer was showing.
     *
     * The drawer itself may stay open: the tools panels do not belong to
     * one node, so they survive a change of the composition.
     */
    private function clearConfiguring(): void
    {
        if ($this->drawer === DrawerMode::NODE || $this->drawer === DrawerMode::TABLE) {
            $this->drawer = null;
            $this->drawerTarget = null;
        }

        $this->clearNotice(NoticeSurface::NODE);

        // The draft belongs to the node it was typed for, and a closed
        // drawer has no node.
        $this->textDraft = '';

        $this->cancelAttribute();
    }

    /**
     * Put one thing in the drawer.
     *
     * The drawer shows one thing at a time, so the target belongs to the
     * modes that work on a node and is dropped by the ones that do not.
     */
    private function openDrawer(DrawerMode $mode, ?string $target = null): void
    {
        $this->drawer = $mode;
        $this->drawerTarget = $mode === DrawerMode::NODE || $mode === DrawerMode::TABLE ? $target : null;
    }

    /**
     * The node the drawer is configuring, if it is configuring one.
     */
    private function configuringId(): ?string
    {
        return $this->drawer === DrawerMode::NODE ? $this->drawerTarget : null;
    }

    /**
     * The node the table form is putting a table under, if it is showing.
     */
    private function tableParentId(): ?string
    {
        return $this->drawer === DrawerMode::TABLE ? $this->drawerTarget : null;
    }

    /**
     * Open the drawer on the themes panel.
     */
    public function openThemes(): void
    {
        $this->openTool(DrawerMode::THEMES);

        $this->clearNotice(NoticeSurface::THEME);
    }

    /**
     * Open the drawer on the saved compositions panel.
     */
    public function openSaved(): void
    {
        $this->openTool(DrawerMode::SAVED);

        // The name input is the way in of the panel, and a request from
        // the header deserves to land ready to type.
        $this->dispatch('builder-focus-name');
    }

    /**
     * Open the drawer on a tools panel.
     *
     * The drawer shows one thing at a time, and the node being configured
     * gives way to the tool that was asked for.
     */
    private function openTool(DrawerMode $mode): void
    {
        $this->clearConfiguring();

        $this->openDrawer($mode);
    }

    /**
     * Add the attribute waiting in the inputs, or update the one being edited.
     */
    public function addAttribute(): void
    {
        $nodeId = $this->configuringId();

        if ($nodeId === null) {
            return;
        }

        $name = trim($this->attributeName);

        if ($name === '') {
            return;
        }

        if (! AttributeGuard::allows($name)) {
            $this->notify(NoticeSurface::ATTRIBUTE, AttributeGuard::reason($name));

            return;
        }

        $this->transact(function () use ($nodeId, $name): void {
            $this->tree = TreeMutator::setAttribute($this->tree, $nodeId, $name, $this->attributeValue);
        });

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
        $nodeId = $this->configuringId();

        if ($nodeId === null) {
            return;
        }

        $this->transact(function () use ($nodeId, $name): void {
            $this->tree = TreeMutator::removeAttribute($this->tree, $nodeId, $name);
        });

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
        $this->clearNotice(NoticeSurface::ATTRIBUTE);
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

        // A table holds one head, one body, one foot and one caption, so a
        // section the table already holds is refused with a word instead of
        // an empty duplicate.
        if ($this->tableHolds($parentId, $type)) {
            $section = match ($type) {
                'table.thead' => 'head',
                'table.tbody' => 'body',
                'table.tfoot' => 'foot',
                default => 'caption',
            };

            $this->notify(NoticeSurface::TABLE, 'This table already has a '.$section.'.');

            return;
        }

        $this->transact(function () use ($type, $parentId): void {
            $node = BuilderNode::make($this->nextId(), $type);

            $this->tree = TreeMutator::addChild($this->tree, $parentId, $node);

            // The child is only added when the parent takes it, so the parent is
            // the one to ask about it.
            if (! in_array($node['id'], $this->tree['nodes'][$parentId]['children'] ?? [], true)) {
                return;
            }

            $this->selectedId = $node['id'];
            $this->clearNotice(NoticeSurface::TABLE);
            $this->clearNotice(NoticeSurface::CANVAS);

            // A text element is almost always one you are about to type into, so
            // the drawer opens with the text field ready. A container is not.
            if (ComponentRegistry::isTextual($type)) {
                $this->openConfig($node['id']);
            }
        });
    }

    /**
     * Whether a node already holds the kind of table section the type is.
     */
    private function tableHolds(string $parentId, string $type): bool
    {
        $parent = $this->tree['nodes'][$parentId] ?? null;

        if ($parent === null || ! in_array($type, NodeChrome::TABLE_SECTIONS, true)) {
            return false;
        }

        $types = array_map(
            fn (string $childId): string => $this->tree['nodes'][$childId]['type'] ?? '',
            $parent['children'],
        );

        return in_array($type, $types, true);
    }

    /**
     * Remove a node.
     */
    public function remove(string $nodeId): void
    {
        $this->transact(function () use ($nodeId): void {
            // Everything below the node goes with it.
            $removed = [$nodeId, ...TreeMutator::descendantsOf($this->tree, $nodeId)];

            $this->tree = TreeMutator::remove($this->tree, $nodeId);

            $this->forgetNodePointers($removed);
        });
    }

    /**
     * Append a text segment to a node.
     */
    public function addText(string $parentId, string $text): void
    {
        if (trim($text) === '') {
            return;
        }

        $this->transact(function () use ($parentId, $text): void {
            $this->tree = TreeMutator::addText($this->tree, $parentId, $text, $this->nextId());
        });
    }

    /**
     * Remove a text segment.
     */
    public function removeText(string $segmentId): void
    {
        $this->transact(function () use ($segmentId): void {
            $this->tree = TreeMutator::remove($this->tree, $segmentId);
        });
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
        $nodeId = $this->configuringId();

        if ($nodeId === null) {
            return;
        }

        $text = trim($this->textDraft);

        if ($text === '') {
            return;
        }

        $this->transact(function () use ($nodeId, $text): void {
            $id = $this->nextId();

            $this->tree = TreeMutator::addText($this->tree, $nodeId, $text, $id);

            // The segment is only added when the node takes text, so the draft is
            // only cleared when it did.
            if (isset($this->tree['nodes'][$id])) {
                $this->textDraft = '';
            }
        });
    }

    /**
     * Copy a node.
     */
    public function duplicate(string $nodeId): void
    {
        if (TreeMutator::find($this->tree, $nodeId) === null) {
            $this->notify(NoticeSurface::NODE, 'That node is not on the canvas any more.');

            return;
        }

        $this->transact(function () use ($nodeId): void {
            ['tree' => $tree, 'id' => $copyId] = TreeMutator::duplicate(
                $this->tree,
                $nodeId,
                fn (string $id): string => $this->nextId(),
            );

            $this->tree = $tree;

            // The root has nowhere to go, so it has no copy to select or
            // configure either.
            if ($copyId === null) {
                $this->notify(NoticeSurface::NODE, 'The root of the composition has nowhere to go.');

                return;
            }

            $this->clearNotice(NoticeSurface::NODE);
            $this->selectedId = $copyId;

            // The drawer was showing the original: the copy is what the user is
            // about to work on next.
            if ($this->configuringId() === $nodeId) {
                $this->openConfig($copyId);
            }
        });
    }

    /**
     * Open the drawer with the table form, for the node a table goes under.
     */
    public function tableMode(string $nodeId): void
    {
        $node = TreeMutator::find($this->tree, $nodeId);

        if ($node === null) {
            return;
        }

        $descriptor = ComponentRegistry::get($node['type']);

        if ($descriptor === null || ! $descriptor->allowsChild('table.table')) {
            return;
        }

        $this->closeConfig();

        $this->selectedId = $nodeId;
        $this->clearNotice(NoticeSurface::TABLE);
        $this->newTableColumns = 3;
        $this->newTableRows = 2;
        $this->openDrawer(DrawerMode::TABLE, $nodeId);
    }

    /**
     * Add the table skeleton the form is asking for.
     */
    public function scaffoldTable(): void
    {
        $parentId = $this->tableParentId();

        if ($parentId === null) {
            return;
        }

        if (TreeMutator::find($this->tree, $parentId) === null) {
            $this->closeConfig();

            return;
        }

        $columns = max(1, min(99, $this->newTableColumns));
        $rows = max(1, min(99, $this->newTableRows));

        $count = count($this->tree['nodes']);

        $this->transact(function () use ($parentId, $columns, $rows): void {
            $this->tree = TreeMutator::scaffoldTable(
                $this->tree,
                $parentId,
                $columns,
                $rows,
                fn (string $id): string => $this->nextId(),
            );
        });

        // The skeleton is only added when the node takes a table, and a
        // quiet refusal has the reason the form stayed open for.
        if (count($this->tree['nodes']) === $count) {
            $this->notify(NoticeSurface::TABLE, 'This node does not take a table.');

            return;
        }

        $this->clearNotice(NoticeSurface::TABLE);

        $this->closeConfig();
    }

    /**
     * Copy a table row below itself, without the texts of its cells.
     */
    public function addRowBelow(string $rowId): void
    {
        $this->transact(function () use ($rowId): void {
            $this->tree = TreeMutator::addRowBelow(
                $this->tree,
                $rowId,
                fn (string $id): string => $this->nextId(),
            );
        });

        $this->clearNotice(NoticeSurface::TABLE);
    }

    /**
     * Add a column at the end of every row of the table the row belongs to.
     */
    public function addColumn(string $rowId): void
    {
        $this->transact(function () use ($rowId): void {
            $this->tree = TreeMutator::addColumn(
                $this->tree,
                $rowId,
                fn (string $id): string => $this->nextId(),
            );
        });

        $this->clearNotice(NoticeSurface::TABLE);
    }

    /**
     * Remove the column a cell belongs to.
     */
    public function deleteColumn(string $cellId): void
    {
        $nodeIds = array_keys($this->tree['nodes']);

        $this->transact(function () use ($nodeIds, $cellId): void {
            $this->tree = TreeMutator::deleteColumn($this->tree, $cellId);

            // The cells of the column go out in one operation, so the pointers
            // that pointed at any of them go out with it.
            $this->forgetNodePointers(array_values(array_diff(
                $nodeIds,
                array_keys($this->tree['nodes']),
            )));
        });

        // The operation leaves the tree alone when the column is not well
        // defined, and the reason is shown instead of the cell going quiet.
        if (isset($this->tree['nodes'][$cellId])) {
            $this->notify(NoticeSurface::TABLE, 'This table cannot lose a column while one of its cells spans rows or columns.');
        } else {
            $this->clearNotice(NoticeSurface::TABLE);
        }
    }

    /**
     * Drop the pointers that pointed at nodes that are gone.
     *
     * The selection and the drawer cannot stay on a node that is not in
     * the tree any more: the drawer would show nothing, and the button
     * that configures the selection would do the same.
     *
     * @param  array<int, string>  $removedIds
     */
    private function forgetNodePointers(array $removedIds): void
    {
        if ($this->selectedId !== null && in_array($this->selectedId, $removedIds, true)) {
            $this->selectedId = $this->tree['root'];
        }

        $nodeId = $this->configuringId();

        if ($nodeId !== null && in_array($nodeId, $removedIds, true)) {
            $this->closeConfig();
        }
    }

    /**
     * Move a node one slot up among its siblings.
     */
    public function moveUp(string $nodeId): void
    {
        $this->transact(function () use ($nodeId): void {
            $this->tree = TreeMutator::shift($this->tree, $nodeId, -1);
        });
    }

    /**
     * Move a node one slot down among its siblings.
     */
    public function moveDown(string $nodeId): void
    {
        $this->transact(function () use ($nodeId): void {
            $this->tree = TreeMutator::shift($this->tree, $nodeId, 1);
        });
    }

    /**
     * Change the text of a node.
     */
    public function updateText(string $nodeId, string $text): void
    {
        $this->transact(function () use ($nodeId, $text): void {
            $this->tree = TreeMutator::setText($this->tree, $nodeId, $text);
        });
    }

    /**
     * Save the composition to disk.
     */
    public function save(): void
    {
        $name = trim($this->compositionName);

        if ($name === '') {
            // A save without a name has nothing to save under yet, so the
            // saved compositions panel is opened with the input waiting
            // for one.
            $this->openSaved();

            return;
        }

        $repository = new CompositionRepository;

        $repository->save($name, $this->tree, $this->themes);

        $this->compositionName = $name;

        // What is on the disk is what the canvas holds, so there is nothing
        // to warn about any more.
        $this->fingerprint = $this->currentFingerprint();

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

        $this->transact(function () use ($found): void {
            $this->tree = $found['tree'];
            $this->themes = $found['themes'];
            $this->selectedId = $this->tree['root'];
            $this->nodeCounter = $this->highestNodeId();

            // The composition that was loaded now owns the canvas: its name is
            // what saving updates, so a save right after a load does not fall
            // back on the name the input was still carrying.
            $this->compositionName = $found['name'];

            // The drawer was showing a node of the composition that was on the
            // canvas, and that node is not necessarily in this one. The drawer
            // itself stays open, so the list the composition was picked from
            // keeps showing.
            $this->clearConfiguring();

            $this->fingerprint = $this->currentFingerprint();
        });

        $this->clearNotice();
    }

    /**
     * Clear the canvas and start a new composition.
     *
     * The themes stay: they are a palette that is reused by every
     * composition, and not part of one.
     */
    public function resetCanvas(): void
    {
        $this->transact(function (): void {
            $this->tree = BuilderNode::tree($this->nextId(), 'div');
            $this->selectedId = $this->tree['root'];
            $this->compositionName = '';

            $this->closeConfig();

            $this->fingerprint = $this->currentFingerprint();
        });

        $this->clearNotice();
    }

    /**
     * Take the composition back to the state before the last change.
     */
    public function undo(): void
    {
        $history = new CompositionHistory($this->undoStack, $this->redoStack);
        $snapshot = $history->undo($this->snapshot());

        if ($snapshot === null) {
            return;
        }

        [$this->undoStack, $this->redoStack] = $history->stacks();

        $this->restore($snapshot);
    }

    /**
     * Bring back the change the last undo took away.
     */
    public function redo(): void
    {
        $history = new CompositionHistory($this->undoStack, $this->redoStack);
        $snapshot = $history->redo($this->snapshot());

        if ($snapshot === null) {
            return;
        }

        [$this->undoStack, $this->redoStack] = $history->stacks();

        $this->restore($snapshot);
    }

    /**
     * The composition as it is right now, for the undo to bring back.
     */
    private function snapshot(): string
    {
        return CompositionHistory::encode([
            'tree' => $this->tree,
            'themes' => $this->themes,
            'selectedId' => $this->selectedId,
            'name' => $this->compositionName,
        ]);
    }

    /**
     * Run a change and remember the state it replaced.
     *
     * Every changing action goes through here, so none of them can forget
     * its undo entry: the state is taken before the change runs, and it is
     * only remembered when the change left something different behind. A new
     * change also takes the redo with it: there is nothing to bring back
     * over it.
     */
    private function transact(callable $change): mixed
    {
        $before = $this->snapshot();

        $result = $change();

        $history = new CompositionHistory($this->undoStack, $this->redoStack);
        $history->record($before, $this->snapshot());

        [$this->undoStack, $this->redoStack] = $history->stacks();

        return $result;
    }

    /**
     * Put a snapshot back on the canvas.
     */
    private function restore(string $snapshot): void
    {
        $state = CompositionHistory::decode($snapshot);

        if ($state === null) {
            return;
        }

        $this->tree = $state['tree'];
        $this->themes = $state['themes'];
        $this->selectedId = $state['selectedId'];
        $this->compositionName = $state['name'];

        // The drawer was showing a node that may not be in the composition
        // the undo brought back, so it is only kept when the node survived.
        $nodeId = $this->configuringId();

        if ($nodeId !== null && TreeMutator::find($this->tree, $nodeId) === null) {
            $this->clearConfiguring();
        }
    }

    /**
     * Delete a saved composition.
     */
    public function delete(string $slug): void
    {
        (new CompositionRepository)->delete($slug);
    }

    /**
     * The preview dialog asked for its copy of the composition.
     */
    public function openPreview(): void
    {
        $this->previewRequested = true;
    }

    /**
     * The preview dialog let go of its copy of the composition.
     */
    public function closePreview(): void
    {
        $this->previewRequested = false;
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
     * Whether the composition holds changes that are not saved.
     *
     * The page is told about it, so the browser can warn before it goes
     * away with work that would be lost.
     */
    #[Computed]
    public function dirty(): bool
    {
        return $this->fingerprint !== $this->currentFingerprint();
    }

    /**
     * Whether the canvas holds nothing but its root.
     *
     * The empty canvas shows a hint, so a fresh page says what to do
     * instead of leaving a blank frame.
     */
    #[Computed]
    public function canvasEmpty(): bool
    {
        return ($this->tree['nodes'][$this->tree['root']]['children'] ?? []) === [];
    }

    /**
     * Whether saving now would replace an existing composition.
     *
     * A save is found by its name, so a name the directory already holds
     * makes the save an overwrite. The saved panel asks this, so the
     * overwrite is announced before it happens instead of a save quietly
     * taking the place of a composition the user may have wanted kept.
     */
    #[Computed]
    public function saveOverwrites(): bool
    {
        return (new CompositionRepository)->has($this->compositionName);
    }

    /**
     * The composition as it will really look, without any of the builder's
     * own additions: no chrome, no reserved row, no minimum sizes.
     *
     * The ids it holds are scoped, so the copy on the canvas does not take
     * their way: both are on the page at once, and a label points at the
     * first element that carries its `for`.
     */
    #[Computed]
    public function cleanPreview(): string
    {
        return (new TreeRenderer($this->themes, 'bw-preview-'))->render($this->tree, null, clean: true);
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
        $nodeId = $this->configuringId();

        if ($nodeId === null) {
            return null;
        }

        return TreeMutator::find($this->tree, $nodeId);
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
        $nodeId = $this->configuringId();

        if ($nodeId === null) {
            return [];
        }

        return TreeMutator::textSegments($this->tree, $nodeId);
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
     * A fingerprint of the composition as it is right now.
     *
     * The tree and the themes are what a composition is made of, so they are
     * what says whether there is work that is not saved.
     */
    private function currentFingerprint(): string
    {
        return hash('xxh128', json_encode([$this->tree, $this->themes]));
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
