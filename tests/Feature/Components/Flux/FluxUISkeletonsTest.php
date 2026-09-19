<?php

declare(strict_types=1);

use App\Components\Groups\Flux\FluxUISkeletons;

it('has the correct NAME constant', function () {
    expect(FluxUISkeletons::NAME)->toBe('Flux UI Skeletons');
});

it('returns 5 skeleton variants', function () {
    expect(FluxUISkeletons::list())->toHaveCount(5);
});

it('sets disable-flex option', function () {
    expect(FluxUISkeletons::options()->disableFlex)->toBeTrue();
});

it('renders a basic skeleton', function () {
    $html = (string) $this->blade('{{ $skeleton }}', ['skeleton' => FluxUISkeletons::basic()]);

    expect($html)->toContain('data-flux-skeleton')
        ->toContain('w-48')
        ->toContain('h-4');
});

it('renders a pulse skeleton', function () {
    $html = (string) $this->blade('{{ $skeleton }}', ['skeleton' => FluxUISkeletons::pulse()]);

    expect($html)->toContain('data-flux-skeleton')
        ->toContain('animate-pulse');
});

it('renders a shimmer skeleton', function () {
    $html = (string) $this->blade('{{ $skeleton }}', ['skeleton' => FluxUISkeletons::shimmer()]);

    expect($html)->toContain('data-flux-skeleton')
        ->toContain('flux-shimmer');
});

it('renders text lines skeleton group', function () {
    $html = (string) $this->blade('{{ $skeleton }}', ['skeleton' => FluxUISkeletons::textLines()]);

    expect($html)->toContain('data-flux-skeleton')
        ->toContain('animate-pulse');
});

it('renders a card skeleton with multiple skeleton children', function () {
    $html = (string) $this->blade('{{ $skeleton }}', ['skeleton' => FluxUISkeletons::cardSkeleton()]);

    expect($html)->toContain('data-flux-card')
        ->toContain('data-flux-skeleton')
        ->toContain('w-full')
        ->toContain('h-40');
});

it('renders all skeleton variants', function () {
    foreach (FluxUISkeletons::list() as $skeleton) {
        $html = (string) $this->blade('{{ $skeleton }}', ['skeleton' => $skeleton]);

        expect($html)->toContain('data-flux-skeleton');
    }
});
