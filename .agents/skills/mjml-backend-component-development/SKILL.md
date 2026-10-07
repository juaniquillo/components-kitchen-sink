---
name: mjml-backend-component-development
description: Build responsive HTML emails in PHP with juaniquillo/mjml-backend-components, compose MJML trees via the builder and enum, configure node/v8js compiler drivers, and test with Pest.
---

# MJML Backend Component Development

## When to Use This Skill

Use this skill when the user needs to:

- Compose an MJML email layout in PHP (document, sections, text, images, buttons)
- Inspect a component tree as raw MJML or compile it to production HTML
- Add a compiler driver (custom `CompilesMjml` implementation) or switch between `node` and `v8js`
- Configure or publish the `mjml-backend-components` config
- Test MJML components with Pest

## Creating Components

Components are created via the builder, starting with `MjmlComponentBuilder::make()` or a shorthand:

```php
use Juaniquillo\MjmlBackendComponents\Builders\MjmlComponentBuilder;
use Juaniquillo\MjmlBackendComponents\Enums\MjmlComponentEnum;

$email = MjmlComponentBuilder::document([
    MjmlComponentBuilder::head([
        MjmlComponentBuilder::preview('Your weekly digest'),
    ]),
    MjmlComponentBuilder::make(MjmlComponentEnum::BODY)->setContents([
        MjmlComponentBuilder::section(
            [MjmlComponentBuilder::text('Hello!', ['color' => '#333'])],
            ['background-color' => '#ffffff']
        ),
    ]),
]);
```

### All available enum cases

| Category | Cases |
|---|---|
| **Document** | `MJML`, `HEAD`, `BODY` |
| **Layout** | `SECTION`, `COLUMN`, `GROUP`, `HERO`, `SPACER` |
| **Content** | `TEXT`, `IMAGE`, `BUTTON`, `DIVIDER`, `RAW` |
| **Head config** | `ATTRIBUTES`, `STYLE`, `FONT`, `TITLE`, `PREVIEW` |

Shorthands exist for `document`, `head`, `preview`, `font`, `attributes`, `style`, `section`, and `text`. Every other case is built through the generic `MjmlComponentBuilder::make()` path — do not invent new shorthand names.

## Setting Content and Attributes

Use **`setContent()`** for a single item and **`setContents()`** for multiple items; **`setAttribute()`** for one attribute and **`setAttributes()`** for a batch:

```php
$section = MjmlComponentBuilder::make(MjmlComponentEnum::SECTION)
    ->setAttributes(['background-color' => '#ffffff'])
    ->setContents([
        MjmlComponentBuilder::text('Hello!'),
        MjmlComponentBuilder::make(MjmlComponentEnum::DIVIDER),
    ]);
```

## Rendering

- `toHtml()` renders the tree to raw MJML markup (`<mjml>`, `<mj-body>`, `<mj-section>`, …). The component implements Laravel's `Htmlable`.
- `renderHtml()` hands that markup to the bound `CompilesMjml` driver and returns production HTML email output.

```php
$mjml = $email->toHtml();
$html = $email->renderHtml();
```

## Compiler Drivers

`config/mjml-backend-components.php` selects the driver via the `default` key (`MJML_COMPILER` env, defaults to `node`):

| Driver | Class | Environment |
|---|---|---|
| `node` | `NodeProcessCompiler` | `MJML_NODE_BINARY` (default `npx`), runs `mjml -s` as an isolated process |
| `v8js` | `V8JsCompiler` | `MJML_JS_PATH` (bundled `mjml.js`), in-memory via `ext-v8js` |

Publish with `php artisan vendor:publish --tag=mjml-backend-components-config`. Custom drivers implement `Juaniquillo\MjmlBackendComponents\Contracts\CompilesMjml::compile(string $mjmlMarkup): string` and are registered as another key under `drivers`.

`ext-v8js` is `suggest`-only and must stay that way: guard usage with `extension_loaded('v8js')` / `class_exists('V8Js')`, and never add it to `require` or `require-dev`.

## Testing

Tests are Pest-based and boot through `tests/TestCase.php` (Orchestra Testbench with the base backend-component provider registered first — keep that order so helpers like `isComponent()` exist):

```bash
composer test:unit     # Pest suite
composer analyse       # PHPStan level 7
composer lint:check    # Pint check mode
composer rector:check  # Rector dry run
composer qa            # all of the above (qa:ci is the CI variant running pest --ci)
```

When testing compilation without the MJML CLI, override the `node` driver config to echo stdin through the PHP binary instead of asserting against real `npx` output.
