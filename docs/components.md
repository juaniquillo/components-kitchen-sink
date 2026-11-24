# Component Library

[← Back to the README](../README.md) · [CRUD schemas](cruds.md) · [Design system](design-system.md)

Components are organized into groups registered in `App\Components\RouteCollectionGroup`, each served at `/components/{group}`. The index at `/components` links to every group, and each group page (e.g. `/components/slate`) renders the full variant list for that group.

| Route Group | Component (variants) |
|---|---|
| **Backend Components** | Alpine JS Modals (4), Backend Tables (2), Backend Details (4) |
| **Flux UI** | Modals (3), Buttons (6), Skeletons (5), Cards (6), Tables (7), Inputs (10) |
| **Bootstrap** | Button (5), Badge (9), Card (2), Carousel (3), Tables (7) |
| **Slate** | Slate Dialogs (2), Slate Tabs (1), Slate Drop Down Menus (1), Slate Buttons (9), Slate Carousel (2), Slate Tables (1) |
| **SVG** | Basic Shapes (1), Paths and Polygons (3), Paint and Patterns (4), Reuse and Text (3), Media (1), Themed Icons (4), Brand Icons (4) |

The design system has its own gallery at `/design-system`; see [design-system.md](design-system.md).

## Third-Party Slate Components

The Slate group is built with `juaniquillo/slate-backend-components`, which adapts the `BackendComponent` contract to render Slate's anonymous Blade components. Components are composed with the `SlateComponentEnum` (over 200 cases) and `SlateComponentFactory`, using helpers like `SlateOverlayUtil` (dialogs), `SlateTabsUtil` (tabs), and `SlateUITableUtil` (tables). Each `SlateBackendComponent` resolves through `backend-component::_utilities.resolve-third-party-component`, which delegates to `<x-dynamic-component>`. The Slate group also registers its own stylesheet (`resources/css/slate.css`) as a per-group asset.

## Third-Party SVG Components

The SVG group is built with `juaniquillo/svg-backend-components`, which adapts the `BackendComponent` contract to SVG elements. Components are composed with the `SvgComponentEnum` (`SVG`, `G`, `PATH`, `CIRCLE`, …) and `SvgComponentFactory`, with roots built fluently via `make(SvgComponentEnum::SVG)->setXmlns()->setViewBox()->setSize()->setContents()`. Geometry uses `SvgShape` presets (`rect`, `circle`, `ellipse`, `line`, `polyline`, `polygon`, `path`), path data uses the `SvgPath` builder (`start()->lineTo()->close()`, `curveTo()`, `quadraticTo()`, `arcTo()`, with `raw()` as the escape hatch), and paint uses the aliases `setFill()` / `setStroke()` / `setStrokeWidth()` / `setOpacity()`. Each `SvgBackendComponent` renders through the `svg::` view context, or standalone via `SvgRenderer::make($component)->toSvg()` (which can add the XML prolog and backfills `xmlns` when saving). The Themed Icons box uses `SvgLocalThemeComponentFactory` so the paint comes from the app's own `fill` theme group (`fill-indigo-600 dark:fill-indigo-300`, …) instead of hardcoded fills. The caption in Reuse and Text does the same through the `muted` / `accent` variants of that group, so no Tailwind classes are written in PHP for SVG paint. Brand marks are single 24x24 paths from Simple Icons fed through `SvgShape::path()`; the mono marks (GitHub, X) use `fill="currentColor"` plus the app's `color` theme group so they flip with light and dark mode, while Facebook keeps its brand colour and Instagram fills its path with a `linearGradient` defined in `defs`.
