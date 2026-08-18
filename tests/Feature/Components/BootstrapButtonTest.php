<?php

declare(strict_types=1);

use App\Components\Groups\BootstrapButton;

it('has the correct NAME constant', function () {
    expect(BootstrapButton::NAME)->toBe('Bootstrap Button');
});

it('returns 5 button variants', function () {
    expect(BootstrapButton::list())->toHaveCount(5);
});

it('returns default options', function () {
    $options = BootstrapButton::options();

    expect($options->disableFlex)->toBeFalse()
        ->and($options->flexColumn)->toBeFalse()
        ->and($options->flexGap)->toBe('flex-gap-sm');
});

it('renders a primary button', function () {
    $html = (string) $this->blade('{{ $button }}', ['button' => BootstrapButton::primary()]);

    expect($html)->toContain('Primary')
        ->toContain('btn')
        ->toContain('btn-primary')
        ->toContain('<button');
});

it('renders a secondary button', function () {
    $html = (string) $this->blade('{{ $button }}', ['button' => BootstrapButton::secondary()]);

    expect($html)->toContain('Secondary')
        ->toContain('btn-secondary');
});

it('renders a success button', function () {
    $html = (string) $this->blade('{{ $button }}', ['button' => BootstrapButton::success()]);

    expect($html)->toContain('Success')
        ->toContain('btn-success');
});

it('renders a danger button', function () {
    $html = (string) $this->blade('{{ $button }}', ['button' => BootstrapButton::danger()]);

    expect($html)->toContain('Danger')
        ->toContain('btn-danger');
});

it('renders a link button', function () {
    $html = (string) $this->blade('{{ $button }}', ['button' => BootstrapButton::link()]);

    expect($html)->toContain('Link')
        ->toContain('btn-link');
});

it('renders all button variants inside a button element', function () {
    foreach (BootstrapButton::list() as $button) {
        $html = (string) $this->blade('{{ $button }}', ['button' => $button]);

        expect($html)->toContain('<button');
    }
});
