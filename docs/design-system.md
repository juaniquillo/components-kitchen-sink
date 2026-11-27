# Design System

[← Back to the README](../README.md) · [Component library](components.md)

A small design system is showcased at `/design-system`, separate from the component kitchen sink. It is a package, `juaniquillo/backend-design-system` (0.4.0, installed from Packagist). The package owns the `Design` value object, the theme managers, the components, the key enums and three bundled designs — `base` (light only), `base-dark` (dark only) and `base-dual` (adaptive). On top of them this app owns two designs of its own, both adaptive and scaffolded with `design-system:make-theme`: `soft`, an indigo-and-rounded look, and `classic`, a slate dashboard look with navy actions — both kept under `resources/views/backend-design-system/`, so the gallery compares the reference look with real application themes. `?design=` switches looks (unknown keys 404), so each design gets a linkable page.

A design is a key, a label, a dark mode and a theme manager, and the manager owns the folder — which is what lets a bundled design read from the package while an application design reads from `resources/views`. The provider binds one `Design` from `backend-design-system.default` and throws on an unknown key rather than rendering the wrong look. An explicit `Design` argument wins per call. The gallery needs a design other than the default, so its route validates the key, sets the config default and forgets the resolved binding before resolving it again.

Theme groups follow two rules: a **look** variant is a complete appearance, while a **state** variant (`disabled`, `loading`, `current`) carries only the affordance and is appended by the component — so a theme never needs a `primary-disabled` for every combination, and the package fails a state variant that carries colors. Sizes live in the component's own file as `size.*` keys, so a look can never collide with a size name. Three shared scales keep spacing, type and corners tunable per design: `space`, `text` and `radius`.

The package ships twenty-four components — `Button`, `Badge`, `Card`, `Alert`, `Input`, `Textarea`, `Select`, `Checkbox`, `Radio`, `Field`, `Label`, `Form`, `Text`, `Heading`, `Link`, `Divider`, `Stack`, `Inline`, `Accordion`, `Breadcrumb`, `ChoiceGroup`, `Table`, `Tabs` and `Dialog` — and the gallery exercises every one of them. `Tabs` and `Breadcrumb` accept per-item `attributes` on the generated element, and `Table` accepts `CellBag` cells plus attributed rows; the gallery demonstrates all three. `Form` builds plain HTML by default and emits `@csrf` plus the hidden `_method` field only when asked (`csrf()`, `methodInput()`).

Accessibility is part of the contract: `Field` derives an id from the input, points the label's `for` at it, gives the error `id="{id}-error"`, and sets `aria-describedby` plus `aria-invalid` on the control. `required` lives on the input (`Input::required()`, forwarded by `Field::required()`), and the visible asterisk is pure CSS (`label:has(+ input:required)::after`), so the label component never learns about it. A test walks the gallery and fails on duplicate ids or a `for` that resolves to nothing.

The gallery is a list of box classes under `App\DesignSystem\Boxes`, each holding its `NAME`, its `options()` and the components it renders for a design; `DesignSystemCollection::boxes()` holds the order. That order is visual — the page is a two-column grid — so boxes can be rearranged by moving a line. A box can also return in-context compositions through `examples()` (an `Example` is a label, a description and a closure from the active design to a component), shown below the grid in an "In a real project" section; boxes without compositions return `[]`. Each box declares its own `GroupOptions` — the same options the kitchen-sink groups use — so a box can turn flex off, sit in a column, or choose its justification and alignment without touching the view. The Dialog box shows the modal with Invoker Commands (`command="show-modal"` plus `commandfor`), so opening and closing need no JavaScript — `showModal()` brings the backdrop, focus handling and `Esc` for free. `Dialog::open()` remains for server-rendered contexts that want the layer in the document. Labels are shown inside the field components rather than in a box of their own.

Keys are enums (`Juaniquillo\BackendDesignSystem\Theme\*`): `ButtonVariant`, `BadgeVariant`, `AlertVariant`, `HeadingLevel`, plus the shared `Look`, `Size` and `State`. Components therefore take enums instead of strings, and `ThemeVariants` is the one place that knows how a key is spelled. The classes themselves stay in the `.blade.php` theme files: the bundled designs keep theirs in the package, while `soft` keeps its in `resources/views`, so only the bundled ones need Tailwind pointed at them — see below.

## Using the default classes

The bundled designs ship their Tailwind classes in `.blade.php` files inside the package. Tailwind only generates a utility it has seen, and `vendor/` is excluded from its scan, so the package views have to be added explicitly. This app does it in `resources/css/design-system.css`, the gallery's own entry:

```css
@source '../../vendor/juaniquillo/backend-design-system/resources/views/**/*.blade.php';
```

That entry imports `base.css` and is loaded by `/design-system` instead of `app.css`, so the package's theme classes never reach the rest of the site. `app.css` scans the app's own sources only; anything the gallery does not use is not downloaded by other pages.

Nothing is copied, and a package update changes the design on the next `npm run build`. With Tailwind v3, list the same path in `tailwind.config.js` under `content` instead.

The app-owned `soft` and `classic` themes need no line of their own: they sit in `resources/views`, inside the default scan.

Two alternatives, for when a theme should be app-owned:

- **Publish the bundled themes.** `php artisan vendor:publish --tag=backend-design-system-themes` (or `--tag=backend-design-system-theme-base-dual` for one design) copies them into `resources/views/backend-design-system`, inside Laravel's default scan, so no `@source` line is needed. The bundled designs still render from the package; to edit a copy, point a design at it with `path`.
- **Scaffold a theme.** `php artisan design-system:make-theme app` copies `base` to `resources/views/backend-design-system/app`, adds an `app` design with a `path` entry to `config/backend-design-system.php` (publishing that config first if it does not exist) and makes it the default. `--from`, `--dark`, `--to`, `--keep-default` and `--force` cover the rest.

Run `npm run build` after any of these. If a design loses its colours after a build, the classes never reached the scanner: check the source line or the published copy.

## Adding a component

Components belong to `juaniquillo/backend-design-system`; this app does not define its own. Adding one to the gallery is a box in `DesignSystemCollection::boxes()` plus its title in the gallery test.

A new component in the package is a class using `IsDesignComponent` plus one theme file per bundled design; the package's gates cover key parity, enum coverage and the no-colour rule for `state.*`. Slots accept strings, design components or backend components directly — `component()` is only needed when a slot has to receive a bare backend component.

## Adding a design

- **Bundled designs**: nothing to do; they are part of the package.
- **An app-owned design**: `soft` and `classic` in this repo are the worked examples. `php artisan design-system:make-theme <key> --from=base-dual --keep-default` scaffolds the folder and the config entry (`--from` also decides the dark mode: `base-dual` → `adaptive`), or write the entry by hand in `config/backend-design-system.php` (`label`, `path`, `dark`), with `path` relative to `resources/`.
- **A packaged design**: a `ThemeManager` class whose constructor sets its own path, named in the config's `manager` key. Its theme files still need to reach Tailwind, through the `@source` line or a published copy.

A folder has to provide every theme group the components use. Layering (a design that only overrides what it changes) is the planned follow-up, and the reason the theme manager, not the `Design`, owns the path.

## Dashboard preview

`/design-system/dashboard` shows the `classic` design the way a project uses it: an app shell with a sidebar (a native-dialog side sheet on mobile), a top strip, breadcrumb, title with actions that open a native-dialog invoice form, an overdue notice, four KPI cards, a revenue-by-month bar chart with native tooltips, an invoices card with a filter toolbar, keyboard-navigable tabs (including an empty-state panel) and pagination, and an invite form — all package components plus SVG charts (`App\DesignSystem\Examples\Chart`), composed in `App\DesignSystem\Examples\Dashboard`. The invite and new-invoice forms post to real dashboard routes that validate and redirect back with a flash notice, so the demo never hits a 405. The charts paint `currentColor`, so they inherit the card's themed text colour in every design instead of carrying their own palette. It defaults to `classic` while the gallery defaults to `base`; `?design=` switches (unknown keys 404) through the shared `App\DesignSystem\DesignSwitch::resolve()`, so both pages stay in step. The page renders in the bare `canvas` layout instead of the site nav, so it reads like an app rather than a page inside the kitchen sink.

The preview surface follows the design: `App\DesignSystem\DesignCanvas` maps a design key to a page background (`classic` previews on slate so its cards sit on a matching surface, the rest keep zinc). An `Example` can also name its own surface through `$canvas`; the six current ones inherit the design's.
