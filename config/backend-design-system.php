<?php

declare(strict_types=1);

use Juaniquillo\BackendDesignSystem\Designs\BaseDarkThemeManager;
use Juaniquillo\BackendDesignSystem\Designs\BaseDualThemeManager;
use Juaniquillo\BackendDesignSystem\Designs\BaseThemeManager;

return [
    /*
    |--------------------------------------------------------------------------
    | Default Design
    |--------------------------------------------------------------------------
    |
    | The key of the design components resolve when none is passed to make().
    | It must exist in the "designs" array below.
    |
    */

    'default' => 'base',

    /*
    |--------------------------------------------------------------------------
    | Designs
    |--------------------------------------------------------------------------
    |
    | Each design is a label, a dark-mode rule and either a ThemeManager class
    | that knows its own folder or a path under resources/views. The bundled
    | designs read from the package, so they stay immutable. Publish the
    | themes for Tailwind, then point an application design at the copy if
    | you want to edit it:
    |
    | php artisan vendor:publish --tag=backend-design-system-themes
    |
    | Publish one bundled design instead with:
    | php artisan vendor:publish --tag=backend-design-system-theme-base
    |
    */

    'designs' => [
        'base' => [
            'label' => 'Base',
            'manager' => BaseThemeManager::class,
            'dark' => 'none',
        ],

        'base-dark' => [
            'label' => 'Base Dark',
            'manager' => BaseDarkThemeManager::class,
            'dark' => 'always',
        ],

        'base-dual' => [
            'label' => 'Base Dual',
            'manager' => BaseDualThemeManager::class,
            'dark' => 'adaptive',
        ],

        // design-system:designs
        'soft' => [
            'label' => 'Soft',
            'path' => 'views/backend-design-system/soft',
            'dark' => 'adaptive',
        ],

        // Scaffold an app-owned theme with:
        // php artisan design-system:make-theme app
    ],
];
