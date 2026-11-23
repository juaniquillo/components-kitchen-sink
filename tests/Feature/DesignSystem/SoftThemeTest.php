<?php

declare(strict_types=1);

function softThemePath(): string
{
    return resource_path('views/backend-design-system/soft');
}

/**
 * @return array<int, string>
 */
function softThemeFiles(): array
{
    return glob(softThemePath().'/*.blade.php') ?: [];
}

/**
 * The utility part of a class, without its variant prefixes.
 */
function softUtility(string $token): string
{
    $utility = str_contains($token, ':') ? substr($token, strrpos($token, ':') + 1) : $token;

    // bg-sky-400/10 is still a colour; the opacity suffix is not part of
    // the utility name.
    return preg_replace('#/\d+$#', '', $utility) ?? $utility;
}

/**
 * @return array<int, string>
 */
function softColorTokens(string $classes): array
{
    $tokens = [];

    foreach (preg_split('/\s+/', trim($classes)) ?: [] as $token) {
        $utility = softUtility($token);

        if (preg_match('/^(bg|text|border|placeholder|ring|divide|outline|decoration|accent|caret|fill|stroke)-(white|black|transparent|current|[a-z]+-\d{2,3})$/', $utility) === 1) {
            $tokens[] = $utility;
        }
    }

    return $tokens;
}

it('is registered as an adaptive application design', function () {
    $definition = config('backend-design-system.designs.soft');

    expect($definition)->toBeArray()
        ->and($definition['label'])->toBe('Soft')
        ->and($definition['path'])->toBe('views/backend-design-system/soft')
        ->and($definition['dark'])->toBe('adaptive')
        ->and($definition)->not->toHaveKey('manager');
});

it('pairs every color with its dark counterpart', function () {
    // The bundled designs are gated by the package; this one is ours, so the
    // adaptive promise is ours to keep.
    foreach (softThemeFiles() as $file) {
        $variants = (array) include $file;

        foreach ($variants as $name => $classes) {
            if (softColorTokens((string) $classes) !== []) {
                $this->assertTrue(
                    str_contains((string) $classes, 'dark:'),
                    basename($file).' / '.$name.' has colors without a dark counterpart: '.$classes,
                );
            }
        }
    }
});

it('stays complete against the bundled design it was copied from', function () {
    // A missing file only throws when that component renders, which would
    // leave a hole nobody notices until a page uses it.
    $bundled = glob(base_path('vendor/juaniquillo/backend-design-system/resources/views/backend-design-system/base-dual/*.blade.php')) ?: [];

    $expected = array_map(fn (string $file): string => basename($file), $bundled);
    $actual = array_map(fn (string $file): string => basename($file), softThemeFiles());

    sort($expected);
    sort($actual);

    expect($actual)->toBe($expected);
});
