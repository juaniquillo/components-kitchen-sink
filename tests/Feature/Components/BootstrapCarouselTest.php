<?php

declare(strict_types=1);

use App\Components\Groups\BootstrapCarousel;

it('has the correct NAME constant', function () {
    expect(BootstrapCarousel::NAME)->toBe('Bootstrap Carousel');
});

it('returns 3 carousel variants', function () {
    expect(BootstrapCarousel::list())->toHaveCount(3);
});

it('returns default options', function () {
    $options = BootstrapCarousel::options();

    expect($options->disableFlex)->toBeFalse()
        ->and($options->flexColumn)->toBeFalse()
        ->and($options->flexGap)->toBe('flex-gap-sm');
});

it('renders a default carousel with slides', function () {
    $html = (string) $this->blade('{{ $carousel }}', ['carousel' => BootstrapCarousel::default()]);

    expect($html)->toContain('carousel')
        ->toContain('carousel-inner')
        ->toContain('carousel-item')
        ->toContain('active')
        ->toContain('img-fluid')
        ->toContain('https://placehold.co/400x200');
});

it('renders a carousel with indicators', function () {
    $html = (string) $this->blade('{{ $carousel }}', ['carousel' => BootstrapCarousel::withIndicators()]);

    expect($html)->toContain('carousel-indicators')
        ->toContain('data-bs-slide-to')
        ->toContain('aria-current');
});

it('renders a carousel with prev/next controls', function () {
    $html = (string) $this->blade('{{ $carousel }}', ['carousel' => BootstrapCarousel::withControls()]);

    expect($html)->toContain('carousel-control-prev')
        ->toContain('carousel-control-next')
        ->toContain('data-bs-slide="prev"')
        ->toContain('data-bs-slide="next"')
        ->toContain('visually-hidden');
});

it('uses unique carousel IDs', function () {
    $default = (string) $this->blade('{{ $carousel }}', ['carousel' => BootstrapCarousel::default()]);
    $indicators = (string) $this->blade('{{ $carousel }}', ['carousel' => BootstrapCarousel::withIndicators()]);

    expect($default)->toContain('id="carousel-default"')
        ->and($indicators)->toContain('id="carousel-indicators"');
});

it('renders all carousel variants', function () {
    foreach (BootstrapCarousel::list() as $carousel) {
        $html = (string) $this->blade('{{ $carousel }}', ['carousel' => $carousel]);

        expect($html)->toContain('carousel');
    }
});
