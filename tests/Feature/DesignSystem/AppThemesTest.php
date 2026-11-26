<?php

declare(strict_types=1);

function applicationThemesPath(string $key): string
{
    return resource_path('views/backend-design-system/'.$key);
}

/**
 * @return array<int, string>
 */
function applicationThemeFiles(string $key): array
{
    return glob(applicationThemesPath($key).'/*.blade.php') ?: [];
}

function applicationThemeUtility(string $token): string
{
    $utility = str_contains($token, ':') ? substr($token, strrpos($token, ':') + 1) : $token;

    // bg-sky-400/10 is still a colour; the opacity suffix is not part of
    // the utility name.
    return preg_replace('#/\d+$#', '', $utility) ?? $utility;
}

/**
 * @return array<int, string>
 */
function applicationThemeColors(string $classes): array
{
    $tokens = [];

    $subject = trim($classes);

    if ($subject === '') {
        return [];
    }

    foreach (preg_split('/\s+/', $subject) as $token) {
        $utility = applicationThemeUtility($token);

        if (preg_match('/^(bg|text|border|placeholder|ring|divide|outline|decoration|accent|caret|fill|stroke)-(white|black|transparent|current|[a-z]+-\d{2,3})$/', $utility) === 1) {
            $tokens[] = $utility;
        }
    }

    return $tokens;
}

it('registers every application-owned design', function () {
    // The bundled designs are the package's contract. The app adds its own
    // designs through make-theme and owns the rule that they pair colours
    // with dark counterparts.
    $designs = config('backend-design-system.designs');

    expect(array_column($designs, 'label', ''))->toContain('Soft', 'Classic');

    foreach (['soft', 'classic'] as $key) {
        $definition = $designs[$key];

        expect($definition)->toBeArray()
            ->and($definition['path'])->toBe('views/backend-design-system/'.$key);

        if ($definition['dark'] === 'adaptive') {
            foreach (applicationThemeFiles($key) as $file) {
                $variants = (array) include $file;

                foreach ($variants as $name => $classes) {
                    if (applicationThemeColors((string) $classes) !== []) {
                        expect(str_contains((string) $classes, 'dark:'))->toBeTrue(basename($file).' / '.$name);
                    }
                }
            }
        }
    }
});

it('keeps every application theme complete against its bundled source', function () {
    $designs = config('backend-design-system.designs');

    $expected = array_map(
        fn (string $file): string => basename($file),
        glob(base_path('vendor/juaniquillo/backend-design-system/resources/views/backend-design-system/base-dual/*.blade.php')) ?: []
    );

    foreach (['soft', 'classic'] as $key) {
        $actual = array_map(fn (string $file): string => basename($file), applicationThemeFiles($key));

        sort($expected);
        sort($actual);

        expect($actual)->toBe($expected);
    }
});
