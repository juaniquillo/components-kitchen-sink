<?php

declare(strict_types=1);

use App\DesignSystem\Components\Label;
use Juaniquillo\BaseBackendComponent\Contracts\BackendComponent;

it('renders a label', function () {
    $html = (string) $this->blade('{{ $label }}', [
        'label' => Label::make(designSystemBase())->text('Email'),
    ]);

    expect($html)->toContain('<label')
        ->toContain('Email')
        ->toContain('text-sm font-medium');
});

it('points at a control with for', function () {
    $html = (string) $this->blade('{{ $label }}', [
        'label' => Label::make(designSystemBase())->text('Email')->for('email'),
    ]);

    expect($html)->toContain('for="email"');
});

it('omits for when none is given', function () {
    $html = (string) $this->blade('{{ $label }}', [
        'label' => Label::make(designSystemBase())->text('Email'),
    ]);

    expect($html)->not->toContain('for=');
});

it('merges attributes', function () {
    $html = (string) $this->blade('{{ $label }}', [
        'label' => Label::make(designSystemBase())->text('Email')->attributes(['class' => 'extra']),
    ]);

    expect($html)->toContain('extra')
        ->toContain('text-sm');
});

it('exposes a plain backend component', function () {
    expect(Label::make(designSystemBase())->text('Email')->component())->toBeInstanceOf(BackendComponent::class);
});
