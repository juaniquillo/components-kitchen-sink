<?php

declare(strict_types=1);

use App\Components\Groups\BootstrapBadge;

it('has the correct NAME constant', function () {
    expect(BootstrapBadge::NAME)->toBe('Bootstrap Badge');
});

it('returns 9 badge variants', function () {
    expect(BootstrapBadge::list())->toHaveCount(9);
});

it('returns default options', function () {
    $options = BootstrapBadge::options();

    expect($options->disableFlex)->toBeFalse()
        ->and($options->flexColumn)->toBeFalse()
        ->and($options->flexGap)->toBe('flex-gap-sm');
});

it('renders a primary badge', function () {
    $html = (string) $this->blade('{{ $badge }}', ['badge' => BootstrapBadge::primary()]);

    expect($html)->toContain('Primary')
        ->toContain('badge')
        ->toContain('bg-primary');
});

it('renders a secondary badge', function () {
    $html = (string) $this->blade('{{ $badge }}', ['badge' => BootstrapBadge::secondary()]);

    expect($html)->toContain('Secondary')
        ->toContain('bg-secondary');
});

it('renders a success badge', function () {
    $html = (string) $this->blade('{{ $badge }}', ['badge' => BootstrapBadge::success()]);

    expect($html)->toContain('Success')
        ->toContain('bg-success');
});

it('renders a danger badge', function () {
    $html = (string) $this->blade('{{ $badge }}', ['badge' => BootstrapBadge::danger()]);

    expect($html)->toContain('Danger')
        ->toContain('bg-danger');
});

it('renders a warning badge', function () {
    $html = (string) $this->blade('{{ $badge }}', ['badge' => BootstrapBadge::warning()]);

    expect($html)->toContain('Warning')
        ->toContain('bg-warning');
});

it('renders an info badge', function () {
    $html = (string) $this->blade('{{ $badge }}', ['badge' => BootstrapBadge::info()]);

    expect($html)->toContain('Info')
        ->toContain('bg-info');
});

it('renders a light badge', function () {
    $html = (string) $this->blade('{{ $badge }}', ['badge' => BootstrapBadge::light()]);

    expect($html)->toContain('Light')
        ->toContain('bg-light');
});

it('renders a dark badge', function () {
    $html = (string) $this->blade('{{ $badge }}', ['badge' => BootstrapBadge::dark()]);

    expect($html)->toContain('Dark')
        ->toContain('bg-dark');
});

it('renders a link badge', function () {
    $html = (string) $this->blade('{{ $badge }}', ['badge' => BootstrapBadge::link()]);

    expect($html)->toContain('Link')
        ->toContain('text-reset')
        ->not->toContain('bg-');
});

it('renders all badge variants inside a span element', function () {
    foreach (BootstrapBadge::list() as $badge) {
        $html = (string) $this->blade('{{ $badge }}', ['badge' => $badge]);

        expect($html)->toContain('<span');
    }
});
