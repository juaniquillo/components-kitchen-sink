<?php

declare(strict_types=1);

namespace App\Builder\Support;

/**
 * The Tailwind declarations the runtime build needs to know about.
 *
 * The canvas is styled by the browser build of Tailwind, which does not read
 * the application's stylesheet. The custom theme and the dark variant live in
 * that stylesheet, so they are taken from it and handed over, which keeps the
 * two in step without repeating them.
 */
final class RuntimeTailwind
{
    /**
     * The declarations the browser build has to be given.
     *
     * An empty string is returned when the stylesheet cannot be read, which
     * leaves the runtime build with the Tailwind defaults.
     */
    public static function declarations(): string
    {
        $css = @file_get_contents(resource_path('css/app.css'));

        if ($css === false) {
            return '';
        }

        $declarations = [];

        if (preg_match('/@theme\s*\{.*?\}/s', $css, $matches) === 1) {
            $declarations[] = $matches[0];
        }

        if (preg_match('/@custom-variant\s+dark\s*[^;]+;/', $css, $matches) === 1) {
            $declarations[] = $matches[0];
        }

        return implode("\n\n", $declarations);
    }
}
