<?php

declare(strict_types=1);

return [

    /*
    |--------------------------------------------------------------------------
    | Default Design
    |--------------------------------------------------------------------------
    |
    | The design every component renders with when none is passed to it.
    | Components accept an explicit Design for per-call or per-section
    | overrides; this key is what they fall back to.
    |
    | A key that is not in the designs list is a configuration error, not a
    | silent fallback, so a typo fails the first component that renders.
    |
    */

    'default' => env('DESIGN_SYSTEM_DESIGN', 'base'),

    /*
    |--------------------------------------------------------------------------
    | Designs
    |--------------------------------------------------------------------------
    |
    | Every design is a label, a dark mode and a theme source:
    |
    | - `manager` names a ThemeManager class that knows where its theme
    |   lives. This is the form a packaged design ships.
    | - `path` names a folder under resources/views; it is wrapped in the
    |   generic manager, which is the quickest way for an app to try a look.
    |
    | `dark` is one of `none`, `always` or `adaptive`.
    |
    */

    'designs' => [

        'base' => [
            'label' => 'Base',
            'path' => 'views/_themes/designs/base',
            'dark' => 'none',
        ],

        'base-dark' => [
            'label' => 'Base Dark',
            'path' => 'views/_themes/designs/base-dark',
            'dark' => 'always',
        ],

        'base-dual' => [
            'label' => 'Base Dual',
            'path' => 'views/_themes/designs/base-dual',
            'dark' => 'adaptive',
        ],

    ],

];
