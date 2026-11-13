<?php

declare(strict_types=1);

use App\Components\Groups\BackendComponent\BackendTables;

it('has the correct NAME constant', function () {
    expect(BackendTables::NAME)->toBe('Backend Tables');
});

it('returns 2 table variants', function () {
    expect(BackendTables::list())->toHaveCount(2);
});

it('returns default options', function () {
    $options = BackendTables::options();

    expect($options->disableFlex)->toBeFalse()
        ->and($options->flexColumn)->toBeFalse()
        ->and($options->flexGap)->toBe('flex-gap-sm');
});

it('renders a plain table', function () {
    $html = (string) $this->blade('{{ $table }}', ['table' => BackendTables::plain()]);

    expect($html)->toContain('<table')
        ->toContain('dark:text-zinc-100')
        ->toContain('<th')
        ->toContain('Name')
        ->toContain('<td')
        ->toContain('alice@example.com');
});

it('renders cells with themes, attributes and nested components', function () {
    $html = (string) $this->blade('{{ $table }}', ['table' => BackendTables::cells()]);

    expect($html)->toContain('<table')
        ->toContain('text-teal-900')
        ->toContain('dark:text-teal-100')
        ->toContain('dark:text-cyan-300')
        ->toContain('colspan="3"')
        ->toContain('Two users share the editor role.')
        ->toContain('<caption')
        ->toContain('Team members and their roles.');
});
