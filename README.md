# Laravel Backend Component Kitchen Sink

A demonstration project showcasing the **`laravel-backend-component`** package and experimenting with new ways to use its components with Livewire 4, Flux UI, Bootstrap, Slate, and SVG.

This is a Laravel 13 application for building reactive PHP interfaces without writing JavaScript. One route shows each idea in isolation; the detail lives in the docs beside this file.

## Documentation

| Section | Where | Doc |
|---|---|---|
| CRUD schemas and form patterns | `/cruds` | [docs/cruds.md](docs/cruds.md) |
| Component library: Flux, Bootstrap, Slate, SVG | `/components` | [docs/components.md](docs/components.md) |
| Design system and swappable themes | `/design-system` | [docs/design-system.md](docs/design-system.md) |
| Responsive MJML emails | `/emails` | [docs/emails.md](docs/emails.md) |
| Drag-and-drop component builder | `/builder` | [docs/builder.md](docs/builder.md) |
| Setup, testing and package integration | — | [docs/development.md](docs/development.md) |

## Quick start

```bash
composer install
npm install
npm run build

php artisan migrate --force
php artisan serve
```

Authentication uses Laravel Fortify; protected routes require the `auth` middleware. The full stack, code style, testing commands and package integration notes are in [docs/development.md](docs/development.md).

## License

MIT
