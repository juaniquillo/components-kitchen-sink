---
name: svg-backend-development
description: Build SVG graphics from PHP using the juaniquillo/svg-backend-components package — compose elements via SvgComponentEnum, shape presets (SvgShape), path data (SvgPath), attributes and aliases, themes, and standalone rendering via SvgRenderer.
---

# Svg Backend Development

## When to Use This Skill

Use this skill when building SVG graphics from PHP backend code with this package:
- Compose SVG elements (`SvgBackendComponent` + `SvgComponentEnum`) with content, attributes, and nesting
- Prefer `SvgShape` presets for rect/circle/ellipse/line/polyline/polygon/path and `SvgPath` for `d` data over raw strings
- Render inline SVG anywhere Blade renders via `{{ $component }}` or `->toHtml()`, or standalone documents via `SvgRenderer`
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

Nest with `setContents()`. Element attributes (`viewBox`, `d`, `fill`, `r`, …) travel as attributes via `setAttribute()` / `setAttributes()`. Extra view settings travel via `setSetting()` / `setSettings()`. Prefer the helpers in Builders below over raw strings: `setXmlns()` / `setViewBox()` / `setSize()`, presentation aliases (`setFill()`, `setStroke()`, `setStrokeWidth()`, `setOpacity()`), `SvgShape` presets for geometry, and `SvgPath` for `d` data.

## Builders

Fluent factories when preferred over `new`:

```php
use Juaniquillo\SvgBackendComponents\Builders\SvgComponentBuilder;
use Juaniquillo\SvgBackendComponents\Builders\SvgLocalThemeComponentBuilder;
use Juaniquillo\SvgBackendComponents\SvgComponentEnum;

// Root helpers instead of raw strings:
$icon = SvgComponentBuilder::make(SvgComponentEnum::SVG)
    ->setXmlns()
    ->setViewBox(0, 0, 24, 24)
    ->setSize(24); // square; setSize(200, 100) for rectangles

// Aliases for common presentation attributes:
$rect = SvgComponentBuilder::make(SvgComponentEnum::RECT)
    ->setFill('#3b82f6')
    ->setStroke('#111827')
    ->setStrokeWidth(2)
    ->setOpacity(0.5);

// Shape presets for common geometry (same component back, fully chainable):
use Juaniquillo\SvgBackendComponents\Shapes\SvgShape;

$rect = SvgShape::rect(10, 10, 80, 50)->setFill('#3b82f6');

// Path data builder for the `d` attribute (curves stay raw until needed):
use Juaniquillo\SvgBackendComponents\Shapes\SvgPath;

$path = SvgShape::path(
    SvgPath::start(10, 100)->lineTo(60, 40)->close()
)->setFill('none');

// Resolves themes from the app's resources/views/_themes/tailwind/ instead of package defaults.
$themed = SvgLocalThemeComponentBuilder::make(SvgComponentEnum::PATH)
    ->setTheme('color', 'success');
```

## Themes

Apply Tailwind variants with `setTheme()` / `setThemes()`. Theme files live in the app's `resources/views/_themes/tailwind/` (one Blade file per group returning a variant array). See [theming in the engine docs](https://github.com/juaniquillo/backend-component-docs/blob/main/05-theming.md) for the file format:

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
- Prefer `SvgShape` presets for rect/circle/ellipse/line/polyline/polygon/path over `SvgComponentBuilder::make()` + raw geometry.
- Prefer `SvgPath` over hand-written `d` strings; use `raw()` only for curves/arcs the builder lacks.
- Prefer presentation aliases (`setFill()`, `setStroke()`, `setStrokeWidth()`, `setOpacity()`) over raw `setAttribute()` for those names.
