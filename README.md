# Laravel Backend Component Kitchen Sink

A demonstration project showcasing the **`laravel-backend-component`** package and experimenting with new ways to use its components with Livewire 4 and Flux UI.

## Project Overview

This is a Laravel 13 kitchen sink application that demonstrates various patterns for building reactive PHP interfaces using:

- **Livewire 4** - Reactive PHP interfaces without writing JavaScript
- **livewire/flux** - Flux UI components for Livewire
- **Tailwind CSS v4** - Styling
- **juaniquillo/laravel-backend-component** - Backend component system

## Features Showcase

### CRUD Schema Types

Four different CRUD implementations demonstrating various theming approaches:

| Identifier | Name | Description |
|---|---|---|
| `unstyled` | Unstyled Crud | Basic form without special theming |
| `simple_crud` | Simple Themed Crud | Themed form with labels, inputs, and avatar |
| `flux` | Flux Crud | Flux-styled form using `FluxBackendComponent` |
| `input_group` | Input Group WC | Form with label/input groups using custom `LabelInputGroup` |

### Component Library

#### Main Package Modals/Dialoags

- **Default modal** - Basic modal with content and simple button
- **Basic modal** - Divided content area with "Simple Modal" button
- **Modal with header and footer** - Complete modal structure with header/footer buttons
- **Dialog HTML Tag** - Using native `<dialog>` element with command pattern

#### Livewire Flux Modals

- **Modal Default** - Trigger button and modal with padding theme
- **Modal Confirm** - Danger-themed confirm deletion modal
- **Modal Flyout** - Side panel flyout navigation

#### Third-Party Flux Components

- `FluxBackendComponent` - Custom backend component implementing `BackendComponent`, `ContentComponent`, `Htmlable`, `PathComponent`, and `ThemeComponent` interfaces
- `FluxComponentEnum` - Comprehensive enum of Flux component types (card, heading, text, button, badge, link, icon, separator, spacer, form fields, tables, nav, modal, tooltip)

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

- **`BackendComponent` interface** - Implemented by `FluxBackendComponent` 
- **`ComponentBuilder`** - Fluent builder for creating component trees
- **`ComponentEnum`** - Enumeration of all available component types
- **Theme system** - Default theme manager with configurable themes
- **Content system** - HasContent concern for managing component content

### Custom Component Development

To create new backend components:

1. Implement `Juaniquillo\BackendComponents\Contracts\BackendComponent`
2. Use concerns like `HasContent`, `IsThemeable`, `HasPath` as needed
3. Implement `getContext()`, `getName()`, `getAttributeBag()`, `toArray()`, `toHtml()`
4. Register the component in the appropriate CRUD or component group

## License

MIT