# Development

[← Back to the README](../README.md)

## Stack

This is a Laravel 13 kitchen sink application that demonstrates various patterns for building reactive PHP interfaces using:

- **Livewire 4** - Reactive PHP interfaces without writing JavaScript
- **livewire/flux** - Flux UI components for Livewire
- **electrik/slate** - Slate component library (Tailwind + Alpine)
- **Bootstrap** - Classic Bootstrap styling
- **Tailwind CSS v4** - Styling
- **juaniquillo/laravel-backend-component** - Backend component system
- **juaniquillo/backend-design-system** - Design system with swappable Tailwind themes
- **juaniquillo/flux-backend-components** - Flux component builder, enum, and backend component
- **juaniquillo/svg-backend-components** - SVG builder, enum, and backend component
- **juaniquillo/mjml-backend-components** - MJML email builder, enum, and compiler drivers
- **juaniquillo/crud-assistant** - CRUD form factories and input collections
- **juaniquillo/input-component-action** - Input value/validation processing

## Installation

```bash
# Install dependencies
composer install
npm install
npm run build

# Run migrations
php artisan migrate --force

# Serve the application
php artisan serve
```

## Authentication

This project uses Laravel Fortify for authentication. Protected routes require `auth` middleware.

Run `php artisan fortify:setup` to configure auth endpoints if needed.

## Code style

Run Pint to format code:

```bash
vendor/bin/pint --format agent
```

## Testing

Run the test suite:

```bash
php artisan test --compact
```

Or run it in parallel, the way the full suite is checked before a change is finished:

```bash
php artisan test --parallel --compact
```

Pest itself accepts a path and a filter:

```bash
vendor/bin/pest tests/Feature/Builder --compact
vendor/bin/pest --filter="undoes a change"
```

## Package integration

This project experiments with the `juaniquillo/laravel-backend-component` package. Key integration points:

- **`BackendComponent` interface** - Implemented by `FluxBackendComponent` (from `juaniquillo/flux-backend-components`), `SlateBackendComponent` (from `juaniquillo/slate-backend-components`), and `SvgBackendComponent` (from `juaniquillo/svg-backend-components`)
- **`ComponentFactory`** - Fluent factory for creating component trees
- **`ComponentEnum`** - Enumeration of all available component types
- **Theme system** - Default theme manager with configurable themes, plus app-level Tailwind theme files
- **Content system** - HasContent concern for managing component content
- **`Component` contract** - Each group implements `App\Components\Contracts\Component` with `list()` and `options()` returning a `GroupOptions` DTO: `ContainerOptions` for flex on/off, column, gap, justification and cross-axis alignment, and `BoxOptions` for the outer box's padding and overflow
- **`RouteCollectionGroup`** - Central registry mapping each route group to its components and static assets
- **`ComponentCollection`** - Collection that aggregates group components for rendering
- **`MjmlComponentFactory`** - Fluent factory for MJML trees; shorthands exist for `document`, `head`, `preview`, `font`, `attributes`, `style`, `section`, and `text`, every other `MjmlComponentEnum` case goes through generic `make()`
- **`toHtml()` vs `renderHtml()`** - `toHtml()` returns raw MJML markup; `renderHtml()` compiles it to production HTML via the explicitly passed `CompilesMjml` driver (e.g. `new NodeProcessCompiler`)

### Custom component development

To create new backend components:

1. Implement `Juaniquillo\BaseBackendComponent\Contracts\BackendComponent`
2. Use concerns like `HasContent`, `IsThemeable`, `HasPath` as needed
3. Implement `getContext()`, `getName()`, `getAttributeBag()`, `toArray()`, `toHtml()`
4. Register the component in the appropriate CRUD or component group

Existing adapters serve as reference implementations:

- `FluxBackendComponent` (from `juaniquillo/flux-backend-components`) - Flux-styled backend components using `FluxComponentEnum` and `FluxComponentFactory`
- `SlateBackendComponent` (from `juaniquillo/slate-backend-components`) - Resolves `SlateComponentEnum` cases to `slate::*` views through `x-dynamic-component`
- `SvgBackendComponent` (from `juaniquillo/svg-backend-components`) - SVG elements from `SvgComponentEnum` composed with `SvgComponentFactory`, rendered through the `svg::` view context
