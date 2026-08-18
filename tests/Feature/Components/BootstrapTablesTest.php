<?php

declare(strict_types=1);

use App\Components\Groups\BootstrapTables;

it('has the correct NAME constant', function () {
    expect(BootstrapTables::NAME)->toBe('Bootstrap Tables');
});

it('returns 7 table variants', function () {
    expect(BootstrapTables::list())->toHaveCount(7);
});

it('sets disable-flex option', function () {
    expect(BootstrapTables::options()->disableFlex)->toBeTrue();
});

it('renders a basic table with thead and tbody', function () {
    $html = (string) $this->blade('{{ $table }}', ['table' => BootstrapTables::basic()]);

    expect($html)->toContain('<table')
        ->toContain('class="table"')
        ->toContain('<thead')
        ->toContain('<tbody')
        ->toContain('Name')
        ->toContain('Email')
        ->toContain('Role')
        ->toContain('Alice')
        ->toContain('alice@example.com')
        ->toContain('Admin');
});

it('renders a striped table', function () {
    $html = (string) $this->blade('{{ $table }}', ['table' => BootstrapTables::striped()]);

    expect($html)->toContain('table-striped')
        ->toContain('Diana');
});

it('renders a hover table', function () {
    $html = (string) $this->blade('{{ $table }}', ['table' => BootstrapTables::hover()]);

    expect($html)->toContain('table-hover');
});

it('renders a compact table', function () {
    $html = (string) $this->blade('{{ $table }}', ['table' => BootstrapTables::compact()]);

    expect($html)->toContain('table-sm');
});

it('renders a table with caption', function () {
    $html = (string) $this->blade('{{ $table }}', ['table' => BootstrapTables::withCaption()]);

    expect($html)->toContain('<caption')
        ->toContain('List of team members');
});

it('renders a table with action buttons', function () {
    $html = (string) $this->blade('{{ $table }}', ['table' => BootstrapTables::withActions()]);

    expect($html)->toContain('Edit')
        ->toContain('Delete')
        ->toContain('btn-outline-primary')
        ->toContain('btn-outline-danger')
        ->toContain('d-flex gap-1');
});

it('renders a dark header table', function () {
    $html = (string) $this->blade('{{ $table }}', ['table' => BootstrapTables::darkHeader()]);

    expect($html)->toContain('table-dark');
});

it('renders all table variants without errors', function () {
    foreach (BootstrapTables::list() as $table) {
        $html = (string) $this->blade('{{ $table }}', ['table' => $table]);

        expect($html)->toContain('<table');
    }
});
