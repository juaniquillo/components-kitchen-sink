<?php

declare(strict_types=1);

use App\Components\Groups\Themes\ThemeLayers;

it('has the correct NAME constant', function () {
    expect(ThemeLayers::NAME)->toBe('Base vs Polished');
});

it('returns 2 theme variants', function () {
    expect(ThemeLayers::list())->toHaveCount(2);
});

it('returns default options', function () {
    $options = ThemeLayers::options();

    expect($options->disableFlex)->toBeFalse()
        ->and($options->flexColumn)->toBeFalse()
        ->and($options->flexGap)->toBe('flex-gap-sm');
});

it('renders the austere base theme without dark variants', function () {
    $html = (string) $this->blade('{{ $theme }}', ['theme' => ThemeLayers::base()]);

    expect($html)->toContain('text-green-800')
        ->toContain('text-blue-800')
        ->not->toContain('dark:');
});

it('renders the polished app theme with its dark variants', function () {
    $html = (string) $this->blade('{{ $theme }}', ['theme' => ThemeLayers::polished()]);

    expect($html)->toContain('text-teal-900')
        ->toContain('dark:text-teal-100')
        ->toContain('text-cyan-500')
        ->toContain('dark:text-cyan-300');
});

it('serves the themes section', function () {
    $response = $this->get('/components/themes');

    $response->assertOk()
        ->assertSee('Base vs Polished', false);
});
