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
 * cover the elements that only accept one kind of child.
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
        'form.textarea' => 'span',

        // Lists only hold list items.
        'lists.ul' => 'li',
        'lists.ol' => 'li',

        // Rows only hold cells.
        'table.tr' => 'td',

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
     * The tag the chrome uses for a component, or null when the component
     * cannot hold one of its own.
     */
    public static function tag(string $type): ?string
    {
        return self::TAGS[$type] ?? null;
    }

    /**
     * Build the chrome for a node.
     *
     * A component that cannot hold a chrome of its own still gets one, so
     * that it can be wrapped in something that can.
     *
     * @param  Node  $node
     * @param  bool  $draggable  Whether the node can be moved, and so needs a handle.
     */
    public static function build(array $node, bool $draggable = true): MainBackendComponent
    {
        $id = $node['id'];

        $chrome = new MainBackendComponent(self::COMPONENTS[self::tag($node['type']) ?? 'span']);

        $chrome->setAttributes([
            'wire:key' => 'chrome-'.$id,
            'x-data' => self::alpine($id),
            'x-on:click.outside' => 'open = false',
            'x-on:keydown.escape.window' => 'open = false',
            // A node may be a link, and a click on a button inside a link
            // still activates it, so the chrome swallows the default.
            'x-on:click.prevent' => 'true',
            // The marker lets the sort configuration keep the chrome out of
            // the way, so a drag takes the node and not its toolbar.
            'class' => 'bw-chrome absolute -top-2.5 right-0 z-20 flex items-center gap-0.5 rounded-md border border-slate-300 bg-white/95 p-0.5 shadow-sm dark:border-slate-600 dark:bg-slate-800/95',
        ]);

        $contents = [];

        if ($draggable) {
            $contents[] = self::handle();
        }

        $contents[] = self::config($id);

        $allowed = self::allowedChildren($node);

        if ($allowed !== []) {
            $contents[] = self::add();
            $contents[] = self::menu($id, $allowed);
        }

        $contents[] = self::delete($id);

        $chrome->setContents($contents);

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
     * The Alpine state of a chrome.
     *
     * The delete button arms itself and deletes on a second click, so a
     * misclick on a small target cannot destroy anything.
     */
    private static function alpine(string $id): string
    {
        return "{ open: false, textMode: false, draft: '', armed: false, arm() { "
            ."if (this.armed) { this.\$wire.remove('".$id."'); this.armed = false; return } "
            .'this.armed = true; setTimeout(() => this.armed = false, 4000) } }';
    }

    /**
     * The handle a node is dragged by.
     */
    private static function handle(): MainBackendComponent
    {
        return self::button('⋮⋮', [
            'wire:sort:handle' => '',
            'title' => 'Drag to move',
            'class' => 'cursor-grab rounded px-1 text-xs leading-4 text-slate-500 hover:bg-slate-100 dark:text-slate-300 dark:hover:bg-slate-700',
        ]);
    }

    /**
     * The button that opens the drawer for a node.
     */
    private static function config(string $id): MainBackendComponent
    {
        return self::button('⚙', [
            'wire:sort:ignore' => '',
            // The node may be a link, and a click on a button inside a link
            // still activates it, so the default is swallowed here too.
            'wire:click.stop.prevent' => "openConfig('".$id."')",
            'title' => 'Configure',
            'class' => 'rounded px-1 text-xs leading-4 text-slate-500 hover:bg-slate-100 dark:text-slate-300 dark:hover:bg-slate-700',
        ]);
    }

    /**
     * The button that opens the menu of things to add.
     */
    private static function add(): MainBackendComponent
    {
        return self::button('+', [
            'wire:sort:ignore' => '',
            'x-on:click' => 'open = !open',
            'title' => 'Add content',
            'class' => 'rounded px-1 text-xs leading-4 text-slate-500 hover:bg-slate-100 dark:text-slate-300 dark:hover:bg-slate-700',
        ]);
    }

    /**
     * The button that deletes a node, once it has been armed.
     */
    private static function delete(string $id): MainBackendComponent
    {
        $button = new MainBackendComponent(ComponentEnum::BUTTON);

        $button->setAttributes([
            'type' => 'button',
            'wire:sort:ignore' => '',
            'x-on:click' => 'open = false; arm()',
            'x-on:mouseleave' => 'armed = false',
            'x-bind:title' => "armed ? 'Click again to delete' : 'Delete'",
            'class' => 'rounded px-1 text-xs leading-4 text-red-500 hover:bg-red-50 dark:text-red-400',
        ]);

        $icon = new MainBackendComponent(ComponentEnum::SPAN);
        $icon->setAttributes(['x-text' => "armed ? 'Delete?' : '✕'"]);

        $button->setContents([$icon]);

        return $button;
    }

    /**
     * The menu of things that can be added to a node.
     *
     * Every part of it is phrasing content, so the menu can live inside a
     * paragraph without the parser pulling it out.
     *
     * @param  array<int, string>  $allowed
     */
    private static function menu(string $id, array $allowed): MainBackendComponent
    {
        $menu = new MainBackendComponent(ComponentEnum::SPAN);

        $menu->setAttributes([
            'x-show' => 'open',
            'x-cloak' => '',
            'class' => 'absolute top-full right-0 z-30 mt-1 w-56 max-h-64 overflow-y-auto rounded-md border border-slate-300 bg-white p-1 shadow-lg dark:border-slate-600 dark:bg-slate-800',
        ]);

        $contents = [];

        $textOption = new MainBackendComponent(ComponentEnum::SPAN);
        $textOption->setAttributes([
            'x-show' => '!textMode',
            'x-on:click' => 'textMode = true; $nextTick(() => $refs.textInput.focus())',
            'class' => 'block cursor-pointer rounded px-2 py-1 text-xs font-medium text-slate-700 hover:bg-indigo-50 hover:text-indigo-700 dark:text-slate-200 dark:hover:bg-indigo-500/20 dark:hover:text-indigo-300',
        ]);
        $textOption->setContent('Text');
        $contents[] = $textOption;

        $list = new MainBackendComponent(ComponentEnum::SPAN);
        $list->setAttributes([
            'x-show' => '!textMode',
            'class' => 'mt-1 border-t border-slate-100 pt-1 dark:border-slate-700',
        ]);

        $items = [];

        foreach ($allowed as $type) {
            $descriptor = ComponentRegistry::get($type);

            if ($descriptor === null) {
                continue;
            }

            $item = new MainBackendComponent(ComponentEnum::SPAN);
            $item->setAttributes([
                'wire:sort:ignore' => '',
                'x-on:click' => "open = false; \$wire.add('".$type."', '".$id."')",
                'class' => 'block cursor-pointer rounded px-2 py-1 text-xs text-slate-700 hover:bg-indigo-50 hover:text-indigo-700 dark:text-slate-200 dark:hover:bg-indigo-500/20 dark:hover:text-indigo-300',
            ]);
            $item->setContent($descriptor->label);

            $items[] = $item;
        }

        $list->setContents($items);
        $contents[] = $list;

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
        $contents[] = $input;

        $menu->setContents($contents);

        return $menu;
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
