<?php

declare(strict_types=1);

use App\Components\Groups\BackendComponent\BackendDetails;
use App\Components\Options\Enums\Items;
use App\Components\Options\Enums\Justify;

it('has the correct NAME constant', function () {
    expect(BackendDetails::NAME)->toBe('Backend Details');
});

it('returns 4 details variants', function () {
    expect(BackendDetails::list())->toHaveCount(4);
});

it('returns the group options', function () {
    $options = BackendDetails::options();

    expect($options->container->disableFlex)->toBeFalse()
        ->and($options->container->flexColumn)->toBeTrue()
        ->and($options->container->flexGap)->toBe('flex-gap')
        ->and($options->container->justify)->toBe(Justify::Start)
        ->and($options->container->items)->toBe(Items::Stretch);
});

it('renders a closed details', function () {
    $html = (string) $this->blade('{{ $details }}', ['details' => BackendDetails::closed()]);

    expect($html)->toContain('<details')
        ->toContain('<summary')
        ->toContain('What is this page?')
        ->not->toContain('open="open"');
});

it('renders an open details', function () {
    $html = (string) $this->blade('{{ $details }}', ['details' => BackendDetails::open()]);

    expect($html)->toContain('<details')
        ->toContain('open="open"')
        ->toContain('What is this page?');
});

it('renders a themed summary', function () {
    $html = (string) $this->blade('{{ $details }}', ['details' => BackendDetails::themed()]);

    expect($html)->toContain('<summary')
        ->toContain('px-4')
        ->toContain('bg-blue-700')
        ->toContain('rounded-sm')
        ->toContain('text-white')
        ->toContain('p-2');
});

it('renders a named accordion group', function () {
    $html = (string) $this->blade('{{ $details }}', ['details' => BackendDetails::named()]);

    expect(substr_count($html, 'name="details"'))->toBe(3)
        ->and($html)->toContain('What is this page 2?')
        ->and($html)->toContain('cursor-pointer');
});
