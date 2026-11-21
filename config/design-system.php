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
    | A key that is not in the registry is a configuration error, not a
    | silent fallback, so a typo fails the first component that renders.
    |
    */

    'default' => env('DESIGN_SYSTEM_DESIGN', 'base'),

];
