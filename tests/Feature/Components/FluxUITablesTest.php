<?php

declare(strict_types=1);

use App\Components\Groups\FluxUITables;

it('has the correct NAME constant', function () {
    expect(FluxUITables::NAME)->toBe('Flux UI Tables');
});

it('returns 7 table variants', function () {
    expect(FluxUITables::list())->toHaveCount(7);
});

it('sets disable-flex option', function () {
    expect(FluxUITables::options()->disableFlex)->toBeTrue();
});

it('renders a basic table with columns and rows', function () {
    $html = (string) $this->blade('{{ $table }}', ['table' => FluxUITables::basic()]);

    expect($html)->toContain('data-flux-table')
        ->toContain('data-flux-columns')
        ->toContain('data-flux-rows')
        ->toContain('data-flux-column')
        ->toContain('data-flux-row')
        ->toContain('data-flux-cell')
        ->toContain('Name')
        ->toContain('Email')
        ->toContain('Role')
        ->toContain('Alice')
        ->toContain('alice@example.com')
        ->toContain('Admin');
});

it('renders a striped table', function () {
    $html = (string) $this->blade('{{ $table }}', ['table' => FluxUITables::striped()]);

    expect($html)->toContain('data-flux-table')
        ->toContain('even:bg-zinc-50')
        ->toContain('Diana');
});

it('renders a compact table', function () {
    $html = (string) $this->blade('{{ $table }}', ['table' => FluxUITables::compact()]);

    expect($html)->toContain('data-flux-table')
        ->toContain('py-1.5')
        ->toContain('text-xs');
});

it('renders a table with action buttons', function () {
    $html = (string) $this->blade('{{ $table }}', ['table' => FluxUITables::withActions()]);

    expect($html)->toContain('data-flux-table')
        ->toContain('Edit')
        ->toContain('Delete')
        ->toContain('data-flux-button');
});

it('renders a table with strong cells', function () {
    $html = (string) $this->blade('{{ $table }}', ['table' => FluxUITables::strongCells()]);

    expect($html)->toContain('data-flux-table')
        ->toContain('font-medium');
});

it('renders a table with aligned columns', function () {
    $html = (string) $this->blade('{{ $table }}', ['table' => FluxUITables::aligned()]);

    expect($html)->toContain('data-flux-table')
        ->toContain('text-start')
        ->toContain('text-center')
        ->toContain('text-end');
});

it('renders a hover table', function () {
    $html = (string) $this->blade('{{ $table }}', ['table' => FluxUITables::hover()]);

    expect($html)->toContain('data-flux-table')
        ->toContain('hover:bg-zinc-100');
});

it('renders all table variants', function () {
    foreach (FluxUITables::list() as $table) {
        $html = (string) $this->blade('{{ $table }}', ['table' => $table]);

        expect($html)->toContain('data-flux-table');
    }
});
