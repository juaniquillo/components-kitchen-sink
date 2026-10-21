<?php

declare(strict_types=1);

use App\Builder\Registries\ThemeRegistry;

it('starts empty', function () {
    $registry = new ThemeRegistry;

    expect($registry->groups())->toBe([])
        ->and($registry->variants())->toBe([]);
});

it('holds a group of variants', function () {
    $registry = (new ThemeRegistry)
        ->with('action', 'primary', 'bg-indigo-600 text-white')
        ->with('action', 'danger', 'bg-red-600 text-white');

    expect($registry->groups())->toBe([
        'action' => [
            'primary' => 'bg-indigo-600 text-white',
            'danger' => 'bg-red-600 text-white',
        ],
    ])
        ->and($registry->classes('action', 'primary'))->toBe('bg-indigo-600 text-white')
        ->and($registry->has('action', 'danger'))->toBeTrue()
        ->and($registry->has('action', 'missing'))->toBeFalse();
});

it('changes a variant instead of adding it twice', function () {
    $registry = (new ThemeRegistry)
        ->with('action', 'primary', 'bg-indigo-600')
        ->with('action', 'primary', 'bg-red-600');

    expect($registry->classes('action', 'primary'))->toBe('bg-red-600')
        ->and($registry->variants())->toHaveCount(1);
});

it('cleans the classes up on the way in', function () {
    $registry = (new ThemeRegistry)->with('action', 'primary', 'bg-indigo-600 "onclick="x');

    expect($registry->classes('action', 'primary'))->toBe('bg-indigo-600');
});

it('refuses a name that cannot be used', function () {
    $registry = (new ThemeRegistry)->with('../etc', 'primary', 'bg-indigo-600');

    expect($registry->groups())->toBe([])
        ->and(ThemeRegistry::allowsName('action'))->toBeTrue()
        ->and(ThemeRegistry::allowsName('action-2'))->toBeTrue()
        ->and(ThemeRegistry::allowsName('action.2'))->toBeFalse()
        ->and(ThemeRegistry::allowsName('action/2'))->toBeFalse();
});

it('drops a group that is left without any variant', function () {
    $registry = (new ThemeRegistry)
        ->with('action', 'primary', 'bg-indigo-600')
        ->without('action', 'primary');

    expect($registry->groups())->toBe([]);
});

it('flattens the variants for a picker', function () {
    $registry = (new ThemeRegistry)
        ->with('action', 'primary', 'bg-indigo-600')
        ->with('card', 'default', 'rounded-lg border');

    expect($registry->variants())->toBe([
        ['group' => 'action', 'variant' => 'primary', 'classes' => 'bg-indigo-600'],
        ['group' => 'card', 'variant' => 'default', 'classes' => 'rounded-lg border'],
    ]);
});

it('keeps only the themes that may be used', function () {
    $themes = ThemeRegistry::sanitize([
        'action' => [
            'primary' => 'bg-indigo-600',
            'not a name' => 'bg-red-600',
        ],
        'not a group' => [
            'primary' => 'bg-red-600',
        ],
        'card' => 'not an array',
    ]);

    expect($themes)->toBe([
        'action' => [
            'primary' => 'bg-indigo-600',
        ],
    ]);
});

it('drops a theme without any classes', function () {
    expect(ThemeRegistry::sanitize([
        'action' => ['primary' => '   '],
    ]))->toBe([]);
});
