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
        $entry = resource_path('css/app.css');

        $css = self::inlineImports($entry, []);

        if ($css === '') {
            return '';
        }

        $declarations = [];

        if (preg_match_all('/@theme\s*\{.*?\}/s', $css, $matches) > 0) {
            foreach ($matches[0] as $block) {
                $declarations[] = $block;
            }
        }

        if (preg_match_all('/@custom-variant\s+dark\s*[^;]+;/', $css, $matches) > 0) {
            foreach ($matches[0] as $block) {
                $declarations[] = $block;
            }
        }

        return implode("\n\n", $declarations);
    }

    /**
     * The entry stylesheet with relative imports inlined.
     *
     * The custom theme lives in the imported file, not in the entry, so
     * the entry alone yields no declarations. Only relative imports are
     * followed; package imports (tailwindcss, plugins, vendor css) are
     * left to the compiler that owns them.
     *
     * @param  array<string, true>  $visited
     */
    private static function inlineImports(string $path, array $visited): string
    {
        $real = realpath($path);

        if ($real === false || isset($visited[$real])) {
            return '';
        }

        $visited[$real] = true;

        $css = @file_get_contents($real);

        if ($css === false) {
            return '';
        }

        $dir = dirname($real);

        if (preg_match_all('/@import\s+(?:url\(\s*)?[\'"]([^\'"]+)[\'"]\s*[^;]*;/', $css, $matches) > 0) {

            foreach ($matches[1] as $specifier) {
                if (! str_starts_with($specifier, '.')) {
                    continue;
                }

                $resolved = realpath($dir.'/'.$specifier);

                if ($resolved === false || ! str_ends_with($resolved, '.css')) {
                    continue;
                }

                $inlined = self::inlineImports($resolved, $visited);

                if ($inlined !== '') {
                    $css .= "\n".$inlined;
                }
            }
        }

        return $css;
    }
}
