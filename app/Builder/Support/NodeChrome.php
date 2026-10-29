<?php

declare(strict_types=1);

namespace App\Builder\Support;

use App\Builder\Contracts\BuilderNode;
use App\Builder\Registries\ComponentRegistry;
use Juaniquillo\BackendComponents\Enums\ComponentEnum;
use Juaniquillo\BackendComponents\MainBackendComponent;

/**
 * The per node controls the builder puts on the canvas.
 *
 * The chrome has to live *inside* the node it belongs to: the drag handle
 * must be a descendant of the sortable item, and the buttons have to travel
 * with the node when it is dragged. That means the chrome's tag depends on
 * the node's content model, so the markup stays valid wherever it lands.
 *
 * A <div> inside a <p> or a <ul> is hoisted out by the browser's parser,
 * which would quietly restructure the tree. A <span> is the one element that
 * fits inside every phrasing context, and a <li>, <td>, <tr> or <caption>
 * cover the elements that only accept one kind of child. Even so, a row
 * holds nothing but cells, so a chrome on a table section rides in a cell
 * of its own inside the out-of-flow chrome row.
 *
 * The chrome is never part of the tree, so the code generator never sees it.
 *
 * @phpstan-import-type Node from BuilderNode
 */
final class NodeChrome
{
    /**
     * The tag the chrome uses inside a given component.
     *
     * @var array<string, string>
     */
    private const TAGS = [
        // Flow containers: anything goes.
        'div' => 'div',
        'collection' => 'div',
        'template' => 'div',
        'details.details' => 'div',
        'layers.dialog' => 'div',
        'form.form' => 'div',
        'form.fieldset' => 'div',
        'lists.li' => 'div',

        // Phrasing only: a span is the one element that always fits.
        'paragraph' => 'span',
        'headers.h1' => 'span',
        'headers.h2' => 'span',
        'headers.h3' => 'span',
        'headers.h4' => 'span',
        'headers.h5' => 'span',
        'headers.h6' => 'span',
        'inline.span' => 'span',
        'inline.bold' => 'span',
        'inline.strong' => 'span',
        'inline.em' => 'span',
        'inline.italic' => 'span',
        'inline.small' => 'span',
        'inline.link' => 'span',
        'inline.button' => 'span',
        'form.label' => 'span',
        'form.legend' => 'span',
        'details.summary' => 'span',
        'table.th' => 'span',
        'table.td' => 'span',
        'table.caption' => 'span',

        // Lists only hold list items.
        'lists.ul' => 'li',
        'lists.ol' => 'li',

        // Rows only hold cells, so a row's chrome is a row of its own: the
        // renderer places it after the row it belongs to.
        'table.tr' => 'tr',

        // Table sections only hold rows.
        'table.thead' => 'tr',
        'table.tbody' => 'tr',
        'table.tfoot' => 'tr',

        // A table only holds a caption, and it has to come first.
        'table.table' => 'caption',
    ];

    /**
     * The component each chrome tag is rendered with.
     *
     * @var array<string, ComponentEnum>
     */
    private const COMPONENTS = [
        'div' => ComponentEnum::DIV,
        'span' => ComponentEnum::SPAN,
        'li' => ComponentEnum::LI,
        'td' => ComponentEnum::TD,
        'tr' => ComponentEnum::TR,
        'caption' => ComponentEnum::CAPTION,
    ];

    /**
     * The types that get no chrome of their own.
     *
     * Every type the palette offers has to be decided on: either it has a tag
     * in TAGS, or it is listed here. Nothing may fall between the two, which
     * is what NO_CHROME exists to make checkable.
     *
     * A void element holds nothing at all, so there is nowhere to put a
     * chrome. A textarea is RCDATA: its contents are text, not markup, so a
     * chrome inside one would be read out as text and the controls would not
     * work. A select, an optgroup, a datalist and an option only take options,
     * and a colgroup and a col only take columns. The renderer wraps a node
     * that has no chrome of its own in something that does have one, unless
     * its parent cannot hold the wrapper.
     *
     * @var array<int, string>
     */
    public const NO_CHROME = [
        'inline.img',
        'form.text',
        'form.email',
        'form.password',
        'form.search',
        'form.file',
        'form.checkbox',
        'form.radio',
        'form.hidden',
        'form.textarea',
        'form.select',
        'form.optgroup',
        'form.option',
        'form.datalist',
        'table.colgroup',
        'table.col',
    ];

    /**
     * The types whose tag cannot hold the chrome.
     *
     * The chrome's controls are buttons, and a button cannot hold a button:
     * the browser takes the inner ones out of the chrome, and with them the
     * chrome's Alpine scope goes. Such a node is wrapped instead, so the
     * chrome rides along outside the button.
     *
     * @var array<int, string>
     */
    private const CANNOT_HOLD = [
        'inline.button',
    ];

    /**
     * The tag the chrome uses for a component, or null when the component
     * cannot hold one of its own.
     */
    public static function tag(string $type): ?string
    {
        return self::TAGS[$type] ?? null;
    }

    /**
     * Whether a component can hold the chrome of its own.
     */
    public static function canHold(string $type): bool
    {
        return self::tag($type) !== null && ! in_array($type, self::CANNOT_HOLD, true);
    }

    /**
     * Build the chrome for a node.
     *
     * A component that cannot hold a chrome of its own still gets one, so
     * that it can be wrapped in something that can.
     *
     * @param  Node  $node
     * @param  bool  $draggable  Whether the node can be moved, and so needs a handle.
     * @param  bool  $overlay  Whether the chrome floats over the node instead of riding in its flow.
     */
    public static function build(array $node, bool $draggable = true, array $childTypes = [], bool $overlay = false): MainBackendComponent
    {
        $id = $node['id'];

        $chrome = new MainBackendComponent(self::COMPONENTS[self::tag($node['type']) ?? 'span']);

        // The chrome is a bar in the flow of its node: the layout engine of
        // the browser keeps everything apart, where a floating toolbar landed
        // on content and on other toolbars. The tag the parser needs decides
        // how much of the styling rides on the chrome itself.
        $attributes = [
            'wire:key' => 'chrome-'.$id,
            'x-data' => self::alpine($id),
            // A menu opens and closes itself with the popover api, and a
            // click outside one is a light dismiss of its own. The drawer
            // holds the escape key back while a menu or a dialog is open,
            // so nothing closes the drawer behind a menu.
            // A node may be a link, and a click on a button inside a link
            // still activates it, so the chrome swallows the default.
            'x-on:click.prevent' => 'true',
        ];

        $tag = self::tag($node['type']);

        if ($tag === 'tr') {
            // A row's chrome is a table row as well, so a row cannot be a
            // bar. Its display stays a table row and its styling moves to
            // the cell that carries the toolbar; the display the chrome
            // goes back to when it is shown travels in a variable the
            // stylesheet of the canvas uses.
            $attributes['class'] = 'bw-chrome';
            $attributes['style'] = '--bw-chrome-display: table-row;';
        } elseif ($tag === 'caption') {
            // A table hosts its chrome in a caption, the one element a table
            // may carry besides its parts, and the one that renders above
            // them. The display of a caption has to stay a caption: any
            // other display takes the caption-ness away from the element,
            // and the table puts the toolbar back under the head it was
            // always meant to ride above. So the caption is bare and the
            // bar of the toolbar travels in an element of its own.
            $attributes['class'] = 'bw-chrome';
            $attributes['style'] = '--bw-chrome-display: table-caption;';
        } elseif ($overlay) {
            // A node whose own display puts every first child in its row or
            // grid — a flex, a grid, columns — would take the chrome as a
            // part of that layout. So its chrome is anchored to the node's
            // bottom right corner instead, and the node itself is positioned
            // by the renderer, which the menu hangs from the chrome from.
            // The corner is the bottom one because the bars of the node's own
            // children ride along the top edge: a bar at the top right would
            // hide under the bar of the first row's last cell, and a bar
            // above the node would land on the node before it.
            $attributes['class'] = 'bw-chrome absolute bottom-0 right-0 z-20 items-center justify-end gap-0.5 rounded-md border border-slate-300 bg-white/95 p-0.5 shadow-sm dark:border-slate-600 dark:bg-slate-800/95';
        } else {
            // The bar's flex display comes from the stylesheet of the canvas
            // when it shows the chrome: a display utility class here would
            // win the cascade over the rule that keeps it put away.
            $attributes['class'] = 'bw-chrome relative z-20 mb-1 items-center justify-end gap-0.5 rounded-md border border-slate-300 bg-white/95 p-0.5 shadow-sm dark:border-slate-600 dark:bg-slate-800/95';
        }

        $chrome->setAttributes($attributes);

        $contents = [self::label($node['type'])];

        $descriptor = ComponentRegistry::get($node['type']);
        $name = $descriptor !== null ? $descriptor->label : $node['type'];

        if ($draggable) {
            $contents[] = self::handle();
        }

        $contents[] = self::config($id, $name);

        $allowed = self::allowedChildren($node);
        $acceptsText = self::acceptsText($node);

        if ($allowed !== [] || $acceptsText) {
            $contents[] = self::add($id, $name);
            $contents[] = self::menu($node, $allowed, $acceptsText, $childTypes);
        }

        $contents[] = self::delete($id, $name);

        // A row only holds cells, and the parser would hoist the toolbar out
        // of the chrome row and leave it without its Alpine scope, so on a
        // table section the toolbar travels in a cell. The cell spans the
        // columns of the table, so the toolbar sits at its right edge like
        // the bars of the other nodes do.
        if (self::tag($node['type']) === 'tr') {
            $cell = new MainBackendComponent(self::COMPONENTS['td']);

            $cell->setAttributes([
                // The browser clamps a colspan wider than the table.
                'colspan' => '999',
                'class' => 'relative space-x-0.5 bg-white/95 py-0.5 pr-1 pl-2 text-right dark:bg-slate-800/95',
            ]);

            $cell->setContents($contents);
            $chrome->setContents([$cell]);
        } elseif ($tag === 'caption') {
            // The caption carries the bar of the toolbar in one element of
            // its own: the bar keeps the styling the other bars have, and
            // the caption keeps the display a caption has to have.
            $bar = new MainBackendComponent(ComponentEnum::SPAN);

            $bar->setAttributes([
                'class' => 'relative z-20 mb-1 flex items-center justify-end gap-0.5 rounded-md border border-slate-300 bg-white/95 p-0.5 shadow-sm dark:border-slate-600 dark:bg-slate-800/95',
            ]);

            $bar->setContents($contents);
            $chrome->setContents([$bar]);
        } else {
            $chrome->setContents($contents);
        }

        return $chrome;
    }

    /**
     * The component types a node may contain.
     *
     * @param  Node  $node
     * @return array<int, string>
     */
    public static function allowedChildren(array $node): array
    {
        $descriptor = ComponentRegistry::get($node['type']);

        if ($descriptor === null || ! $descriptor->acceptsChildren()) {
            return [];
        }

        return $descriptor->allowedChildren;
    }

    /**
     * Whether a node takes text.
     *
     * @param  Node  $node
     */
    public static function acceptsText(array $node): bool
    {
        return ComponentRegistry::get($node['type'])?->acceptsText() ?? false;
    }

    /**
     * The Alpine state of a chrome.
     *
     * The delete button arms itself and deletes on a second click, so a
     * misclick on a small target cannot destroy anything. The menu of the
     * chrome is a popover, and the browser owns its open state.
     */
    private static function alpine(string $id): string
    {
        return "{ textMode: false, draft: '', armed: false, arm() { "
            ."if (this.armed) { this.\$wire.remove('".$id."'); this.armed = false; return } "
            .'this.armed = true; setTimeout(() => this.armed = false, 4000) } }';
    }

    /**
     * The label of the component the chrome belongs to.
     *
     * With every chrome shown at all times, a tree shows a bar per node,
     * and stacked bars are indistinguishable without one. The label never
     * takes the pointer, so a click on it keeps selecting the node.
     */
    private static function label(string $type): MainBackendComponent
    {
        $label = new MainBackendComponent(ComponentEnum::SPAN);

        $label->setAttributes([
            'class' => 'mr-auto pointer-events-none select-none pl-1 text-[10px] uppercase leading-4 tracking-wide text-slate-500 dark:text-slate-400',
        ]);

        $label->setContent(ComponentRegistry::get($type)->label);

        return $label;
    }

    /**
     * The handle a node is dragged by.
     */
    private static function handle(): MainBackendComponent
    {
        return self::button('⋮⋮', [
            'wire:sort:handle' => '',
            'title' => 'Drag to move',
            'aria-label' => 'Drag to move',
            'class' => 'cursor-grab rounded px-1 text-xs leading-4 text-slate-500 hover:bg-slate-100 dark:text-slate-300 dark:hover:bg-slate-700',
        ]);
    }

    /**
     * The button that opens the drawer for a node.
     */
    private static function config(string $id, string $name): MainBackendComponent
    {
        return self::button('⚙', [
            'wire:sort:ignore' => '',
            // The node may be a link, and a click on a button inside a link
            // still activates it, so the default is swallowed here too.
            'wire:click.stop.prevent' => "openConfig('".$id."')",
            'title' => 'Configure',
            'aria-label' => 'Configure the '.$name,
            'class' => 'rounded px-1 text-xs leading-4 text-slate-500 hover:bg-slate-100 dark:text-slate-300 dark:hover:bg-slate-700',
        ]);
    }

    /**
     * The button that opens the menu of things to add.
     *
     * The menu is a popover in the top layer, so no toolbar of another node
     * can paint over it. Out of the flow a popover has no containing block
     * to hang from, and anchor positioning ties it back to this button.
     *
     * The chrome keeps the default of every click under it, and the popover
     * target works as default action of the click itself, so the button
     * stops the chrome from seeing its click at all.
     */
    private static function add(string $id, string $name): MainBackendComponent
    {
        return self::button('+', [
            'wire:sort:ignore' => '',
            // The stop is only there to keep the click from the chrome.
            'x-on:click.stop' => 'true',
            'popovertarget' => 'bw-menu-'.$id,
            'title' => 'Add content',
            'aria-label' => 'Add content to the '.$name,
            'style' => 'anchor-name: --bw-add-'.$id,
            'class' => 'rounded px-1 text-xs leading-4 text-slate-500 hover:bg-slate-100 dark:text-slate-300 dark:hover:bg-slate-700',
        ]);
    }

    /**
     * The button that deletes a node, once it has been armed.
     */
    private static function delete(string $id, string $name): MainBackendComponent
    {
        $button = new MainBackendComponent(ComponentEnum::BUTTON);

        $button->setAttributes([
            'type' => 'button',
            'wire:sort:ignore' => '',
            'x-on:click' => 'arm()',
            'x-on:mouseleave' => 'armed = false',
            'x-bind:title' => "armed ? 'Click again to delete' : 'Delete'",
            'aria-label' => 'Delete the '.$name,
            'class' => 'rounded px-1 text-xs leading-4 text-red-600 hover:bg-red-50 dark:text-red-400',
        ]);

        $icon = new MainBackendComponent(ComponentEnum::SPAN);
        $icon->setAttributes(['x-text' => "armed ? 'Delete?' : '✕'"]);

        $button->setContents([$icon]);

        return $button;
    }

    /**
     * The table sections a table keeps one of.
     *
     * A head, a body, a foot and a caption are parts a table holds once, so
     * the menu of a table only lists the ones it does not hold yet, and the
     * canvas refuses to add a section a table already holds.
     */
    public const TABLE_SECTIONS = [
        'table.thead',
        'table.tbody',
        'table.tfoot',
        'table.caption',
    ];

    /**
     * The menu of things that can be added to a node.
     *
     * Every part of it is phrasing content, so the menu can live inside a
     * paragraph without the parser pulling it out. The menu is a popover:
     * the browser takes it to the top layer, whence nothing on the canvas
     * can paint over it, and gives it the light dismisses, the escape key
     * and one open menu at a time. The table operations come first, the
     * text entry is only offered where the node takes text, and the
     * component list only where it takes children: a list or a table row
     * has somewhere to put an item, but nowhere to put a run of text.
     *
     * @param  array<int, string>  $allowed
     * @param  array<int, string>  $childTypes
     */
    private static function menu(array $node, array $allowed, bool $acceptsText, array $childTypes = []): MainBackendComponent
    {
        $menu = new MainBackendComponent(ComponentEnum::SPAN);

        $menu->setAttributes([
            'id' => 'bw-menu-'.$node['id'],
            'popover' => 'auto',
            'role' => 'group',
            'aria-label' => 'Add content',
            'style' => 'position-anchor: --bw-add-'.$node['id'],
            'class' => 'bw-menu w-56 max-h-64 overflow-y-auto rounded-md border border-slate-300 bg-white p-1 shadow-lg dark:border-slate-600 dark:bg-slate-800',
        ]);

        $contents = [];

        $actions = self::actions($node);

        if ($actions !== []) {
            $contents[] = self::actionGroup($actions);
        }

        if ($acceptsText) {
            $contents[] = self::textOption();
        }

        $allowed = $node['type'] === 'table.table'
            ? self::withoutHeldSections($allowed, $childTypes)
            : $allowed;

        if ($allowed !== []) {
            $contents[] = self::componentList($node['id'], $allowed, $acceptsText);
        }

        if ($acceptsText) {
            $contents[] = self::textInput($node['id']);
        }

        $menu->setContents($contents);

        return $menu;
    }

    /**
     * The allowed children of a table, without the sections it holds.
     *
     * @param  array<int, string>  $allowed
     * @param  array<int, string>  $childTypes
     * @return array<int, string>
     */
    private static function withoutHeldSections(array $allowed, array $childTypes): array
    {
        return array_values(array_filter(
            $allowed,
            fn (string $type): bool => ! in_array($type, self::TABLE_SECTIONS, true)
                || ! in_array($type, $childTypes, true),
        ));
    }

    /**
     * The table operations the menu of a node offers.
     *
     * A table is the one component that is not assembled part by part: the
     * node that can hold one offers its scaffold, and the parts offer the
     * row and column operations that finish the job. Every operation calls
     * the canvas, where the tree is.
     *
     * @param  Node  $node
     * @return array<int, array{label: string, wire: string}>
     */
    private static function actions(array $node): array
    {
        $actions = [];
        $id = $node['id'];

        if (in_array('table.table', self::allowedChildren($node), true)) {
            $actions[] = [
                'label' => 'Add table…',
                'wire' => "\$wire.tableMode('".$id."')",
            ];
        }

        if ($node['type'] === 'table.tr') {
            $actions[] = [
                'label' => 'Row below',
                'wire' => "\$wire.addRowBelow('".$id."')",
            ];

            $actions[] = [
                'label' => 'Add column',
                'wire' => "\$wire.addColumn('".$id."')",
            ];
        }

        if (in_array($node['type'], ['table.th', 'table.td'], true)) {
            $actions[] = [
                'label' => 'Delete column',
                'wire' => "\$wire.deleteColumn('".$id."')",
            ];
        }

        return $actions;
    }

    /**
     * The table operations of a menu, in a band of their own.
     *
     * @param  array<int, array{label: string, wire: string}>  $actions
     */
    private static function actionGroup(array $actions): MainBackendComponent
    {
        $group = new MainBackendComponent(ComponentEnum::SPAN);

        $group->setAttributes([
            'x-show' => '!textMode',
            'class' => 'mb-1 border-b border-slate-100 pb-1 dark:border-slate-700',
        ]);

        $items = [];

        foreach ($actions as $action) {
            $item = new MainBackendComponent(ComponentEnum::BUTTON);

            $item->setAttributes([
                'type' => 'button',
                'wire:sort:ignore' => '',
                'x-on:click' => "\$el.closest('[popover]')?.hidePopover(); ".$action['wire'],
                'class' => 'block w-full cursor-pointer rounded px-2 py-1 text-left text-xs font-medium text-indigo-600 hover:bg-indigo-50 dark:text-indigo-300 dark:hover:bg-indigo-500/20',
            ]);

            $item->setContent($action['label']);

            $items[] = $item;
        }

        $group->setContents($items);

        return $group;
    }

    /**
     * The entry that switches the menu over to typing text.
     */
    private static function textOption(): MainBackendComponent
    {
        $option = new MainBackendComponent(ComponentEnum::BUTTON);

        $option->setAttributes([
            'type' => 'button',
            'wire:sort:ignore' => '',
            'x-show' => '!textMode',
            'x-on:click' => 'textMode = true; $nextTick(() => $refs.textInput.focus())',
            'class' => 'block w-full cursor-pointer rounded px-2 py-1 text-left text-xs font-medium text-slate-700 hover:bg-indigo-50 hover:text-indigo-700 dark:text-slate-200 dark:hover:bg-indigo-500/20 dark:hover:text-indigo-300',
        ]);

        $option->setContent('Text');

        return $option;
    }

    /**
     * The components that can be added to a node.
     *
     * @param  array<int, string>  $allowed
     */
    private static function componentList(string $id, array $allowed, bool $divided): MainBackendComponent
    {
        $list = new MainBackendComponent(ComponentEnum::SPAN);

        $attributes = ['x-show' => '!textMode'];

        // The rule is only there to separate the list from the text entry
        // above it. On its own it would be a line under nothing.
        if ($divided) {
            $attributes['class'] = 'mt-1 border-t border-slate-100 pt-1 dark:border-slate-700';
        }

        $list->setAttributes($attributes);

        $items = [];

        foreach ($allowed as $type) {
            $descriptor = ComponentRegistry::get($type);

            if ($descriptor === null) {
                continue;
            }

            $item = new MainBackendComponent(ComponentEnum::BUTTON);
            $item->setAttributes([
                'type' => 'button',
                'wire:sort:ignore' => '',
                'x-on:click' => "\$el.closest('[popover]')?.hidePopover(); \$wire.add('".$type."', '".$id."')",
                'class' => 'block w-full cursor-pointer rounded px-2 py-1 text-left text-xs text-slate-700 hover:bg-indigo-50 hover:text-indigo-700 dark:text-slate-200 dark:hover:bg-indigo-500/20 dark:hover:text-indigo-300',
            ]);
            $item->setContent($descriptor->label);

            $items[] = $item;
        }

        $list->setContents($items);

        return $list;
    }

    /**
     * The field the new text is typed into.
     */
    private static function textInput(string $id): MainBackendComponent
    {
        $input = new MainBackendComponent(ComponentEnum::TEXT_INPUT);

        $input->setAttributes([
            'x-show' => 'textMode',
            'x-cloak' => '',
            'x-ref' => 'textInput',
            'x-model' => 'draft',
            'x-on:keydown.enter.prevent' => "\$wire.addText('".$id."', draft).then(() => { draft = ''; textMode = false })",
            'placeholder' => 'Text',
            'class' => 'w-full rounded border border-slate-200 px-2 py-1 text-xs dark:border-slate-600 dark:bg-slate-900',
        ]);

        return $input;
    }

    /**
     * One of the chrome's buttons.
     *
     * @param  array<string, string>  $attributes
     */
    private static function button(string $glyph, array $attributes): MainBackendComponent
    {
        $button = new MainBackendComponent(ComponentEnum::BUTTON);

        $button->setAttributes(['type' => 'button', ...$attributes]);
        $button->setContent($glyph);

        return $button;
    }
}
