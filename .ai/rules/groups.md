---
paths:
  - 'app/Components/Groups/Bootstrap*.php'
---

# Groups

## Bootstrap groups use CSS classes, not themes
Bootstrap component groups must use plain CSS class attributes via ComponentBuilder, NOT the Tailwind theme system. Themes are Tailwind-specific. Bootstrap classes go directly in `class` attributes (e.g. `class="table table-striped"`).
