<?php

declare(strict_types=1);

use App\DesignSystem\DesignRegistry;
use App\DesignSystem\Theme\AlertVariant;
use App\DesignSystem\Theme\BadgeVariant;
use App\DesignSystem\Theme\ButtonVariant;
use App\DesignSystem\Theme\Look;
use App\DesignSystem\Theme\Size;
use App\DesignSystem\Theme\State;

function themeFiles(string $key): array
{
    $design = DesignRegistry::get($key);

    return glob($design->themeManager->getThemePath().'/*.blade.php');
}

function themeVariants(string $file): array
{
    return include $file;
}

function colorTokens(string $classes): array
{
    $tokens = [];

    foreach (preg_split('/\s+/', trim($classes)) ?: [] as $token) {
        if (preg_match('/^(bg|text|border|placeholder|ring|divide|outline|decoration|accent|caret|fill|stroke)-(white|black|transparent|current|[a-z]+-\d{2,3})$/', (string) $token)) {
            $tokens[] = $token;
        }
    }

    return $tokens;
}

it('keeps single-mode designs free of conditional branches', function () {
    // A design that is always light or always dark has no use for dark:
    // variants. A branch there would never trigger in one mode, or would
    // quietly turn the design adaptive.
    foreach (['base', 'base-dark'] as $key) {
        foreach (themeFiles($key) as $file) {
            foreach (themeVariants($file) as $name => $classes) {
                expect($classes)->not->toContain('dark:');
            }
        }
    }
});

it('pairs every color with its dark counterpart in adaptive designs', function () {
    foreach (themeFiles('base-dual') as $file) {
        foreach (themeVariants($file) as $name => $classes) {
            if (colorTokens((string) $classes) !== []) {
                expect($classes)->toContain('dark:');
            }
        }
    }
});

it('keeps state variants free of colors', function () {
    // A state is an affordance the component appends to the look. Giving
    // it colors would quietly replace the look it was meant to decorate.
    foreach (['base', 'base-dark', 'base-dual'] as $key) {
        foreach (themeFiles($key) as $file) {
            foreach (themeVariants($file) as $name => $classes) {
                if (str_starts_with((string) $name, 'state.')) {
                    expect(colorTokens((string) $classes))->toBe([]);
                }
            }
        }
    }
});

it('keeps every class of a component in its own file', function () {
    // A component-sized file of its own is the shape that fragments a
    // component; dimensions live in its file as size.* keys instead.
    foreach (['base', 'base-dark', 'base-dual'] as $key) {
        foreach (themeFiles($key) as $file) {
            expect(basename($file))->not->toMatch('/-(size|state)\.blade\.php$/');
        }
    }
});

it('namespaces dimension keys and leaves looks unprefixed', function () {
    foreach (['base', 'base-dark', 'base-dual'] as $key) {
        foreach (themeFiles($key) as $file) {
            foreach (array_keys(themeVariants($file)) as $name) {
                $name = (string) $name;

                if (str_contains($name, '.')) {
                    expect($name)->toMatch('/^(size|state)\./');
                }
            }
        }
    }
});

it('keeps the sizes of a button in the button file', function () {
    foreach (['base', 'base-dark', 'base-dual'] as $key) {
        $button = themeVariants(
            DesignRegistry::get($key)->themeManager->getThemePath().'/button.blade.php'
        );

        expect($button)->toHaveKeys(['size.sm', 'size.md', 'size.lg'])
            ->not->toHaveKey('sm')
            ->not->toHaveKey('md')
            ->not->toHaveKey('lg')
            ->toHaveKey('state.disabled');
    }
});

it('defines every enum case in every design', function () {
    $expectations = [
        'button.blade.php' => [ButtonVariant::class, Size::class, State::class],
        'badge.blade.php' => [BadgeVariant::class],
        'alert.blade.php' => [AlertVariant::class],
        'input.blade.php' => [Look::class, State::class],
        'select.blade.php' => [Look::class, State::class],
        'textarea.blade.php' => [Look::class, State::class],
    ];

    foreach (['base', 'base-dark', 'base-dual'] as $key) {
        $path = DesignRegistry::get($key)->themeManager->getThemePath();

        foreach ($expectations as $file => $enums) {
            $keys = array_keys(themeVariants($path.'/'.$file));

            foreach ($enums as $enum) {
                foreach ($enum::cases() as $case) {
                    expect($keys)->toContain($case->value);
                }
            }
        }
    }
});

it('keeps tailwind classes out of the enums', function () {
    // The keys are the enums; the classes stay in the .blade.php files,
    // where Laravel's Tailwind setup already scans them. A class fragment
    // in an enum would need an extra @source from every consumer.
    $enums = [
        Look::class, Size::class, State::class,
        ButtonVariant::class, BadgeVariant::class, AlertVariant::class,
    ];

    foreach ($enums as $enum) {
        foreach ($enum::cases() as $case) {
            expect($case->value)->toMatch('/^[a-z][a-z.]*$/');
        }
    }
});

it('defines the same keys in every design', function () {
    // A key only one design knows renders there and throws elsewhere, so
    // the three folders have to agree group by group.
    $keys = [];

    foreach (['base', 'base-dark', 'base-dual'] as $key) {
        foreach (themeFiles($key) as $file) {
            $keys[basename($file)][$key] = array_keys(themeVariants($file));
        }
    }

    foreach ($keys as $group => $byDesign) {
        $expected = $byDesign['base'];

        foreach ($byDesign as $design => $actual) {
            expect($actual)->toBe($expected, "{$group} differs in {$design}");
        }
    }
});

it('builds the registry once per process', function () {
    expect(DesignRegistry::all())->toBe(DesignRegistry::all());
});
