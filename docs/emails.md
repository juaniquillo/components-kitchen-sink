# Emails

[← Back to the README](../README.md)

Responsive HTML emails built with `juaniquillo/mjml-backend-components`, browsable at `/emails` with per-email previews at `/emails/{name}`:

| Email | Accent | Description |
|---|---|---|
| `welcome` | Indigo `#4f46e5` | Onboarding: hero, 2 value-prop rows, CTA band |
| `notification` | Emerald `#059669` | Alert card plus "what happened / what to do next" details band |
| `newsletter` | Indigo `#4f46e5` | Editorial issue: masthead, hero, 3 image+text stories, checklist CTA band |

All three share a slate ink/body/muted palette, the bolt logo from `public/images/mail`, and a personalized `Hi {{ $name }},` greeting rendered in bold above the hero. The index page presents each email through a Flux modal trigger; the modal embeds the compiled email in an iframe via the `email.preview` route.

## Architecture

```
app/Emails/
├── Contracts/EmailInterface.php + EmailPresenterInterface.php
├── Messages/WelcomeEmail.php, NotificationEmail.php, NewsletterEmail.php
├── Presenters/EmailPresenter.php
├── Exceptions/MissingEmailUserException.php
└── EmailCollection.php
```

The pipeline: `Messages::*::build()` composes an MJML tree → `php artisan emails:render` compiles it to `resources/views/mail/*.blade.php` → `App\Mail\Send*` mailables render those views with the `User` → preview routes display them. Content builders (`Emails\Messages\*`) stay separate from senders (`Mail\Send*`); `EmailCollection` is the registry, `EmailPresenter` renders the modal preview behind the `EmailPresenterInterface` seam.

## Preview routes

- `/emails/{name}` resolves the first `User` and passes it to the mailable so the greeting renders with a real name.
- Unknown email names `abort(404)`; a missing user throws `MissingEmailUserException` (500) so the two failures are never confused. Covered by `tests/Feature/EmailPreviewTest.php`.

## MJML gotchas applied here

- Every `SECTION` wraps content in `COLUMN` children — content directly in a section violates MJML structural rules.
- Critical styles (colors, font sizes) go in component attributes via an `ATTRIBUTES` block (`mj-all`, `mj-text`, `mj-button`, `mj-class`), not CSS classes — Gmail strips `<head>` CSS.
- Images use `asset()` so `src` is absolute (`APP_URL`-based); relative paths are invisible to email clients.
- Plain `{{ $name }}` interpolation passes through MJML compilation untouched and resolves as Blade at send time; only block tags containing `<`/`>` need protection.
- No dark-mode overrides yet; no `SOCIAL`/`TABLE`/`WRAPPER` components — the builder enum covers document, layout (`SECTION`, `COLUMN`, `GROUP`, `HERO`, `SPACER`), content (`TEXT`, `IMAGE`, `BUTTON`, `DIVIDER`, `RAW`), and head config.
