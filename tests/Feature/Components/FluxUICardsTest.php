<?php

declare(strict_types=1);

use App\Components\Groups\FluxUICards;

it('returns the correct number of card variants', function () {
    expect(FluxUICards::list())->toHaveCount(6);
});

it('has the correct NAME constant', function () {
    expect(FluxUICards::NAME)->toBe('Flux UI Cards');
});

it('sets disable-flex and width options', function () {
    $options = FluxUICards::options();

    expect($options['disable-flex'])->toBeTrue()
        ->and($options['width'])->toBe('component-box-md-width');
});

it('renders a simple card with heading and text', function () {
    $html = (string) $this->blade('{{ $card }}', ['card' => FluxUICards::simple()]);

    expect($html)->toContain('data-flux-card')
        ->toContain('Simple card')
        ->toContain('basic Flux card with a heading');
});

it('renders a card with header and separator', function () {
    $html = (string) $this->blade('{{ $card }}', ['card' => FluxUICards::withHeader()]);

    expect($html)->toContain('data-flux-card')
        ->toContain('Card with header')
        ->toContain('data-flux-separator')
        ->toContain('Content separated from the heading');
});

it('renders a card with footer buttons', function () {
    $html = (string) $this->blade('{{ $card }}', ['card' => FluxUICards::withFooter()]);

    expect($html)->toContain('data-flux-card')
        ->toContain('Card with footer')
        ->toContain('Cancel')
        ->toContain('Save')
        ->toContain('data-flux-separator')
        ->toContain('data-flux-button');
});

it('renders a small card with compact padding', function () {
    $html = (string) $this->blade('{{ $card }}', ['card' => FluxUICards::small()]);

    expect($html)->toContain('data-flux-card')
        ->toContain('p-4')
        ->toContain('rounded-lg')
        ->toContain('Small card')
        ->toContain('Compact padding variant');
});

it('renders an interactive card with click handler and hover classes', function () {
    $html = (string) $this->blade('{{ $card }}', ['card' => FluxUICards::interactive()]);

    expect($html)->toContain('data-flux-card')
        ->toContain('cursor-pointer')
        ->toContain('hover:shadow-md')
        ->toContain('x-on:click')
        ->toContain('Interactive card')
        ->toContain('Click this card');
});

it('renders a card with an image at the top', function () {
    $html = (string) $this->blade('{{ $card }}', ['card' => FluxUICards::withImage()]);

    expect($html)->toContain('data-flux-card')
        ->toContain('<img')
        ->toContain('https://placehold.co/600x200')
        ->toContain('alt="Placeholder image"')
        ->toContain('Card with image')
        ->toContain('An image sits at the top');
});

it('renders all cards via the static list method', function () {
    $cards = FluxUICards::list();

    foreach ($cards as $card) {
        $html = (string) $this->blade('{{ $card }}', ['card' => $card]);

        expect($html)->toContain('data-flux-card');
    }
});
