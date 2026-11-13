<?php

declare(strict_types=1);

use App\Builder\Registries\ComponentRegistry;
use App\Builder\Scaffolds\ScaffoldFieldType;
use App\Builder\Scaffolds\ScaffoldRegistry;

it('registers a scaffold per compound with a known root type', function () {
    $keys = array_keys(ScaffoldRegistry::all());

    expect($keys)->toContain('table', 'details');

    foreach (ScaffoldRegistry::all() as $definition) {
        expect(ComponentRegistry::get($definition->rootType))->not->toBeNull();
    }
});

it('opens the table form with three columns and two rows', function () {
    $table = ScaffoldRegistry::get('table');

    expect($table?->defaults())->toBe(['columns' => 3, 'rows' => 2])
        ->and($table?->normalize(['columns' => 0, 'rows' => 200]))->toBe(['columns' => 1, 'rows' => 99])
        ->and(array_map(fn ($field) => $field->type, $table?->fields ?? []))->toBe([ScaffoldFieldType::NUMBER, ScaffoldFieldType::NUMBER]);
});

it('opens the details form with an empty summary and a closed details', function () {
    $details = ScaffoldRegistry::get('details');

    expect($details?->defaults())->toBe(['summary' => '', 'open' => false])
        ->and($details?->normalize(['summary' => '  Shipping  ', 'open' => 1]))->toBe(['summary' => 'Shipping', 'open' => true]);
});

it('offers scaffolds per palette group', function () {
    expect(array_map(fn ($definition) => $definition->key, ScaffoldRegistry::forPaletteGroup(ComponentRegistry::GROUP_TABLE)))->toBe(['table'])
        ->and(array_map(fn ($definition) => $definition->key, ScaffoldRegistry::forPaletteGroup(ComponentRegistry::GROUP_LAYOUT)))->toBe(['details'])
        ->and(ScaffoldRegistry::forPaletteGroup(ComponentRegistry::GROUP_TEXT))->toBe([]);
});

it('offers scaffolds a node may hold', function () {
    $div = ComponentRegistry::get('div');
    $paragraph = ComponentRegistry::get('paragraph');

    expect(array_map(fn ($definition) => $definition->key, ScaffoldRegistry::forAllowedChildren($div?->allowedChildren ?? [])))->toBe(['table', 'details'])
        ->and(ScaffoldRegistry::forAllowedChildren($paragraph?->allowedChildren ?? []))->toBe([]);
});
