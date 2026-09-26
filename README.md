# Laravel Backend Component Kitchen Sink

A demonstration project showcasing the **`laravel-backend-component`** package and experimenting with new ways to use its components with Livewire 4, Flux UI, Bootstrap, and Slate.

## Project Overview

This is a Laravel 13 kitchen sink application that demonstrates various patterns for building reactive PHP interfaces using:

- **Livewire 4** - Reactive PHP interfaces without writing JavaScript
- **livewire/flux** - Flux UI components for Livewire
- **electrik/slate** - Slate component library (Tailwind + Alpine)
- **Bootstrap** - Classic Bootstrap styling
- **Tailwind CSS v4** - Styling
- **juaniquillo/laravel-backend-component** - Backend component system
- **juaniquillo/flux-backend-components** - Flux component builder, enum, and backend component
- **juaniquillo/crud-assistant** - CRUD form factories and input collections
- **juaniquillo/input-component-action** - Input value/validation processing

## Features Showcase

### CRUD Schema Types

Four different CRUD implementations demonstrating various theming approaches:

| Identifier | Name | Description |
|---|---|---|
| `unstyled` | Unstyled Crud | Basic form without special theming |
| `simple_crud` | Simple Themed Crud | Themed form with labels, inputs, and avatar |
| `flux` | Flux Crud | Flux-styled form built with the `flux-backend-components` package |
| `input_group` | Input Group WC | Form with label/input groups using custom `LabelInputGroup` and `FormGroupWebComponentBuilder` |

### Component Library

Components are organized into groups registered in `App\Components\RouteCollectionGroup`, each served at `/components/{group}`:

| Route Group | Component (variants) |
|---|---|
| **Backend Components** | Alpine JS Modals — Default modal (1), Basic modal (1), Modal with header and footer (1), Dialog HTML Tag (1) |
| **Flux UI** | Modals (3), Buttons (6), Skeletons (5), Cards (6), Tables (7), Inputs (10) |
| **Bootstrap** | Button (5), Badge (9), Card (2), Carousel (3), Tables (7) |
| **Slate** | Dialog (2), Tabs (1), Drop Down Menus (1), Buttons (9), Carousel (2), Tables (1) |

#### Third-Party Slate Components

`App\Components\ThirdParty\Slate\SlateBackendComponent` adapts the package's `BackendComponent` contract to render Slate's anonymous Blade components. It hardcodes the `slate::` view context and resolves each component through `backend-component::_utilities.resolve-third-party-component`, which delegates to `<x-dynamic-component>`. The Slate group also registers its own stylesheet (`resources/css/slate.css`) as a per-group asset.

### Form Patterns

Each CRUD follows a consistent pattern:

1. **Factories** define input fields (name, email, etc.)
2. **`CrudAssistant::make()`** creates an `InputCollection`
3. **`InputComponentAction`** processes values and errors
4. **`ComponentBuilder`** constructs the final HTML component
5. **Form submits to `/cruds`** with `identifier` input

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

## Usage

### Accessing CRUDs

Visit `/cruds` to see all available CRUD types. Each card links to its respective form.

### Exploring Components

Visit `/components` for links to every registered component group. Each group page (e.g. `/components/slate`) renders the full variant list for that group: Backend Components, Flux UI, Bootstrap, and Slate.

### Authentication

This project uses Laravel Fortify for authentication. Protected routes require `auth` middleware.

Run `php artisan fortify:setup` to configure auth endpoints if needed.

## Development

### Code Style

Run Pint to format code:

```bash
vendor/bin/pint --format agent
```

### Testing

Run the test suite:

```bash
php artisan test --compact
```

Or with Pest:

```bash
php artisan pest --compact
```

## Package Integration

This project experiments with the `juaniquillo/laravel-backend-component` package. Key integration points:

- **`BackendComponent` interface** - Implemented by `FluxBackendComponent` (from `juaniquillo/flux-backend-components`) and `SlateBackendComponent`
- **`ComponentBuilder`** - Fluent builder for creating component trees
- **`ComponentEnum`** - Enumeration of all available component types
- **Theme system** - Default theme manager with configurable themes, plus app-level Tailwind theme files
- **Content system** - HasContent concern for managing component content
- **`Component` contract** - Each group implements `App\Components\Contracts\Component` with `list()` and `options()` returning a `ContainerOptions` DTO (flex/column/gap layout settings)
- **`RouteCollectionGroup`** - Central registry mapping each route group to its components and static assets
- **`ComponentCollection`** - Collection that aggregates group components for rendering

### Custom Component Development

To create new backend components:

1. Implement `Juaniquillo\BackendComponents\Contracts\BackendComponent`
2. Use concerns like `HasContent`, `IsThemeable`, `HasPath` as needed
3. Implement `getContext()`, `getName()`, `getAttributeBag()`, `toArray()`, `toHtml()`
4. Register the component in the appropriate CRUD or component group

Existing adapters serve as reference implementations:

- `FluxBackendComponent` (from `juaniquillo/flux-backend-components`) - Flux-styled backend components using `FluxComponentEnum` and `FluxComponentBuilder`
- `SlateBackendComponent` - Third-party resolver that maps component names to `slate::*` views through `x-dynamic-component`

## License

MIT