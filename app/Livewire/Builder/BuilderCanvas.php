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
     * The id of the node the drawer is configuring.
     */
    public ?string $configuringId = null;

    /**
     * The id of the node the drawer is putting a table under.
     *
     * A table is the one component that is not assembled part by part: the
     * drawer asks for a size and the canvas gets the whole skeleton at once.
     */
    public ?string $tableModeId = null;

    /**
     * The tool the drawer is showing when no node is being configured.
     *
     * Saved compositions and themes live in the drawer too, so a value
     * here opens the drawer on one of them instead of a node.
     *
     * @var 'themes'|'saved'|null
     */
    public ?string $drawerMode = null;

    /**
     * The number of columns the table form is set to.
     */
    public int $newTableColumns = 3;

    /**
     * The number of body rows the table form is set to.
     */
    public int $newTableRows = 2;

    /**
     * Why the last table operation was refused, if it was.
     *
     * The row and column operations reach across a table, and a table that
     * is not lined up has to say so instead of staying quiet.
     */
    public ?string $tableNotice = null;

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

    /**
     * How many changes the undo remembers.
     *
     * A session is a long run of small changes, and a stack that grows
     * without end would drag every request behind it.
     */
    private const HISTORY_LIMIT = 25;

    public function mount(): void
    {
        $this->tree = BuilderNode::tree($this->nextId(), 'div');
        $this->selectedId = $this->tree['root'];

        $this->fingerprint = $this->currentFingerprint();
    }

    /**
     * Add a theme, or change the one being edited.
     */
    public function saveTheme(): void
    {
        $before = $this->snapshot();

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

        $this->record($before);
    }

    /**
     * Change the classes of a theme.
     */
    public function updateThemeClasses(string $group, string $variant, string $classes): void
    {
        $before = $this->snapshot();

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

        $this->record($before);
    }

    /**
     * Remove a theme.
     *
     * A theme that is still used by a node is kept, because the generated
     * code would point at a variant that is not there any more.
     */
    public function deleteTheme(string $group, string $variant): void
    {
        $before = $this->snapshot();

        if ($this->themeUsage($group, $variant) > 0) {
            $this->themeNotice = 'This theme is still used by a node, so it cannot be removed.';

            return;
        }

        $this->themeNotice = null;

        $this->themes = (new ThemeRegistry($this->themes))->without($group, $variant)->groups();

        $this->record($before);
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
        $before = $this->snapshot();

        if (! $this->themeExists($group, $variant)) {
            return;
        }

        if ($this->nodeUsesTheme($nodeId, $group, $variant)) {
            $this->tree = TreeMutator::removeTheme($this->tree, $nodeId, $group, $variant);

            $this->record($before);

            return;
        }

        $this->tree = TreeMutator::addTheme($this->tree, $nodeId, $group, $variant);

        $this->record($before);
    }

    /**
     * Take a theme variant off a node.
     */
    public function removeNodeTheme(string $nodeId, string $group, string $variant): void
    {
        $before = $this->snapshot();

        $this->tree = TreeMutator::removeTheme($this->tree, $nodeId, $group, $variant);

        $this->record($before);
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

        $before = $this->snapshot();

        $this->tree = TreeMutator::move($this->tree, $itemId, $parentId, $position);
        $this->selectedId = $itemId;

        $this->record($before);
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

        // The drawer shows one thing at a time, and the table form and the
        // tools give way to the node that was asked about.
        $this->tableModeId = null;
        $this->drawerMode = null;

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
        $this->clearConfiguring();

        // The tools panel goes away with the drawer being closed.
        $this->drawerMode = null;
    }

    /**
     * Drop whatever node the drawer was showing.
     *
     * The drawer itself may stay open: the tools panels do not belong to
     * one node, so they survive a change of the composition.
     */
    private function clearConfiguring(): void
    {
        $this->configuringId = null;
        $this->tableModeId = null;

        // The draft belongs to the node it was typed for, and a closed
        // drawer has no node.
        $this->textDraft = '';

        $this->cancelAttribute();
    }

    /**
     * Open the drawer on the themes panel.
     */
    public function openThemes(): void
    {
        $this->openTool('themes');
    }

    /**
     * Open the drawer on the saved compositions panel.
     */
    public function openSaved(): void
    {
        $this->openTool('saved');

        // The name input is the way in of the panel, and a request from
        // the header deserves to land ready to type.
        $this->dispatch('builder-focus-name');
    }

    /**
     * Open the drawer on a tools panel.
     *
     * The drawer shows one thing at a time, and the node being configured
     * gives way to the tool that was asked for.
     *
     * @param  'themes'|'saved'  $mode
     */
    private function openTool(string $mode): void
    {
        $this->clearConfiguring();

        $this->drawerMode = $mode;
    }

    /**
     * Add the attribute waiting in the inputs, or update the one being edited.
     */
    public function addAttribute(): void
    {
        if ($this->configuringId === null) {
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

        $before = $this->snapshot();

        $this->tree = TreeMutator::setAttribute($this->tree, $this->configuringId, $name, $this->attributeValue);

        $this->cancelAttribute();

        $this->record($before);
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
        if ($this->configuringId === null) {
            return;
        }

        $before = $this->snapshot();

        $this->tree = TreeMutator::removeAttribute($this->tree, $this->configuringId, $name);

        if ($this->editingAttribute === $name) {
            $this->cancelAttribute();
        }

        $this->record($before);
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
        $before = $this->snapshot();

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

            $this->tableNotice = 'This table already has a '.$section.'.';

            return;
        }
        $node = BuilderNode::make($this->nextId(), $type);

        $this->tree = TreeMutator::addChild($this->tree, $parentId, $node);

        // The child is only added when the parent takes it, so the parent is
        // the one to ask about it.
        if (! in_array($node['id'], $this->tree['nodes'][$parentId]['children'] ?? [], true)) {
            return;
        }

        $this->selectedId = $node['id'];
        $this->tableNotice = null;

        // A text element is almost always one you are about to type into, so
        // the drawer opens with the text field ready. A container is not.
        if (ComponentRegistry::isTextual($type)) {
            $this->openConfig($node['id']);
        }

        $this->record($before);
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
        $before = $this->snapshot();

        // Everything below the node goes with it.
        $removed = [$nodeId, ...TreeMutator::descendantsOf($this->tree, $nodeId)];

        $this->tree = TreeMutator::remove($this->tree, $nodeId);

        $this->forgetNodePointers($removed);

        $this->record($before);
    }

    /**
     * Append a text segment to a node.
     */
    public function addText(string $parentId, string $text): void
    {
        if (trim($text) === '') {
            return;
        }

        $before = $this->snapshot();

        $this->tree = TreeMutator::addText($this->tree, $parentId, $text, $this->nextId());

        $this->record($before);
    }

    /**
     * Remove a text segment.
     */
    public function removeText(string $segmentId): void
    {
        $before = $this->snapshot();

        $this->tree = TreeMutator::remove($this->tree, $segmentId);

        $this->record($before);
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

        $before = $this->snapshot();

        $id = $this->nextId();

        $this->tree = TreeMutator::addText($this->tree, $this->configuringId, $text, $id);

        // The segment is only added when the node takes text, so the draft is
        // only cleared when it did.
        if (isset($this->tree['nodes'][$id])) {
            $this->textDraft = '';

            $this->record($before);
        }
    }

    /**
     * Copy a node.
     */
    public function duplicate(string $nodeId): void
    {
        $before = $this->snapshot();

        $parentId = TreeMutator::parentOf($this->tree, $nodeId);
        $siblings = $parentId !== null ? $this->tree['nodes'][$parentId]['children'] : [];

        $this->tree = TreeMutator::duplicate(
            $this->tree,
            $nodeId,
            fn (string $id): string => $this->nextId(),
        );

        // The copy's id is the one its parent did not hold before, and when
        // the duplication refused there is no such id.
        $copyId = $parentId !== null
            ? (array_values(array_diff($this->tree['nodes'][$parentId]['children'] ?? [], $siblings))[0] ?? null)
            : null;

        if ($copyId === null) {
            return;
        }

        $this->selectedId = $copyId;

        // The drawer was showing the original: the copy is what the user is
        // about to work on next.
        if ($this->configuringId === $nodeId) {
            $this->openConfig($copyId);
        }

        $this->record($before);
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
        $this->tableNotice = null;
        $this->newTableColumns = 3;
        $this->newTableRows = 2;
        $this->tableModeId = $nodeId;
    }

    /**
     * Add the table skeleton the form is asking for.
     */
    public function scaffoldTable(): void
    {
        if ($this->tableModeId === null) {
            return;
        }

        if (TreeMutator::find($this->tree, $this->tableModeId) === null) {
            $this->closeConfig();

            return;
        }

        $before = $this->snapshot();

        $columns = max(1, min(99, $this->newTableColumns));
        $rows = max(1, min(99, $this->newTableRows));

        $count = count($this->tree['nodes']);

        $this->tree = TreeMutator::scaffoldTable(
            $this->tree,
            $this->tableModeId,
            $columns,
            $rows,
            fn (string $id): string => $this->nextId(),
        );

        // The skeleton is only added when the node takes a table, and a
        // quiet refusal has the reason the form stayed open for.
        if (count($this->tree['nodes']) === $count) {
            $this->tableNotice = 'This node does not take a table.';

            return;
        }

        $this->tableNotice = null;

        $this->closeConfig();

        $this->record($before);
    }

    /**
     * Copy a table row below itself, without the texts of its cells.
     */
    public function addRowBelow(string $rowId): void
    {
        $before = $this->snapshot();

        $this->tree = TreeMutator::addRowBelow(
            $this->tree,
            $rowId,
            fn (string $id): string => $this->nextId(),
        );

        $this->tableNotice = null;

        $this->record($before);
    }

    /**
     * Add a column at the end of every row of the table the row belongs to.
     */
    public function addColumn(string $rowId): void
    {
        $before = $this->snapshot();

        $this->tree = TreeMutator::addColumn(
            $this->tree,
            $rowId,
            fn (string $id): string => $this->nextId(),
        );

        $this->tableNotice = null;

        $this->record($before);
    }

    /**
     * Remove the column a cell belongs to.
     */
    public function deleteColumn(string $cellId): void
    {
        $before = $this->snapshot();

        $nodeIds = array_keys($this->tree['nodes']);

        $this->tree = TreeMutator::deleteColumn($this->tree, $cellId);

        // The operation leaves the tree alone when the column is not well
        // defined, and the reason is shown instead of the cell going quiet.
        $this->tableNotice = isset($this->tree['nodes'][$cellId])
            ? 'This table cannot lose a column while one of its cells spans rows or columns.'
            : null;

        // The cells of the column go out in one operation, so the pointers
        // that pointed at any of them go out with it.
        $this->forgetNodePointers(array_values(array_diff(
            $nodeIds,
            array_keys($this->tree['nodes']),
        )));

        $this->record($before);
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

        if ($this->configuringId !== null && in_array($this->configuringId, $removedIds, true)) {
            $this->closeConfig();
        }
    }

    /**
     * Move a node one slot up among its siblings.
     */
    public function moveUp(string $nodeId): void
    {
        $before = $this->snapshot();

        $this->tree = TreeMutator::shift($this->tree, $nodeId, -1);

        $this->record($before);
    }

    /**
     * Move a node one slot down among its siblings.
     */
    public function moveDown(string $nodeId): void
    {
        $before = $this->snapshot();

        $this->tree = TreeMutator::shift($this->tree, $nodeId, 1);

        $this->record($before);
    }

    /**
     * Change the text of a node.
     */
    public function updateText(string $nodeId, string $text): void
    {
        $before = $this->snapshot();

        $this->tree = TreeMutator::setText($this->tree, $nodeId, $text);

        $this->record($before);
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
        $before = $this->snapshot();

        $found = (new CompositionRepository)->findWithThemes($slug);

        if ($found === null) {
            return;
        }

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

        $this->record($before);
    }

    /**
     * Clear the canvas and start a new composition.
     *
     * The themes stay: they are a palette that is reused by every
     * composition, and not part of one.
     */
    public function resetCanvas(): void
    {
        $before = $this->snapshot();

        $this->tree = BuilderNode::tree($this->nextId(), 'div');
        $this->selectedId = $this->tree['root'];
        $this->compositionName = '';

        $this->closeConfig();

        $this->fingerprint = $this->currentFingerprint();

        $this->record($before);
    }

    /**
     * Take the composition back to the state before the last change.
     */
    public function undo(): void
    {
        if ($this->undoStack === []) {
            return;
        }

        $this->redoStack[] = $this->snapshot();

        $this->restore((string) array_pop($this->undoStack));
    }

    /**
     * Bring back the change the last undo took away.
     */
    public function redo(): void
    {
        if ($this->redoStack === []) {
            return;
        }

        $this->undoStack[] = $this->snapshot();

        $this->restore((string) array_pop($this->redoStack));
    }

    /**
     * The composition as it is right now, for the undo to bring back.
     */
    private function snapshot(): string
    {
        $snapshot = json_encode([
            $this->tree,
            $this->themes,
            $this->selectedId,
            $this->compositionName,
        ]);

        return $snapshot === false ? '' : $snapshot;
    }

    /**
     * Remember the state a change has replaced, once its work is done.
     *
     * Every changing action hands the state it started from here at its
     * end. A change that turned out to change nothing — a refusal, a move
     * that landed where it came from — is not remembered, because an undo
     * that does nothing reads like the undo is broken. A new change also
     * takes the redo with it: there is nothing to bring back over it.
     */
    private function record(string $before): void
    {
        if ($this->snapshot() === $before) {
            return;
        }

        $this->undoStack[] = $before;
        $this->redoStack = [];

        if (count($this->undoStack) > self::HISTORY_LIMIT) {
            array_shift($this->undoStack);
        }
    }

    /**
     * Put a snapshot back on the canvas.
     */
    private function restore(string $snapshot): void
    {
        /** @var array{0: Tree, 1: array<string, array<string, string>>, 2: string|null, 3: string} $state */
        $state = json_decode($snapshot, true);

        [$this->tree, $this->themes, $this->selectedId, $this->compositionName] = $state;

        // The drawer was showing a node that may not be in the composition
        // the undo brought back, so it is only kept when the node survived.
        if ($this->configuringId !== null && TreeMutator::find($this->tree, $this->configuringId) === null) {
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
