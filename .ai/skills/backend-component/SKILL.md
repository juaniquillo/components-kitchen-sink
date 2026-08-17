---
name: backend-component
description: Build and compose dynamic HTML components in PHP using the juaniquillo/laravel-backend-component package — create component trees, apply Tailwind themes, manage settings, integrate Livewire, serialize/deserialize component structures, and resolve components locally.
---

# Backend Component Development

## When to Use This Skill
Use this skill when the user needs to:
- Create a new HTML component (button, div, table, form element, modal, etc.)
- Compose multiple components into a tree structure
- Apply or create Tailwind CSS theme classes to components
- Add Livewire integration to a component
- Serialize a component tree to an array or restore it from one
- Build custom Blade templates for new component types
- Add a new component type (enum case + blade file + tests)
- Use the concrete `DivComponent` for quick divs without the builder
- Build tables programmatically using `TableUtil` and `CellBag`
- Build modals programmatically using `ModalUtil`
- Resolve components or themes from the consuming app's local views

## Creating Components

Components are created via the builder, always starting with `ComponentBuilder::make()`:

```php
use Juaniquillo\BackendComponents\Builders\ComponentBuilder;
use Juaniquillo\BackendComponents\Enums\ComponentEnum;

// Simple components
$button = ComponentBuilder::make(ComponentEnum::BUTTON);
$div = ComponentBuilder::make(ComponentEnum::DIV);
$input = ComponentBuilder::make(ComponentEnum::TEXT_INPUT);
$table = ComponentBuilder::make(ComponentEnum::TABLE);

// By string name (path)
$custom = ComponentBuilder::make('inline.button');
```

### All available components

| Category | Cases |
|---|---|
| **Template** | `TEMPLATE` |
| **Collection** | `COLLECTION` |
| **Block** | `DIV`, `PARAGRAPH` |
| **Inline** | `BUTTON`, `LINK`, `IMG`, `SPAN`, `BOLD`, `EM`, `ITALIC`, `STRONG`, `SMALL` |
| **Headers** | `H1`, `H2`, `H3`, `H4`, `H5`, `H6` |
| **Form** | `FORM`, `LABEL`, `LEGEND`, `FIELDSET`, `TEXT_INPUT`, `FILE_INPUT`, `EMAIL_INPUT`, `SEARCH_INPUT`, `PASSWORD_INPUT`, `CHECKBOX_INPUT`, `HIDDEN_INPUT`, `RADIO_INPUT`, `DATALIST`, `TEXTAREA`, `SELECT`, `OPTGROUP`, `OPTION` |
| **Table** | `TABLE`, `THEAD`, `TBODY`, `TFOOT`, `TR`, `TH`, `TD`, `CAPTION`, `COLGROUP`, `COL` |
| **Lists** | `OL`, `UL`, `LI` |
| **Details** | `DETAILS`, `SUMMARY` |
| **Layers** | `DIALOG` |

## Setting Content

Use **`setContent()`** for a single item and **`setContents()`** for multiple items at once:

```php
$div = ComponentBuilder::make(ComponentEnum::DIV)
    ->setContent('Hello')                               // single (no key)
    ->setContent('World', 'key_1')                      // single with key
    ->setContents([                                      // batch (ignores keys)
        ComponentBuilder::make(ComponentEnum::SPAN)->setContent('A'),
        ComponentBuilder::make(ComponentEnum::SPAN)->setContent('B'),
    ])
    ->setContents([                                      // batch with keys (overwrites existing)
        'item_1' => ComponentBuilder::make(ComponentEnum::SPAN)->setContent('A'),
        'item_2' => ComponentBuilder::make(ComponentEnum::SPAN)->setContent('B'),
    ], overwrite: true);
```

## Setting Attributes

Use **`setAttribute()`** for a single attribute and **`setAttributes()`** for multiple at once:

```php
$div = ComponentBuilder::make(ComponentEnum::DIV)
    ->setAttribute('id', 'my-id')
    ->setAttribute('class', 'custom-class')
    ->setAttributes(['data-foo' => 'bar', 'style' => 'display:none']);
```

## Composition (Nested Components)

Components can be nested by passing them as content:

```php
$card = ComponentBuilder::make(ComponentEnum::DIV)
    ->setAttribute('class', 'card')
    ->setContent(
        ComponentBuilder::make(ComponentEnum::H2)->setContent('Card Title')
    )
    ->setContent(
        ComponentBuilder::make(ComponentEnum::PARAGRAPH)->setContent('Card body text')
    );
```

## Applying Themes

Themes are predefined in `resources/views/_themes/tailwind/`. Each file returns a PHP array of Tailwind classes keyed by variant name:

```php
// resources/views/_themes/tailwind/action.blade.php
return [
    'default' => "whitespace-nowrap bg-blue-700 hover:bg-blue-800",
    'success' => "whitespace-nowrap bg-green-700 hover:bg-green-800",
    'error'   => "whitespace-nowrap bg-red-700 hover:bg-red-800",
    'link'    => "text-blue-500 underline hover:no-underline",
];
```

```php
// Apply a single theme variant
$button = ComponentBuilder::make(ComponentEnum::BUTTON)
    ->setTheme('action', 'success');

// Apply multiple variant keys from the same theme file
$button = ComponentBuilder::make(ComponentEnum::BUTTON)
    ->setTheme('table', ['th', 'th-dark']);

// Apply multiple themes
$button = ComponentBuilder::make(ComponentEnum::BUTTON)
    ->setThemes([
        'action' => 'success',
        'size' => 'lg',
    ]);

// Theme classes merge into the HTML class attribute automatically
```

## Individual Components

`DivComponent` is both a utility **and** a blueprint for creating new targeted component classes that bypass the enum/builder entirely. To create a new individual component, duplicate the `DivComponent` pattern:

1. Put the class in `src/Components/Individual/`
2. Implement `BackendComponent`, `IndividualComponent`, `ThemeComponent`, `Htmlable` (omit `ContentsComponent`+`HasContent` for self-closing elements)
3. Use traits `IsBackendComponent`, `IsThemeable` (add `HasContent` only if the component can hold children)
4. Define `getName()` to return the `ComponentEnum` value (or any dotted view path)
5. Define `getComponentPath()` and `getPathOnly()` following the existing convention
6. Wire up `getAttributeBag()`, `toHtml()`, `toArray()`, and a static `make()` factory
7. Mark the class `final` (optional but recommended)

```php
use Juaniquillo\BackendComponents\Components\Individual\DivComponent;

$div = new DivComponent;
$div->setAttribute('class', 'my-class');
$div->setContent('Hello');
```

Currently only `DivComponent` exists in this category — add more as needed.

## Modal Utility

`ModalUtil` builds a complete modal component tree with Alpine.js interactivity:

```php
use Juaniquillo\BackendComponents\Utils\ModalUtil;

$modal = ModalUtil::make(
    content: 'Hello World',
    button: ComponentBuilder::make(ComponentEnum::BUTTON)
        ->setContent('Open')
        ->setAttribute('@click', 'showModal = true'),
    title: ComponentBuilder::make(ComponentEnum::H2)->setContent('Title'),
    footer: ComponentBuilder::make(ComponentEnum::DIV)->setContent('Footer'),
)
    ->setAttribute('id', 'my-modal')
    ->setTheme('modal', 'lg')
    ->getComponent();
```

The modal is composed from `DIV` components with Alpine.js attributes — no separate blade template or slots needed.

## Table Utilities

`TableUtil` builds a complete `<table>` component tree from head/body arrays. `CellBag` passes per-cell data:

```php
use Juaniquillo\BackendComponents\Utils\TableUtil;
use Juaniquillo\BackendComponents\Utils\CellBag;

$table = TableUtil::make(
    head: ['Name', 'Email', 'Role'],
    body: [
        [                         // plain values
            'Alice',
            'alice@example.com',
            'Admin',
        ],
        [                         // CellBag for per-cell control
            new CellBag(content: 'Bob', theme: ['color' => 'success']),
            'bob@example.com',
            'Editor',
        ],
    ],
)->getComponent();
```

## Livewire Components

Wrap any component as a Livewire wrapper:

```php
$livewire = ComponentBuilder::make(ComponentEnum::DIV)
    ->setLivewire()
    ->setLivewireKey('unique-key')
    ->setLivewireParams(['userId' => 1, 'team' => 'engineering']);
```

## Local Resolution

For apps consuming the package, three builders control whether components/themes resolve from the package or the app's `resources/views/`:

```php
use Juaniquillo\BackendComponents\Builders\LocalComponentBuilder;
use Juaniquillo\BackendComponents\Builders\LocalThemeComponentBuilder;

// Resolves BOTH components and themes from the app's views
$local = LocalComponentBuilder::make(ComponentEnum::BUTTON);

// Resolves THEMES from the app's views, components from the package
$localTheme = LocalThemeComponentBuilder::make(ComponentEnum::BUTTON);

// ComponentBuilder::make()->useLocal() is equivalent to LocalComponentBuilder
$component = ComponentBuilder::make(ComponentEnum::BUTTON)->useLocal();
```

| Builder | Component path | Theme path |
|---|---|---|
| `ComponentBuilder` | package `views/components/` | package `views/_themes/tailwind/` |
| `LocalComponentBuilder` | app `views/components/` | app `views/_themes/tailwind/` |
| `LocalThemeComponentBuilder` | package `views/components/` | app `views/_themes/tailwind/` |

## Serialization

Persist and restore component trees:

```php
use Juaniquillo\BackendComponents\Factories\ComponentFactory;

// Serialize
$array = $component->toArray();

// Restore
$restored = ComponentFactory::fromArray($array);
```

This works recursively for nested content, themes, settings, and Livewire state.

## Guardrails
- Do NOT build HTML strings manually — always use the component builder and enum to ensure proper rendering.
- Do NOT apply CSS classes directly in Blade templates — use the theme system instead for maintainability.
- Always use `ComponentEnum` cases rather than raw strings when possible, to benefit from IDE autocompletion and type safety.
- Do NOT use `echo` or `{!! !!}` for component output — components implement `Htmlable` so `{{ $component }}` is safe and correct.
- For self-closing HTML elements (input, img, col), ensure the Blade template uses `/>` not `></tag>`.
