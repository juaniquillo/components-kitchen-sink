<?php

declare(strict_types=1);

use App\Components\Groups\Slate\SlateCarousel;

it('has the correct NAME constant', function () {
    expect(SlateCarousel::NAME)->toBe('Carousel');
});

it('returns 2 carousel variants', function () {
    expect(SlateCarousel::list())->toHaveCount(2);
});

it('returns default options', function () {
    $options = SlateCarousel::options();

    expect($options->disableFlex)->toBeFalse()
        ->and($options->flexColumn)->toBeFalse()
        ->and($options->flexGap)->toBe('flex-gap-sm');
});

it('renders a simple carousel with slides and controls', function () {
    $html = (string) $this->blade('{{ $carousel }}', ['carousel' => SlateCarousel::simple()]);

    expect($html)->toContain('data-slot="carousel"')
        ->toContain('data-orientation="horizontal"')
        ->toContain('data-slot="carousel-content"')
        ->toContain('data-slot="carousel-item"')
        ->toContain('data-slot="card"')
        ->toContain('Slide 1')
        ->toContain('Slide 2')
        ->toContain('Slide 3')
        ->toContain('data-slot="carousel-previous"')
        ->toContain('data-slot="carousel-next"');
});

it('renders a carousel with image slides', function () {
    $html = (string) $this->blade('{{ $carousel }}', ['carousel' => SlateCarousel::withImages()]);

    expect($html)->toContain('data-slot="carousel"')
        ->toContain('https://placehold.co/400x200/')
        ->toContain('rounded-sm overflow-hidden')
        ->toContain('<img alt=""')
        ->not->toContain('Slide 1');
});

it('renders all carousel variants without errors', function () {
    foreach (SlateCarousel::list() as $carousel) {
        $html = (string) $this->blade('{{ $carousel }}', ['carousel' => $carousel]);

        expect($html)->toContain('data-slot="carousel"');
    }
});
