---
name: svg-backend-development
description: Build SVG graphics from PHP using the juaniquillo/svg-backend-components package — compose elements via SvgComponentEnum, nest them with setContents, pass attributes with setAttribute, and apply Tailwind themes.
---

# Svg Backend Development

## When to Use This Skill

Use this skill when building SVG graphics from PHP backend code with this package:
- Compose SVG elements (`SvgBackendComponent` + `SvgComponentEnum`) with content, attributes, and nesting
- Render inline SVG anywhere Blade renders via `{{ $component }}` or `->toHtml()`
- Resolve themes from the consuming app's local views

For engine mechanics (serialization, Blade pipeline, helpers), see the `backend-component` skill.

## Components

Pick an element case from `SvgComponentEnum` and render via `{{ $component }}` (components are `Htmlable`) or `->toHtml()`:

```php
use Juaniquillo\SvgBackendComponents\SvgBackendComponent;
use Juaniquillo\SvgBackendComponents\SvgComponentEnum;

$icon = (new SvgBackendComponent(SvgComponentEnum::SVG))
    ->setAttributes(['viewBox' => '0 0 24 24', 'fill' => 'none'])
    ->setContents([
        (new SvgBackendComponent(SvgComponentEnum::PATH))
            ->setAttribute('d', 'M0 0h24v24H0z'),
    ]);
```

```blade
{{ $icon }}
```

Nest with `setContents()`. Element attributes (`viewBox`, `d`, `fill`, `r`, …) travel as attributes via `setAttribute()` / `setAttributes()`. Extra view settings travel via `setSetting()` / `setSettings()`.

## Builders

Fluent factories when preferred over `new`:

```php
use Juaniquillo\SvgBackendComponents\Builders\SvgComponentBuilder;
use Juaniquillo\SvgBackendComponents\Builders\SvgLocalThemeComponentBuilder;
use Juaniquillo\SvgBackendComponents\SvgComponentEnum;

// Root shorthand: xmlns is set for you.
$icon = SvgComponentBuilder::svg($contents, ['viewBox' => '0 0 24 24']);

// Viewport helpers instead of raw strings:
$icon = SvgComponentBuilder::make(SvgComponentEnum::SVG)
    ->setViewBox(0, 0, 24, 24)
    ->setSize(24); // square; setSize(200, 100) for rectangles

// Resolves themes from the app's resources/views/_themes/tailwind/ instead of package defaults.
$themed = SvgLocalThemeComponentBuilder::make(SvgComponentEnum::PATH)
    ->setTheme('color', 'success');
```

## Themes

Apply Tailwind variants with `setTheme()` / `setThemes()`. Theme files live in the app's `resources/views/_themes/tailwind/` (one Blade file per group returning a variant array):

```php
$path = SvgComponentBuilder::make(SvgComponentEnum::PATH)
    ->setTheme('color', 'success');
```

## Rendering

Turn any component into a standalone SVG document string, or write it straight to disk:

```php
use Juaniquillo\SvgBackendComponents\SvgRenderer;

$svg = SvgRenderer::make($component)->toSvg();

SvgRenderer::make($component)->saveTo('path/to/icon.svg');
```

## Guardrails

- Always use `SvgComponentEnum` cases, never raw element name strings.
- Attributes are scalar (`setAttribute()` / `setAttributes()`); nested elements go through `setContent()` / `setContents()`.
