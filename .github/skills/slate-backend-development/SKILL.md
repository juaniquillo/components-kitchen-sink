---
name: slate-backend-development
description: Build Slate UI components from PHP using the juaniquillo/slate-backend-components package — compose components via SlateComponentEnum, build overlays with SlateOverlayUtil, accordions and tabs with SlateAccordionUtil and SlateTabsUtil, build data tables with SlateUITableUtil, pass rich values with setProp, and apply Tailwind themes.
---

# Slate Backend Development

## When to Use This Skill

Use this skill when building Slate UI from PHP backend code with this package:
- Compose Slate components (`SlateBackendComponent` + `SlateComponentEnum`) with content, attributes, and nesting
- Build overlays (dialog, alert-dialog, sheet, drawer) with `SlateOverlayUtil`
- Build accordions and tabs programmatically with `SlateAccordionUtil` and `SlateTabsUtil`
- Build data tables programmatically with `SlateUITableUtil` and `CellBag`
- Pass rich (non-scalar) values with `setProp()` / `setProps()`
- Resolve themes from the consuming app's local views

For engine mechanics (serialization, Blade pipeline, helpers), see the `backend-component` skill. For available component props (`variant`, `loading`, `showError`, …), see the [Slate components documentation](https://slate.electrik.dev/components).

## Components

Pick a case from `SlateComponentEnum` (206 cases mirroring every Slate Blade component) and render via `{{ $component }}` (components are `Htmlable`) or `->toHtml()`:

```php
use Juaniquillo\SlateBackendComponents\SlateBackendComponent;
use Juaniquillo\SlateBackendComponents\SlateComponentEnum;

$button = (new SlateBackendComponent(SlateComponentEnum::BUTTON))
    ->setContent('Save changes')
    ->setAttributes(['id' => 'save-btn', 'variant' => 'primary']);
```

```blade
{{ $button }}
```

Nest with `setContents()`:

```php
$card = (new SlateBackendComponent(SlateComponentEnum::CARD))
    ->setContents([
        (new SlateBackendComponent(SlateComponentEnum::CARD_HEADER))->setContent('Account'),
        (new SlateBackendComponent(SlateComponentEnum::CARD_CONTENT))->setContent('Manage your workspace settings.'),
    ]);
```

## Factories

Fluent factories when preferred over `new`:

```php
use Juaniquillo\SlateBackendComponents\Factories\SlateComponentFactory;
use Juaniquillo\SlateBackendComponents\Factories\SlateLocalThemeComponentFactory;
use Juaniquillo\SlateBackendComponents\SlateComponentEnum;

$button = SlateComponentFactory::make(SlateComponentEnum::BUTTON)
    ->setContent('Save changes');

// Resolves themes from the app's resources/views/_themes/tailwind/ instead of package defaults.
$themed = SlateLocalThemeComponentFactory::make(SlateComponentEnum::BUTTON)
    ->setTheme('color', 'success');
```

## Overlays

`SlateOverlayUtil` builds a complete dialog, alert-dialog, sheet, or drawer tree — trigger, content, header (title + description), and footer. The root must be one of `DIALOG`, `ALERT_DIALOG`, `SHEET`, or `DRAWER`; anything else throws:

```php
use Juaniquillo\SlateBackendComponents\SlateBackendComponent;
use Juaniquillo\SlateBackendComponents\SlateComponentEnum;
use Juaniquillo\SlateBackendComponents\Utils\SlateOverlayUtil;

$dialog = SlateOverlayUtil::make(
    root: SlateComponentEnum::DIALOG,
    content: 'Edit your profile details below.',
    trigger: (new SlateBackendComponent(SlateComponentEnum::BUTTON))->setContent('Edit profile'),
    title: 'Edit profile',
    description: 'Make changes to your profile here.',
    footer: (new SlateBackendComponent(SlateComponentEnum::BUTTON))->setContent('Save changes'),
)
    ->setShowCloseButton(false)
    ->getComponent();
```

For alert dialogs, pass the action/cancel buttons as a footer array (`setShowCloseButton()` is ignored there — alert dialogs have no close button):

```php
$confirm = SlateOverlayUtil::make(
    root: SlateComponentEnum::ALERT_DIALOG,
    content: 'This action cannot be undone.',
    trigger: 'Delete account',
    title: 'Are you sure?',
    footer: [$deleteButton, $cancelButton],
)->getComponent();
```

## Accordions and Tabs

`SlateAccordionUtil` and `SlateTabsUtil` build item trees from keyed arrays. Keys become the item values the Alpine state tracks, so triggers and panels stay wired without hand-written value props:

```php
use Juaniquillo\SlateBackendComponents\Utils\SlateAccordionUtil;
use Juaniquillo\SlateBackendComponents\Utils\SlateTabsUtil;

$accordion = SlateAccordionUtil::make(
    items: [
        'item-1' => ['title' => 'First', 'content' => 'First body'],
        'item-2' => ['title' => 'Second', 'content' => 'Second body'],
    ],
    defaultValue: 'item-1',
)->getComponent();

$tabs = SlateTabsUtil::make(
    tabs: [
        'overview' => ['label' => 'Overview', 'content' => 'Overview body'],
        'settings' => ['label' => 'Settings', 'content' => 'Settings body'],
    ],
    defaultValue: 'overview',
)->getComponent();
```

## Tables

`SlateUITableUtil` builds a complete `<x-slate::table>` from head/body arrays. Cells accept plain values, component instances, `CellBag` objects, or `['content' => …, 'theme' => …, 'attributes' => …]` arrays:

```php
use Juaniquillo\BackendComponents\Utils\CellBag;
use Juaniquillo\SlateBackendComponents\SlateBackendComponent;
use Juaniquillo\SlateBackendComponents\SlateComponentEnum;
use Juaniquillo\SlateBackendComponents\Utils\SlateUITableUtil;

$table = SlateUITableUtil::make(
    head: ['Customer', 'Status'],
    body: [
        [
            'Lindsey Aminoff',
            new CellBag(
                content: (new SlateBackendComponent(SlateComponentEnum::BADGE))->setContent('Paid'),
                attributes: ['variant' => 'strong'],
            ),
        ],
    ],
)
    ->setTableAttributes(['id' => 'orders'])
    ->getComponent();
```

Per-section themes: `setTableThemes()`, `setThThemes()`, `setTrThemes()`, `setTdThemes()`. Unlike Flux, Slate's `table-header` is a plain `<thead>` — the util wraps header columns in a `table-row` of `table-head` cells, so never add that wrapper yourself.

## Props

Scalar values travel as attributes. Anything richer — booleans, arrays, objects — travels as props:

```php
$button = (new SlateBackendComponent(SlateComponentEnum::BUTTON))
    ->setAttribute('id', 'save-btn') // scalar attribute
    ->setProp('loading', true)       // rich prop (renders aria-busy)
    ->setProps(['rows' => $orders]); // …or several at once
```

Props merge into the rendered output (winning over attributes on collision) but stay out of `toArray()`, keeping exports JSON-safe and round-trippable.

## Themes

Apply Tailwind variants with `setTheme()` / `setThemes()`. Theme files live in the app's `resources/views/_themes/tailwind/` (one Blade file per group returning a variant array), resolved through the local theme factories. Slate's own styling ships with its CSS tokens — base themes only affect variants you set explicitly:

```php
$button = SlateComponentFactory::make(SlateComponentEnum::BUTTON)
    ->setTheme('color', 'success');
```

## Guardrails

- Always use `SlateComponentEnum` cases (206 available), never raw component name strings.
- `SlateOverlayUtil` roots are `DIALOG`, `ALERT_DIALOG`, `SHEET`, and `DRAWER` only.
- Rich values go through `setProp()` / `setProps()` — never `setAttribute()`, which is scalar-only by contract.
- Header columns belong inside the util-built `table-row` — never wrap them yourself.
