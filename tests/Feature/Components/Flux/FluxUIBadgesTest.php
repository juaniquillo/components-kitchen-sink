<?php

declare(strict_types=1);

use App\Components\Groups\Flux\FluxUIBadges;
use App\Components\Options\Enums\Items;
use App\Components\Options\Enums\Justify;

it('has the correct NAME constant', function () {
    expect(FluxUIBadges::NAME)->toBe('Flux UI Badges');
});

it('returns 12 badge examples', function () {
    expect(FluxUIBadges::list())->toHaveCount(12);
});

it('returns default options', function () {
    $options = FluxUIBadges::options();

    expect($options->container->disableFlex)->toBeFalse()
        ->and($options->container->justify)->toBe(Justify::Center)
        ->and($options->container->items)->toBe(Items::Center);
});

it('renders every badge', function () {
    foreach (FluxUIBadges::list() as $badge) {
        $html = (string) $this->blade('{{ $badge }}', ['badge' => $badge]);

        expect($html)->toContain('data-flux-badge');
    }
});

it('renders a solid badge', function () {
    $html = (string) $this->blade('{{ $badge }}', ['badge' => FluxUIBadges::solid()]);

    expect($html)->toContain('data-flux-badge')
        ->toContain('bg-green-500')
        ->toContain('Solid');
});

it('renders a rounded badge', function () {
    $html = (string) $this->blade('{{ $badge }}', ['badge' => FluxUIBadges::pill()]);

    expect($html)->toContain('rounded-full')
        ->toContain('Pill');
});

it('renders a small badge', function () {
    $html = (string) $this->blade('{{ $badge }}', ['badge' => FluxUIBadges::small()]);

    expect($html)->toContain('text-xs')
        ->toContain('Small');
});

it('renders badges with a leading icon', function () {
    $html = (string) $this->blade('{{ $badge }}', ['badge' => FluxUIBadges::withIcon()]);

    expect($html)->toContain('data-flux-badge-icon')
        ->toContain('Verified');
});

it('renders a badge with a trailing icon', function () {
    $html = (string) $this->blade('{{ $badge }}', ['badge' => FluxUIBadges::withTrailingIcon()]);

    expect($html)->toContain('data-flux-badge-icon:trailing')
        ->toContain('Next');
});

it('renders a dismissible badge', function () {
    $html = (string) $this->blade('{{ $badge }}', ['badge' => FluxUIBadges::dismissible()]);

    expect($html)->toContain('data-flux-badge-close')
        ->toContain('Draft')
        ->and(substr_count($html, 'Draft'))->toBe(1);
});
