---
name: flux-backend-development
description: Build Flux UI components from PHP using the juaniquillo/flux-backend-components package — compose components via FluxComponentEnum, render standalone icons with FluxIconEnum, build data tables with FluxUITableUtil, pass rich values with setProp, and apply Tailwind themes. Free Flux edition only.
---

# Flux Backend Development

## When to Use This Skill

Use this skill when building Flux UI from PHP backend code with this package:
- Compose Flux components (`FluxBackendComponent` + `FluxComponentEnum`) with content, attributes, and nesting
- Render standalone icons via `FluxIconEnum`
- Build data tables programmatically with `FluxUITableUtil` and `CellBag`
- Pass rich (non-scalar) values with `setProp()` / `setProps()`
- Resolve themes from the consuming app's local views

For engine mechanics (serialization, Blade pipeline, helpers), see the `backend-component` skill. For available component props (`variant`, `size`, `sortable`, …), see the [Flux documentation](https://fluxui.dev).

## Components

Pick a case from `FluxComponentEnum` (free Flux set only — no PRO components) and render via `{{ $component }}` (components are `Htmlable`) or `->toHtml()`:

```php
use Juaniquillo\FluxBackendComponents\FluxBackendComponent;
use Juaniquillo\FluxBackendComponents\FluxComponentEnum;

$button = (new FluxBackendComponent(FluxComponentEnum::BUTTON))
    ->setContent('Save changes')
    ->setAttributes(['id' => 'save-btn', 'variant' => 'primary']);
```

```blade
{{ $button }}
```

Nest with `setContents()`:

```php
$card = (new FluxBackendComponent(FluxComponentEnum::CARD))
    ->setContents([
        (new FluxBackendComponent(FluxComponentEnum::HEADING))->setContent('Recent customers'),
        (new FluxBackendComponent(FluxComponentEnum::TEXT))->setContent('Your latest customer activity.'),
    ]);
```

## Builders

Fluent factories when preferred over `new`:

```php
use Juaniquillo\FluxBackendComponents\Builders\FluxComponentBuilder;
use Juaniquillo\FluxBackendComponents\Builders\FluxLocalThemeComponentBuilder;
use Juaniquillo\FluxBackendComponents\FluxComponentEnum;

$button = FluxComponentBuilder::make(FluxComponentEnum::BUTTON)
    ->setContent('Save changes');

// Resolves themes from the app's resources/views/_themes/tailwind/ instead of package defaults.
$themed = FluxLocalThemeComponentBuilder::make(FluxComponentEnum::BUTTON)
    ->setTheme('color', 'success');
```

## Icons

Every icon bundled with Flux is a `FluxIconEnum` case — the equivalent of `<flux:icon.bolt />`:

```php
use Juaniquillo\FluxBackendComponents\FluxBackendComponent;
use Juaniquillo\FluxBackendComponents\FluxIconEnum;

$icon = new FluxBackendComponent(FluxIconEnum::BOLT);

// Variant: outline (default), solid, mini, or micro.
$icon->setAttribute('variant', 'solid');
```

For the dynamic form (`<flux:icon name="…">`), use `FluxComponentEnum::ICON` with the icon name as an attribute.

## Tables

`FluxUITableUtil` builds a complete `<flux:table>` from head/body arrays. Cells accept plain values, component instances, `CellBag` objects, or `['content' => …, 'theme' => …, 'attributes' => …]` arrays:

```php
use Juaniquillo\BackendComponents\Utils\CellBag;
use Juaniquillo\FluxBackendComponents\FluxBackendComponent;
use Juaniquillo\FluxBackendComponents\FluxComponentEnum;
use Juaniquillo\FluxBackendComponents\Utils\FluxUITableUtil;

$table = FluxUITableUtil::make(
    head: ['Customer', 'Status'],
    body: [
        [
            'Lindsey Aminoff',
            new CellBag(
                content: (new FluxBackendComponent(FluxComponentEnum::BADGE))->setContent('Paid'),
                attributes: ['variant' => 'strong'],
            ),
        ],
    ],
)
    ->setTableAttributes(['bleed' => true])
    ->setColumnsAttributes(['sticky' => true])
    ->getComponent();
```

Per-section themes: `setTableThemes()`, `setThThemes()`, `setTrThemes()`, `setTdThemes()`. Columns go directly inside `THEAD` — it renders its own header row, so never wrap them in a `TR`.

## Props

Scalar values travel as attributes. Anything richer — booleans, arrays, collections, paginators — travels as props:

```php
$table = (new FluxBackendComponent(FluxComponentEnum::TABLE))
    ->setAttribute('id', 'orders')   // scalar attribute
    ->setProp('bleed', true)         // rich prop
    ->setProps(['rows' => $orders]); // …or several at once
```

Props merge into the rendered output (winning over attributes on collision) but stay out of `toArray()`, keeping exports JSON-safe and round-trippable.

## Themes

Apply Tailwind variants with `setTheme()` / `setThemes()`. Theme files live in the app's `resources/views/_themes/tailwind/` (one Blade file per group returning a variant array):

```php
$button = FluxComponentBuilder::make(FluxComponentEnum::BUTTON)
    ->setTheme('color', 'success');
```

## Guardrails

- Free Flux components only — the enum carries no PRO cases, never invent component names.
- Always use `FluxComponentEnum` / `FluxIconEnum` cases, never raw component name strings.
- Rich values go through `setProp()` / `setProps()` — never `setAttribute()`, which is scalar-only by contract.
- Columns go directly inside `THEAD`.
