<?php

declare(strict_types=1);

namespace App\Builder\Registries;

use App\Builder\Components\ComponentDescriptor;
use App\Builder\Components\ContentMode;
use Juaniquillo\BackendComponents\Enums\ComponentEnum;

/**
 * The builder palette: every component the user may place, how it is grouped,
 * what it may contain and which attributes it tends to carry.
 *
 * The nesting rules deliberately mirror the HTML content models. That is not
 * cosmetic: the browser silently hoists invalid children out of their parent
 * (a <div> inside a <ul> is moved out by the parser), so keeping the dragged
 * DOM valid is the same thing as keeping the rules correct.
 */
final class ComponentRegistry
{
    public const GROUP_LAYOUT = 'Layout';

    public const GROUP_TYPOGRAPHY = 'Typography';

    public const GROUP_TEXT = 'Text';

    public const GROUP_MEDIA = 'Media & Links';

    public const GROUP_LISTS = 'Lists';

    public const GROUP_TABLE = 'Table';

    public const GROUP_FORM = 'Form';

    /**
     * Inline elements: allowed inside paragraphs, headings and other inline elements.
     *
     * @var array<int, string>
     */
    private const PHRASING = [
        'inline.button',
        'inline.link',
        'inline.img',
        'inline.span',
        'inline.bold',
        'inline.em',
        'inline.italic',
        'inline.strong',
        'inline.small',
        'form.label',
        'form.legend',
        'form.text',
        'form.email',
        'form.password',
        'form.search',
        'form.file',
        'form.checkbox',
        'form.radio',
    ];

    /**
     * Block elements: allowed inside divs, list items, form controls and so on.
     *
     * @var array<int, string>
     */
    private const FLOW = [
        'template',
        'collection',
        'div',
        'paragraph',
        'headers.h1',
        'headers.h2',
        'headers.h3',
        'headers.h4',
        'headers.h5',
        'headers.h6',
        'inline.button',
        'inline.link',
        'inline.img',
        'inline.span',
        'inline.bold',
        'inline.em',
        'inline.italic',
        'inline.strong',
        'inline.small',
        'form.form',
        'form.fieldset',
        'form.label',
        'form.legend',
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
        'form.datalist',
        'lists.ul',
        'lists.ol',
        'table.table',
        'details.details',
        'details.summary',
        'layers.dialog',
    ];

    /**
     * Attributes every component may carry.
     *
     * @var array<int, string>
     */
    private const SUGGESTED_ATTRIBUTES = [
        'id',
        'class',
        'title',
        'role',
        'lang',
        'dir',
        'hidden',
        'tabindex',
    ];

    /**
     * Attributes shared by the text-like form inputs.
     *
     * @var array<int, string>
     */
    private const SUGGESTED_INPUT_ATTRIBUTES = [
        'name',
        'value',
        'placeholder',
        'disabled',
        'readonly',
        'required',
        'maxlength',
        'minlength',
        'min',
        'max',
        'step',
        'pattern',
        'autocomplete',
        'multiple',
        'accept',
    ];

    /**
     * Components that render as inline elements.
     *
     * An empty inline element has no box at all, so the builder gives it one
     * to make it visible and to give its toolbar something to sit against.
     *
     * @var array<int, string>
     */
    private const INLINE = [
        'inline.span',
        'inline.bold',
        'inline.strong',
        'inline.em',
        'inline.italic',
        'inline.small',
        'inline.link',
        'inline.button',
        'form.label',
    ];

    /**
     * Elements that may not hold an element of their own kind.
     *
     * A button cannot hold a button, and a link cannot hold a link: the
     * browser takes the inner one out of the outer one, and the tree the
     * builder holds is no longer the tree in the page.
     *
     * @var array<int, string>
     */
    private const NO_SELF_NESTING = [
        'inline.button',
        'inline.link',
    ];

    /**
     * @var array<string, ComponentDescriptor>|null
     */
    private static ?array $descriptors = null;

    /**
     * Every component the builder offers, keyed by its ComponentEnum value.
     *
     * @return array<string, ComponentDescriptor>
     */
    public static function all(): array
    {
        if (self::$descriptors !== null) {
            return self::$descriptors;
        }

        $descriptors = [];

        foreach (self::definitions() as $definition) {
            $descriptor = new ComponentDescriptor(
                type: $definition['type'],
                label: $definition['label'],
                group: $definition['group'],
                contentMode: $definition['contentMode'],
                allowedChildren: $definition['children'],
                suggestedAttributes: $definition['attributes'],
            );

            $descriptors[$descriptor->type] = $descriptor;
        }

        return self::$descriptors = $descriptors;
    }

    public static function get(string $type): ?ComponentDescriptor
    {
        return self::all()[$type] ?? null;
    }

    /**
     * Whether a component renders as an inline element.
     */
    public static function isInline(string $type): bool
    {
        return in_array($type, self::INLINE, true);
    }

    /**
     * Whether a component is a text element, rather than a container.
     *
     * A text element holds phrasing content, never block content, so its
     * content is the text you type into it. A div holds a div, so its content
     * is the children you add to it.
     */
    public static function isTextual(string $type): bool
    {
        $descriptor = self::get($type);

        if ($descriptor === null || ! $descriptor->acceptsText()) {
            return false;
        }

        return array_diff($descriptor->allowedChildren, self::PHRASING) === [];
    }

    /**
     * The palette grouped for display, preserving the declared group order.
     *
     * @return array<string, array<string, ComponentDescriptor>>
     */
    public static function groups(): array
    {
        $groups = [];

        foreach (self::all() as $descriptor) {
            $groups[$descriptor->group][$descriptor->type] = $descriptor;
        }

        return $groups;
    }

    /**
     * The enum case name for a component type, e.g. "headers.h1" => "H1".
     *
     * Used by the code generator, which emits ComponentEnum::H1.
     */
    public static function enumCase(string $type): ?string
    {
        foreach (ComponentEnum::cases() as $case) {
            if ($case->value === $type) {
                return $case->name;
            }
        }

        return null;
    }

    /**
     * @return array<int, array{
     *  type: string,
     *  label: string,
     *  group: string,
     *  contentMode: ContentMode,
     *  children: array<int, string>,
     *  attributes: array<int, string>,
     * }>
     */
    private static function definitions(): array
    {
        $definitions = [];

        $add = function (
            string $type,
            string $label,
            string $group,
            ContentMode $contentMode,
            array $children,
            array $attributes,
        ) use (&$definitions): void {
            if (in_array($type, self::NO_SELF_NESTING, true)) {
                $children = array_values(array_filter(
                    $children,
                    static fn (string $child): bool => $child !== $type,
                ));
            }

            $definitions[] = [
                'type' => $type,
                'label' => $label,
                'group' => $group,
                'contentMode' => $contentMode,
                'children' => $children,
                'attributes' => $attributes,
            ];
        };

        // --- Layout -------------------------------------------------------
        $add('div', 'Div', self::GROUP_LAYOUT, ContentMode::TextAndChildren, self::FLOW, self::SUGGESTED_ATTRIBUTES);
        $add('collection', 'Collection', self::GROUP_LAYOUT, ContentMode::Children, self::FLOW, self::SUGGESTED_ATTRIBUTES);
        $add('template', 'Template', self::GROUP_LAYOUT, ContentMode::Children, self::FLOW, self::SUGGESTED_ATTRIBUTES);
        $add('details.details', 'Details', self::GROUP_LAYOUT, ContentMode::TextAndChildren, ['details.summary', ...self::FLOW], [...self::SUGGESTED_ATTRIBUTES, 'open']);
        $add('details.summary', 'Summary', self::GROUP_LAYOUT, ContentMode::TextAndChildren, self::PHRASING, self::SUGGESTED_ATTRIBUTES);
        $add('layers.dialog', 'Dialog', self::GROUP_LAYOUT, ContentMode::TextAndChildren, self::FLOW, [...self::SUGGESTED_ATTRIBUTES, 'open']);
        // --- Typography ---------------------------------------------------
        $add('paragraph', 'Paragraph', self::GROUP_TYPOGRAPHY, ContentMode::TextAndChildren, self::PHRASING, self::SUGGESTED_ATTRIBUTES);
        $add('headers.h1', 'Heading 1', self::GROUP_TYPOGRAPHY, ContentMode::TextAndChildren, self::PHRASING, self::SUGGESTED_ATTRIBUTES);
        $add('headers.h2', 'Heading 2', self::GROUP_TYPOGRAPHY, ContentMode::TextAndChildren, self::PHRASING, self::SUGGESTED_ATTRIBUTES);
        $add('headers.h3', 'Heading 3', self::GROUP_TYPOGRAPHY, ContentMode::TextAndChildren, self::PHRASING, self::SUGGESTED_ATTRIBUTES);
        $add('headers.h4', 'Heading 4', self::GROUP_TYPOGRAPHY, ContentMode::TextAndChildren, self::PHRASING, self::SUGGESTED_ATTRIBUTES);
        $add('headers.h5', 'Heading 5', self::GROUP_TYPOGRAPHY, ContentMode::TextAndChildren, self::PHRASING, self::SUGGESTED_ATTRIBUTES);
        $add('headers.h6', 'Heading 6', self::GROUP_TYPOGRAPHY, ContentMode::TextAndChildren, self::PHRASING, self::SUGGESTED_ATTRIBUTES);
        // --- Text ----------------------------------------------------------
        $add('inline.span', 'Span', self::GROUP_TEXT, ContentMode::TextAndChildren, self::PHRASING, self::SUGGESTED_ATTRIBUTES);
        $add('inline.bold', 'Bold', self::GROUP_TEXT, ContentMode::TextAndChildren, self::PHRASING, self::SUGGESTED_ATTRIBUTES);
        $add('inline.strong', 'Strong', self::GROUP_TEXT, ContentMode::TextAndChildren, self::PHRASING, self::SUGGESTED_ATTRIBUTES);
        $add('inline.em', 'Emphasis', self::GROUP_TEXT, ContentMode::TextAndChildren, self::PHRASING, self::SUGGESTED_ATTRIBUTES);
        $add('inline.italic', 'Italic', self::GROUP_TEXT, ContentMode::TextAndChildren, self::PHRASING, self::SUGGESTED_ATTRIBUTES);
        $add('inline.small', 'Small', self::GROUP_TEXT, ContentMode::TextAndChildren, self::PHRASING, self::SUGGESTED_ATTRIBUTES);
        // --- Media & links ------------------------------------------------
        $add('inline.link', 'Link', self::GROUP_MEDIA, ContentMode::TextAndChildren, self::PHRASING, [...self::SUGGESTED_ATTRIBUTES, 'href', 'target', 'rel', 'download']);
        $add('inline.img', 'Image', self::GROUP_MEDIA, ContentMode::None, [], [...self::SUGGESTED_ATTRIBUTES, 'src', 'alt', 'width', 'height', 'loading', 'decoding']);
        $add('inline.button', 'Button', self::GROUP_MEDIA, ContentMode::TextAndChildren, self::PHRASING, [...self::SUGGESTED_ATTRIBUTES, 'type', 'name', 'value', 'disabled']);
        // --- Lists ---------------------------------------------------------
        $add('lists.ul', 'Unordered List', self::GROUP_LISTS, ContentMode::Children, ['lists.li'], self::SUGGESTED_ATTRIBUTES);
        $add('lists.ol', 'Ordered List', self::GROUP_LISTS, ContentMode::Children, ['lists.li'], self::SUGGESTED_ATTRIBUTES);
        $add('lists.li', 'List Item', self::GROUP_LISTS, ContentMode::TextAndChildren, self::FLOW, self::SUGGESTED_ATTRIBUTES);
        // --- Table ---------------------------------------------------------
        $add('table.table', 'Table', self::GROUP_TABLE, ContentMode::Children, ['table.caption', 'table.colgroup', 'table.thead', 'table.tbody', 'table.tfoot', 'table.tr'], self::SUGGESTED_ATTRIBUTES);
        $add('table.thead', 'Table Head', self::GROUP_TABLE, ContentMode::Children, ['table.tr'], self::SUGGESTED_ATTRIBUTES);
        $add('table.tbody', 'Table Body', self::GROUP_TABLE, ContentMode::Children, ['table.tr'], self::SUGGESTED_ATTRIBUTES);
        $add('table.tfoot', 'Table Foot', self::GROUP_TABLE, ContentMode::Children, ['table.tr'], self::SUGGESTED_ATTRIBUTES);
        $add('table.tr', 'Table Row', self::GROUP_TABLE, ContentMode::Children, ['table.th', 'table.td'], self::SUGGESTED_ATTRIBUTES);
        $add('table.th', 'Header Cell', self::GROUP_TABLE, ContentMode::TextAndChildren, self::PHRASING, [...self::SUGGESTED_ATTRIBUTES, 'colspan', 'rowspan', 'scope', 'headers']);
        $add('table.td', 'Data Cell', self::GROUP_TABLE, ContentMode::TextAndChildren, self::PHRASING, [...self::SUGGESTED_ATTRIBUTES, 'colspan', 'rowspan']);
        $add('table.caption', 'Caption', self::GROUP_TABLE, ContentMode::TextAndChildren, self::PHRASING, [...self::SUGGESTED_ATTRIBUTES, 'colspan']);
        $add('table.colgroup', 'Column Group', self::GROUP_TABLE, ContentMode::Children, ['table.col'], self::SUGGESTED_ATTRIBUTES);
        $add('table.col', 'Column', self::GROUP_TABLE, ContentMode::None, [], [...self::SUGGESTED_ATTRIBUTES, 'span']);
        // --- Form ----------------------------------------------------------
        $add('form.form', 'Form', self::GROUP_FORM, ContentMode::TextAndChildren, self::FLOW, [...self::SUGGESTED_ATTRIBUTES, 'action', 'method', 'enctype', 'novalidate']);
        $add('form.fieldset', 'Fieldset', self::GROUP_FORM, ContentMode::TextAndChildren, self::FLOW, [...self::SUGGESTED_ATTRIBUTES, 'name', 'disabled']);
        $add('form.legend', 'Legend', self::GROUP_FORM, ContentMode::TextAndChildren, self::PHRASING, self::SUGGESTED_ATTRIBUTES);
        $add('form.label', 'Label', self::GROUP_FORM, ContentMode::TextAndChildren, self::PHRASING, [...self::SUGGESTED_ATTRIBUTES, 'for']);
        $add('form.text', 'Text Input', self::GROUP_FORM, ContentMode::None, [], [...self::SUGGESTED_ATTRIBUTES, ...self::SUGGESTED_INPUT_ATTRIBUTES]);
        $add('form.email', 'Email Input', self::GROUP_FORM, ContentMode::None, [], [...self::SUGGESTED_ATTRIBUTES, ...self::SUGGESTED_INPUT_ATTRIBUTES]);
        $add('form.password', 'Password Input', self::GROUP_FORM, ContentMode::None, [], [...self::SUGGESTED_ATTRIBUTES, ...self::SUGGESTED_INPUT_ATTRIBUTES]);
        $add('form.search', 'Search Input', self::GROUP_FORM, ContentMode::None, [], [...self::SUGGESTED_ATTRIBUTES, ...self::SUGGESTED_INPUT_ATTRIBUTES]);
        $add('form.file', 'File Input', self::GROUP_FORM, ContentMode::None, [], [...self::SUGGESTED_ATTRIBUTES, ...self::SUGGESTED_INPUT_ATTRIBUTES]);
        $add('form.checkbox', 'Checkbox', self::GROUP_FORM, ContentMode::None, [], [...self::SUGGESTED_ATTRIBUTES, 'name', 'value', 'checked', 'disabled', 'required']);
        $add('form.radio', 'Radio', self::GROUP_FORM, ContentMode::None, [], [...self::SUGGESTED_ATTRIBUTES, 'name', 'value', 'checked', 'disabled', 'required']);
        $add('form.hidden', 'Hidden Input', self::GROUP_FORM, ContentMode::None, [], [...self::SUGGESTED_ATTRIBUTES, 'name', 'value']);
        $add('form.textarea', 'Textarea', self::GROUP_FORM, ContentMode::Text, [], [...self::SUGGESTED_ATTRIBUTES, ...self::SUGGESTED_INPUT_ATTRIBUTES, 'rows', 'cols']);
        $add('form.select', 'Select', self::GROUP_FORM, ContentMode::Children, ['form.optgroup', 'form.option'], [...self::SUGGESTED_ATTRIBUTES, 'name', 'disabled', 'required', 'multiple', 'size']);
        $add('form.optgroup', 'Option Group', self::GROUP_FORM, ContentMode::Children, ['form.option'], [...self::SUGGESTED_ATTRIBUTES, 'label', 'disabled']);
        $add('form.option', 'Option', self::GROUP_FORM, ContentMode::Text, [], [...self::SUGGESTED_ATTRIBUTES, 'value', 'selected', 'disabled', 'label']);
        $add('form.datalist', 'Datalist', self::GROUP_FORM, ContentMode::Children, ['form.option'], self::SUGGESTED_ATTRIBUTES);

        return $definitions;
    }
}
