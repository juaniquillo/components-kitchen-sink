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
- **juaniquillo/mjml-backend-components** - MJML email builder, enum, and compiler drivers
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

The Slate group is built with `juaniquillo/slate-backend-components`, which adapts the `BackendComponent` contract to render Slate's anonymous Blade components. Components are composed with the `SlateComponentEnum` (over 200 cases) and `SlateComponentBuilder`, using helpers like `SlateOverlayUtil` (dialogs), `SlateTabsUtil` (tabs), and `SlateUITableUtil` (tables). Each `SlateBackendComponent` resolves through `backend-component::_utilities.resolve-third-party-component`, which delegates to `<x-dynamic-component>`. The Slate group also registers its own stylesheet (`resources/css/slate.css`) as a per-group asset.

### Emails

Responsive HTML emails built with `juaniquillo/mjml-backend-components`, browsable at `/emails` with per-email previews at `/emails/{name}`:

| Email | Accent | Description |
|---|---|---|
| `welcome` | Indigo `#4f46e5` | Onboarding: hero, 2 value-prop rows, CTA band |
| `notification` | Emerald `#059669` | Alert card plus "what happened / what to do next" details band |
| `newsletter` | Indigo `#4f46e5` | Editorial issue: masthead, hero, 3 image+text stories, checklist CTA band |

All three share a slate ink/body/muted palette, the bolt logo from `public/images/mail`, and a personalized `Hi {{ $name }},` greeting rendered in bold above the hero. The index page presents each email through a Flux modal trigger; the modal embeds the compiled email in an iframe via the `email.preview` route.

#### Architecture

```
app/Emails/
├── Contracts/EmailInterface.php + EmailPresenterInterface.php
├── Messages/WelcomeEmail.php, NotificationEmail.php, NewsletterEmail.php
├── Presenters/EmailPresenter.php
├── Exceptions/MissingEmailUserException.php
└── EmailCollection.php
```

The pipeline: `Messages::*::build()` composes an MJML tree → `php artisan emails:render` compiles it to `resources/views/mail/*.blade.php` → `App\Mail\Send*` mailables render those views with the `User` → preview routes display them. Content builders (`Emails\Messages\*`) stay separate from senders (`Mail\Send*`); `EmailCollection` is the registry, `EmailPresenter` renders the modal preview behind the `EmailPresenterInterface` seam.

#### Preview routes

- `/emails/{name}` resolves the first `User` and passes it to the mailable so the greeting renders with a real name.
- Unknown email names `abort(404)`; a missing user throws `MissingEmailUserException` (500) so the two failures are never confused. Covered by `tests/Feature/EmailPreviewTest.php`.

#### MJML gotchas applied here

- Every `SECTION` wraps content in `COLUMN` children — content directly in a section violates MJML structural rules.
- Critical styles (colors, font sizes) go in component attributes via an `ATTRIBUTES` block (`mj-all`, `mj-text`, `mj-button`, `mj-class`), not CSS classes — Gmail strips `<head>` CSS.
- Images use `asset()` so `src` is absolute (`APP_URL`-based); relative paths are invisible to email clients.
- Plain `{{ $name }}` interpolation passes through MJML compilation untouched and resolves as Blade at send time; only block tags containing `<`/`>` need protection.
- No dark-mode overrides yet; no `SOCIAL`/`TABLE`/`WRAPPER` components — the builder enum covers document, layout (`SECTION`, `COLUMN`, `GROUP`, `HERO`, `SPACER`), content (`TEXT`, `IMAGE`, `BUTTON`, `DIVIDER`, `RAW`), and head config.

### Component Builder

A drag-and-drop builder for `backend-component` trees, at `/builder`. Compose a component from the base `ComponentEnum` vocabulary, then copy the PHP that reproduces it. Every workspace has its own address, so handing one over is a matter of sending a link.

| Panel | What it does |
|---|---|
| **Workspace header** | The workspace name, saved as it is typed, with **Copy link**, **New**, **Reset** and **Delete**. |
| **Palette** | Every base component, grouped (Layout, Typography, Text, Media & Links, Lists, Table, Form). Click to add. The Table group also offers **Add table…**, which asks for rows and columns and scaffolds the head and body. |
| **Canvas** | The real rendered output. Drag the handle to reorder a node or move it into another parent; click to select. |
| **Drawer** | Slides in from the right: the node being configured, the table form, or the themes panel. |
| **Generated code** | The PHP for the current tree, with a copy button. |

#### Workspaces

`/builder` is onboarding: **New workspace** makes one, and a second field opens an existing one from a pasted link — the whole address or just the id at the end. A workspace lives at `/builder/{workspace}`, where the id is a ULID.

The address is the whole of the capability: anyone holding the link can open and edit the workspace. There are no accounts, no sharing settings and no lock — one writer at a time is assumed, and two tabs on the same link are last-write-wins.

The name in the header belongs to the workspace and not to the composition: **Reset** clears the canvas but keeps the workspace and its name, and the undo brings the canvas back. **New** starts a second workspace and leaves the first where it is. **Delete** removes the file for everyone holding the link and returns to onboarding.

A workspace is one flat JSON file under `storage/app/private/builder/`, named by its id:

```json
{
    "id": "01J...",
    "version": 1,
    "name": "Hero section",
    "saved_at": "2026-10-29T21:06:09.077783+00:00",
    "tree": { "root": "n1", "nodes": {} },
    "themes": { "action": { "primary": "bg-indigo-600 text-white" } }
}
```

Nothing is saved by hand. Every request that changes the tree, the themes or the name writes the file once, on its way out, and only when a fingerprint says something moved — opening the drawer or the preview leaves it alone. **Ctrl+S** writes it again and shows the toast as an acknowledgement.

**Undo and redo** live in the browser session, keyed by the workspace id, with 25 steps. They are not part of the file: a shared link opens on the work without someone else's undo stack, and requests do not carry the history.

#### Architecture

```
app/Builder/
├── Components/ComponentDescriptor.php + ContentMode.php
├── Contracts/BuilderNode.php
├── Registries/ComponentRegistry.php + ThemeRegistry.php
└── Support/AttributeGuard.php, ClassList.php, CompositionHistory.php,
             NodeChrome.php, TreeRenderer.php, TreeMutator.php,
             PhpCodeGenerator.php, ThemeArchive.php, CompositionSerializer.php,
             WorkspaceRepository.php, RuntimeTailwind.php

app/Livewire/Builder/BuilderCanvas.php + DrawerMode.php + NoticeSurface.php
```

The tree is a flat map of nodes keyed by id, each holding the ids of its children (`App\Builder\Contracts\BuilderNode`). A flat map keeps the type free of recursion, makes moving a node a matter of editing two lists, and stays small on disk.

`TreeRenderer` turns the tree into real `MainBackendComponent` objects, so the canvas is the genuine article — the same code the generator emits, rendered by the same views. `PhpCodeGenerator` walks the same tree to produce the PHP.

#### The node chrome

Every node carries a small toolbar in its top-right corner: a **drag handle**, a **configure button**, an **add content** button that opens a menu of the components that node may contain, and a **delete** button.

The chrome has to live *inside* the node it belongs to — the handle must be a descendant of the sortable item, and the buttons have to travel with the node when it is dragged. That means the chrome's tag depends on the node's content model, so the markup stays valid wherever it lands:

| Node | Chrome tag | Nodes with no chrome |
|---|---|---|
| `div`, `form`, `fieldset`, `li`, `details`, `dialog` | `<div>` | `img`, `col`, `colgroup`, and every form control (`input`s, `textarea`, `select`, `optgroup`, `option`, `datalist`) |
| `p`, `h1`–`h6`, `span`, `a`, `button`, `label`, `summary`, `td`, `th` | `<span>` | |
| `ul`, `ol` | `<li>` | |
| `tr` | `<tr>` | |
| `thead`, `tbody`, `tfoot` | `<tr>` | |
| `table` | `<caption>` | |

A `<div>` inside a `<p>` or a `<ul>` is hoisted out by the browser's parser, which would quietly restructure the tree — a `<span>` is the one element that fits inside every phrasing context.

A component that cannot hold a chrome of its own is **wrapped** in a `<span>` that can, so the toolbar still travels with it. The wrapper is builder-only and never reaches the generated code. Four components cannot be wrapped — a `<span>` is invalid inside a `<select>`, an `<optgroup>`, a `<colgroup>` or a `<table>` — so `form.option`, `form.optgroup`, `table.col` and `table.colgroup` keep the **Configure selected** route.

The chrome is never part of the tree, so the code generator never sees it. A node keeps a row free above its content for the chrome to sit in, and an empty inline element is given a box so it can be seen and grabbed; both are stripped from the generated PHP.

#### Text

Text is a **content entry** rather than a field on the node, so a paragraph can hold text and a link and more text in any order:

```php
ComponentBuilder::make(ComponentEnum::PARAGRAPH)
    ->setContents([
        'First text',
        ComponentBuilder::make(ComponentEnum::LINK)
            ->setContents([
                ' This is a link',
            ])
            ->setAttributes([
                'href' => 'https://google.com',
            ]),
        'Second Text',
    ]);
```

Internally a text segment is a node of its own, so it can be edited and removed like anything else. The **add content** menu has a **Text** entry at the top; picking it reveals an input where the segment is typed. The drawer lists the text segments of the node being configured.

The one wrinkle: `wire:sort` reports a position among the *draggable* items, and a text segment is not draggable, so a move has to skip over them.

#### Deleting

Delete lives on the chrome and is confirmed by the same button twice — the first click arms it and it becomes a red **Delete?**, the second deletes. It disarms on mouse-leave or after four seconds, and the drawer closes if it was showing the node that went, or anything inside it.

#### Previewing

**Preview** in the canvas header opens a modal with the composition rendered *cleanly*: no chrome, no reserved row, no minimum sizes, no `wire:` attributes. That is exactly what the generated code produces, which is what makes it safe for the canvas to be distorted.

#### Drag and drop

Livewire 4 ships `wire:sort`, with SortableJS bundled, so no extra dependency is needed. The builder injects the sort attributes as ordinary component attributes, which the package's views render onto the element:

```
wire:sort:item  →  wire:key  →  wire:sort  →  wire:sort:group  →  wire:sort:group-id
```

The order matters: Livewire's sort binding walks the attributes in DOM order and stops at `wire:sort:group`, so `wire:sort` has to come first. A container stays a drop target even while it is empty, otherwise there would be nowhere to drop the first child into.

Drag starts from the handle (`wire:sort:handle`), which keeps the rest of the node selectable and clickable.

A node may be a link, and a click on a button inside a link still activates it, so the chrome swallows the default — and so does the node itself, which means clicking a link in the canvas selects it instead of navigating away. The **Preview** modal is where the real thing is seen.

The handler receives the dropped item, its new position and the container it landed in, which is exactly the primitive a tree needs. A drop the parent cannot hold, or one into the node itself, is refused before the tree is touched, and the canvas says why:

```php
public function handleSort(string $itemId, int $position, ?string $parentId): void
{
    if (($refusal = $this->sortRefusal($itemId, $parentId)) !== null) {
        $this->notify(NoticeSurface::CANVAS, $refusal);

        return;
    }

    $this->transact(function () use ($itemId, $parentId, $position): void {
        $this->tree = TreeMutator::move($this->tree, $itemId, $parentId, $position);
        $this->selectedId = $itemId;
    });
}
```

#### Attributes

The inspector takes any attribute name, with a couple of text inputs and a list of the ones already applied. Each applied attribute can be edited in place or deleted.

The name is not taken on trust. It ends up inside a Livewire expression and inside the generated HTML, and Blade escapes attribute *values* but not attribute *names*, so `App\Builder\Support\AttributeGuard` refuses:

| Refused | Why |
|---|---|
| `on*` | Event handlers — `onclick="…"` would be a live cross site scripting hole |
| `style` | CSS injection |
| `wire:*` | **Privilege escalation** — a workspace file could invoke any public method on the builder |
| `x-*` | Alpine's directive syntax |
| `data-node-id`, `data-selected` | The builder's own, and they would collide with the injected ones |
| anything that is not a valid attribute name | A quote or a bracket in the name would let it break out of the expression it sits in |

Everything else is allowed — `aria-label`, `data-*`, `itemprop`, `contenteditable`, `part`, `translate`. The guard is the single source of truth: the mutator applies it when an attribute is set, and the serializer applies it again when a workspace file is read back, so a hand written file cannot smuggle one in either.

The per component attribute lists are still there, but they are no longer a rule — they are the suggestions the name field offers.

#### Nesting rules

`ComponentRegistry` mirrors the HTML content models — a `ul` only holds `li`, a `p` only holds phrasing content, a `td` only lives in a `tr`, and a list item only lives in a list. That is not cosmetic: the browser silently hoists invalid children out of their parent, so keeping the dragged DOM valid is the same thing as keeping the rules correct.

A paragraph holds a link, a span or a `strong`; a `div` holds a paragraph; there is no limit on how deep the tree may go. A `p` cannot hold a `div`, because that is not what a paragraph is for — use a `div` if you need block content.

The components that hold text and the components that hold children are described separately, so a paragraph gets both a text field and an add button, while a `div` gets an add button but no reason to be typed into. Adding a text element opens the drawer on it, with the text field ready.

#### Themes

A theme is a named set of Tailwind classes, by group and variant (`action / primary`), defined in the drawer's **Themes** panel. A node holds a reference to the theme rather than a copy of its classes, so changing a theme restyles every component that uses it. The chips in the node panel toggle variants on and off, and a theme that is still used by a node cannot be deleted. The generated PHP names the group and the variant; the classes themselves live in the theme file.

The groups are listed collapsed to begin with: the file path in a group's header is the button that opens it, and **Collapse all** / **Expand all** move them all at once. Saving a variant opens its group, so a theme that was just added never lands out of sight.

**Download all themes (.zip)** exports every group as the file the local theme manager reads, under `resources/views/_themes/tailwind/<group>.blade.php`. Unzipping the archive at the root of a project puts the files in place, or its `resources` folder can be dragged into the project; a file with the same group name replaces the one that is already there. The archive carries a README that says so. The component is not part of the archive: it is copied from the Generated code panel, because only the user knows where it is going.

The canvas is styled by the browser build of Tailwind, not by the compiled stylesheet: the builder page turns the local assets off and is handed the application's `@theme` and `@custom-variant dark` declarations by `RuntimeTailwind`, so the two stay in step.

#### Reading a file back

A file is data, never code. `CompositionSerializer` validates everything it reads against the registry: an unknown component, an attribute the guard blocks, a theme reference that points at nothing, a child its parent cannot hold, a node with two parents and a cycle are all dropped before the tree reaches the canvas. The same pass runs on a file that was edited by hand, which is why the store can stay a plain directory.

#### Refusals

An action that cannot be done says why, in the place that asked for it: a drop the parent cannot hold, a node dropped into itself, a duplicated root, a table that would gain a second head, a theme that would lose its last class, an attribute the guard blocks. There is one notice at a time, tagged with the panel it belongs to, and the next successful action of that panel takes it away.

#### Known limitation

Tailwind scans *source files*. The chrome's classes live in `app/Builder/Support/NodeChrome.php`, so `resources/css/app.css` carries an `@source '../../app/Builder/**/*.php'` entry to pick them up.

A class typed into the themes panel appears on the canvas at once — the browser build generates it as it shows up — but it only reaches the rest of the application once it is in a source file the compiled build scans, or in a safelist. Pasting the generated PHP into a Blade view is the usual way: the classes it names are then in a file Tailwind reads.

#### Keyboard and accessibility

| Key | Does |
|---|---|
| `Ctrl`/`⌘` + `S` | Writes the workspace now and shows the toast |
| `Ctrl`/`⌘` + `Z` | Undo |
| `Ctrl`/`⌘` + `Shift` + `Z`, or `Ctrl`/`⌘` + `Y` | Redo |
| `Escape` | Closes the drawer, a menu, or the preview dialog |

The undo keys belong to the field while the focus is in an input, so typing keeps the browser's own undo. Dragging needs a pointer: the keyboard way to move a node is **Up** / **Down** in the drawer.

The drawer is `inert` while it is closed, moves focus to its **Close** button when it opens, and gives the focus back to the canvas when it closes. Every icon of the node chrome has an accessible name, and the entries of the add menu are real buttons, so Tab reaches them and Enter and Space fire them. The header of a theme group is a button as well, so a keyboard opens and closes a group with Enter, and the class field inside it is named after its group and variant. The builder was checked with axe-core in a real browser; the one violation left on the page is the shared navigation's link colour, which is an application-wide decision and not the builder's.

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

Or run it in parallel, the way the full suite is checked before a change is finished:

```bash
php artisan test --parallel --compact
```

Pest itself accepts a path and a filter:

```bash
vendor/bin/pest tests/Feature/Builder --compact
vendor/bin/pest --filter="undoes a change"
```

## Package Integration

This project experiments with the `juaniquillo/laravel-backend-component` package. Key integration points:

- **`BackendComponent` interface** - Implemented by `FluxBackendComponent` (from `juaniquillo/flux-backend-components`) and `SlateBackendComponent` (from `juaniquillo/slate-backend-components`)
- **`ComponentBuilder`** - Fluent builder for creating component trees
- **`ComponentEnum`** - Enumeration of all available component types
- **Theme system** - Default theme manager with configurable themes, plus app-level Tailwind theme files
- **Content system** - HasContent concern for managing component content
- **`Component` contract** - Each group implements `App\Components\Contracts\Component` with `list()` and `options()` returning a `ContainerOptions` DTO (flex/column/gap layout settings)
- **`RouteCollectionGroup`** - Central registry mapping each route group to its components and static assets
- **`ComponentCollection`** - Collection that aggregates group components for rendering
- **`MjmlComponentBuilder`** - Fluent builder for MJML trees; shorthands exist for `document`, `head`, `preview`, `font`, `attributes`, `style`, `section`, and `text`, every other `MjmlComponentEnum` case goes through generic `make()`
- **`toHtml()` vs `renderHtml()`** - `toHtml()` returns raw MJML markup; `renderHtml()` compiles it to production HTML via the bound `CompilesMjml` driver (node process by default)

### Custom Component Development

To create new backend components:

1. Implement `Juaniquillo\BackendComponents\Contracts\BackendComponent`
2. Use concerns like `HasContent`, `IsThemeable`, `HasPath` as needed
3. Implement `getContext()`, `getName()`, `getAttributeBag()`, `toArray()`, `toHtml()`
4. Register the component in the appropriate CRUD or component group

Existing adapters serve as reference implementations:

- `FluxBackendComponent` (from `juaniquillo/flux-backend-components`) - Flux-styled backend components using `FluxComponentEnum` and `FluxComponentBuilder`
- `SlateBackendComponent` (from `juaniquillo/slate-backend-components`) - Resolves `SlateComponentEnum` cases to `slate::*` views through `x-dynamic-component`

## License

MIT