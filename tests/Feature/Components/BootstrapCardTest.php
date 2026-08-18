<?php

declare(strict_types=1);

use App\Components\Groups\BootstrapCard;

it('has the correct NAME constant', function () {
    expect(BootstrapCard::NAME)->toBe('Bootstrap Cards');
});

it('returns 2 card variants', function () {
    expect(BootstrapCard::list())->toHaveCount(2);
});

it('returns default options', function () {
    $options = BootstrapCard::options();

    expect($options->disableFlex)->toBeFalse()
        ->and($options->flexColumn)->toBeFalse()
        ->and($options->flexGap)->toBe('flex-gap-sm');
});

it('renders a simple card with image, title and content', function () {
    $html = (string) $this->blade('{{ $card }}', ['card' => BootstrapCard::simple()]);

    expect($html)->toContain('card')
        ->toContain('card-img-top')
        ->toContain('https://placehold.co/400x200')
        ->toContain('card-body')
        ->toContain('card-title')
        ->toContain('Simple card')
        ->toContain('card-text')
        ->toContain('This is simple content');
});

it('renders a card without image with title, subtitle and content', function () {
    $html = (string) $this->blade('{{ $card }}', ['card' => BootstrapCard::noImage()]);

    expect($html)->toContain('card')
        ->toContain('card-body')
        ->toContain('card-title')
        ->toContain('Simple card without image')
        ->toContain('card-subtitle')
        ->toContain('Subtitle included')
        ->toContain('card-text')
        ->not->toContain('card-img-top');
});

it('renders all card variants', function () {
    foreach (BootstrapCard::list() as $card) {
        $html = (string) $this->blade('{{ $card }}', ['card' => $card]);

        expect($html)->toContain('card');
    }
});
