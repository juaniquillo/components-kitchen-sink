<?php

declare(strict_types=1);

use Juaniquillo\MjmlBackendComponents\Compilers\NodeProcessCompiler;
use Juaniquillo\MjmlBackendComponents\Compilers\V8JsCompiler;

return [

    /*
    |--------------------------------------------------------------------------
    | Default MJML Compiler Driver
    |--------------------------------------------------------------------------
    |
    | This option controls the default compiler driver that will be used to
    | convert your MJML component markup trees into production HTML.
    |
    | Supported: "node", "v8js", "custom"
    |
    */

    'default' => env('MJML_COMPILER', 'node'),

    /*
    |--------------------------------------------------------------------------
    | MJML Compiler Drivers Configurations
    |--------------------------------------------------------------------------
    |
    | Here you can configure the settings for each compiler driver.
    |
    */

    'drivers' => [

        'node' => [
            'class' => NodeProcessCompiler::class,
            'binary' => env('MJML_NODE_BINARY', 'npx'),
            'arguments' => ['mjml', '-i', '-s'],
            'timeout' => (float) env('MJML_TIMEOUT', 60),
        ],

        'v8js' => [
            'class' => V8JsCompiler::class,
            'mjml_js_path' => env('MJML_JS_PATH', base_path('vendor/mjml/mjml.js')),
        ],

    ],

];
