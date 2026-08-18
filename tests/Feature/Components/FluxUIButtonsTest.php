<?php

declare(strict_types=1);

use App\Components\Groups\FluxUIButtons;

it('has the correct NAME constant', function () {
    expect(FluxUIButtons::NAME)->toBe('Flux UI Buttons');
});

it('returns 6 button variants', function () {
    expect(FluxUIButtons::list())->toHaveCount(6);
});

it('returns default options', function () {
    $options = FluxUIButtons::options();

    expect($options->disableFlex)->toBeFalse()
        ->and($options->flexColumn)->toBeFalse()
        ->and($options->flexGap)->toBe('flex-gap-sm');
});

it('renders a primary button', function () {
    $html = (string) $this->blade('{{ $button }}', ['button' => FluxUIButtons::primary()]);

    expect($html)->toContain('data-flux-button')
        ->toContain('Primary');
});

it('renders a danger button', function () {
    $html = (string) $this->blade('{{ $button }}', ['button' => FluxUIButtons::danger()]);

    expect($html)->toContain('data-flux-button')
        ->toContain('Danger');
});

it('renders a ghost button', function () {
    $html = (string) $this->blade('{{ $button }}', ['button' => FluxUIButtons::ghost()]);

    expect($html)->toContain('data-flux-button')
        ->toContain('Ghost');
});

it('renders an outline button', function () {
    $html = (string) $this->blade('{{ $button }}', ['button' => FluxUIButtons::outline()]);

    expect($html)->toContain('data-flux-button')
        ->toContain('Outline');
});

it('renders a small button', function () {
    $html = (string) $this->blade('{{ $button }}', ['button' => FluxUIButtons::sizes()]);

    expect($html)->toContain('data-flux-button')
        ->toContain('Small')
        ->toContain('sm');
});

it('renders a button with icon', function () {
    $html = (string) $this->blade('{{ $button }}', ['button' => FluxUIButtons::withIcon()]);

    expect($html)->toContain('data-flux-button')
        ->toContain('Add item');
});

it('renders all button variants', function () {
    foreach (FluxUIButtons::list() as $button) {
        $html = (string) $this->blade('{{ $button }}', ['button' => $button]);

        expect($html)->toContain('data-flux-button');
    }
});
