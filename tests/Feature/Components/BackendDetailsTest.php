<?php

declare(strict_types=1);

use App\Components\Groups\BackendComponent\BackendDetails;

it('has the correct NAME constant', function () {
    expect(BackendDetails::NAME)->toBe('Backend Details');
});

it('returns 3 details variants', function () {
    expect(BackendDetails::list())->toHaveCount(3);
});

it('returns the group options', function () {
    $options = BackendDetails::options();

    expect($options->disableFlex)->toBeFalse()
        ->and($options->flexColumn)->toBeTrue()
        ->and($options->flexGap)->toBe('flex-gap')
        ->and($options->justifyCenter)->toBeFalse();
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
        ->toContain('text-cyan-500');
});
