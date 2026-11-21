<?php

declare(strict_types=1);

use App\DesignSystem\Components\Input;
use Juaniquillo\BaseBackendComponent\Contracts\BackendComponent;

it('renders an email input', function () {
    $html = (string) $this->blade('{{ $input }}', [
        'input' => Input::make(designSystemBase())->name('email')->type('email')->placeholder('you@example.com'),
    ]);

    expect($html)->toContain('<input')
        ->toContain('type="email"')
        ->toContain('name="email"')
        ->toContain('placeholder="you@example.com"')
        ->toContain('border-zinc-400');
});

it('renders required and disabled states', function () {
    $required = (string) $this->blade('{{ $input }}', [
        'input' => Input::make(designSystemBase())->name('email')->required(),
    ]);
    $disabled = (string) $this->blade('{{ $input }}', [
        'input' => Input::make(designSystemBase())->name('nickname')->disabled(),
    ]);

    expect($required)->toContain('required')
        ->and($disabled)->toContain('disabled')
        ->toContain('opacity-50');
});

it('merges interaction attributes', function () {
    $html = (string) $this->blade('{{ $input }}', [
        'input' => Input::make(designSystemBase())->name('email')->attributes(['wire:model.live' => 'email']),
    ]);

    expect($html)->toContain('wire:model.live="email"')
        ->toContain('name="email"');
});

it('passes any html input type through', function () {
    $html = (string) $this->blade('{{ $input }}', [
        'input' => Input::make(designSystemBase())->name('birthday')->type('date'),
    ]);

    expect($html)->toContain('type="date"')
        ->toContain('name="birthday"');
});

it('leaves the name out when none is given', function () {
    $html = (string) $this->blade('{{ $input }}', [
        'input' => Input::make(designSystemBase())->placeholder('Search'),
    ]);

    expect($html)->not->toContain('name=""');
});

it('exposes a plain backend component', function () {
    expect(Input::make(designSystemBase())->name('email')->component())->toBeInstanceOf(BackendComponent::class);
});
