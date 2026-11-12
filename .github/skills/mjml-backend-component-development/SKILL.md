---
name: mjml-backend-component-development
description: Build responsive HTML emails in PHP with juaniquillo/mjml-backend-components, compose MJML trees via the factory and enum, configure node/v8js compiler drivers, and test with Pest.
---

# MJML Backend Component Development

## When to Use This Skill

Use this skill when the user needs to:

- Compose an MJML email layout in PHP (document, sections, text, images, buttons)
- Inspect a component tree as raw MJML or compile it to production HTML
- Add a compiler driver (custom `CompilesMjml` implementation) or switch between `node` and `v8js`
- Configure the `node` / `v8js` compiler drivers
- Test MJML components with Pest

## Creating Components

Components are created via the factory, starting with `MjmlComponentFactory::make()` or a shorthand:

```php
use Juaniquillo\MjmlBackendComponents\Factories\MjmlComponentFactory;
use Juaniquillo\MjmlBackendComponents\Enums\MjmlComponentEnum;

$email = MjmlComponentFactory::document([
    MjmlComponentFactory::head([
        MjmlComponentFactory::preview('Your weekly digest'),
    ]),
    MjmlComponentFactory::make(MjmlComponentEnum::BODY)->setContents([
        MjmlComponentFactory::section(
            [MjmlComponentFactory::text('Hello!', ['color' => '#333'])],
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
| **Head config** | `ATTRIBUTES`, `ALL`, `SELECTOR`, `STYLE`, `FONT`, `TITLE`, `PREVIEW` |
| **Special** | `MJML_CLASS` (= `class`) |

Shorthands exist for `document`, `head`, `preview`, `font`, `attributes`, `style`, `section`, and `text`. Every other case is built through the generic `MjmlComponentFactory::make()` path (do not invent new shorthand names).

## Setting Content and Attributes

Use **`setContent()`** for a single item and **`setContents()`** for multiple items; **`setAttribute()`** for one attribute and **`setAttributes()`** for a batch:

```php
$section = MjmlComponentFactory::make(MjmlComponentEnum::SECTION)
    ->setAttributes(['background-color' => '#ffffff'])
    ->setContents([
        MjmlComponentFactory::text('Hello!'),
        MjmlComponentFactory::make(MjmlComponentEnum::DIVIDER),
    ]);
```

## Rendering

- `toHtml()` renders the tree to raw MJML markup (`<mjml>`, `<mj-body>`, `<mj-section>`, …) via this package's `Htmlable` contract.
- `renderHtml()` hands that markup to the explicitly passed `CompilesMjml` driver and returns production HTML email output.

```php
use Juaniquillo\MjmlBackendComponents\Compilers\NodeProcessCompiler;

$mjml = $email->toHtml();
$html = $email->renderHtml(new NodeProcessCompiler);
```

## Compiler Drivers

Pass a `CompilesMjml` driver to `renderHtml()`. The two bundled drivers take plain config arrays:

| Driver | Class | Environment |
|---|---|---|
| `node` | `NodeProcessCompiler` | `binary` (default `npx`), `arguments` (default `['mjml', '-s']`), `timeout` (default `60.0`); runs MJML as an isolated process |
| `v8js` | `V8JsCompiler` | `mjml_js_path` (required path to the bundled `mjml.js`), in-memory via `ext-v8js` |

```php
use Juaniquillo\MjmlBackendComponents\Compilers\NodeProcessCompiler;

$html = $email->renderHtml(new NodeProcessCompiler([
    'binary' => 'npx',
    'arguments' => ['mjml', '-s'],
    'timeout' => 60.0,
]));
```

Custom drivers implement `Juaniquillo\MjmlBackendComponents\Contracts\CompilesMjml::compile(string $mjmlMarkup): string` and are passed to `renderHtml()` the same way.

`ext-v8js` is `suggest`-only and must stay that way: guard usage with `extension_loaded('v8js')` / `class_exists('V8Js')`, and never add it to `require` or `require-dev`.

## Testing

Tests are plain Pest (no framework bootstrapping):

```bash
composer test:unit     # Pest suite
composer analyse       # PHPStan level 7
composer lint:check    # Pint check mode
composer rector:check  # Rector dry run
composer qa            # all of the above (qa:ci is the CI variant running pest --ci)
```

When testing compilation without the MJML CLI, construct the `node` driver with the PHP binary echoing stdin instead of asserting against real `npx` output.
