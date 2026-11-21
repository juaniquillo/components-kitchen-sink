<?php

declare(strict_types=1);

use App\DesignSystem\Components\Field;
use App\DesignSystem\Components\Input;
use App\DesignSystem\Components\Label;
use Juaniquillo\BaseBackendComponent\Contracts\BackendComponent;

it('renders a label, an input and an error', function () {
    $html = (string) $this->blade('{{ $field }}', [
        'field' => Field::make(designSystemBase())
            ->label('Email')
            ->input('email')
            ->error('That address is already taken.'),
    ]);

    expect($html)->toContain('Email')
        ->toContain('name="email"')
        ->toContain('That address is already taken.')
        ->toContain('text-red-700');
});

it('accepts components in every slot', function () {
    $html = (string) $this->blade('{{ $field }}', [
        'field' => Field::make(designSystemBase())
            ->label('Email')
            ->input(Input::make(designSystemBase())->name('email')->type('email')->component()),
    ]);

    expect($html)->toContain('type="email"')
        ->toContain('name="email"');
});

it('associates the label and the error with the input', function () {
    $html = (string) $this->blade('{{ $field }}', [
        'field' => Field::make(designSystemBase())->label('Email')->input('email')->error('Taken.'),
    ]);

    expect($html)->toContain('for="email"')
        ->toContain('id="email"')
        ->toContain('id="email-error"')
        ->toContain('aria-describedby="email-error"')
        ->toContain('aria-invalid="true"');
});

it('prefers an explicit id over the derived one', function () {
    $html = (string) $this->blade('{{ $field }}', [
        'field' => Field::make(designSystemBase())->label('Email')->input('nickname')->id('user-email'),
    ]);

    expect($html)->toContain('for="user-email"')
        ->toContain('id="user-email"')
        ->not->toContain('for="nickname"');
});

it('forwards required to a built input', function () {
    $html = (string) $this->blade('{{ $field }}', [
        'field' => Field::make(designSystemBase())->label('Email')->input('email')->required(),
    ]);

    expect($html)->toContain('required');
});

it('wires a passed label and input rather than trusting them', function () {
    $html = (string) $this->blade('{{ $field }}', [
        'field' => Field::make(designSystemBase())
            ->label(Label::make(designSystemBase())->text('Email')->for('other')->component())
            ->input(Input::make(designSystemBase())->name('email')->component())
            ->error('Taken.'),
    ]);

    // Field owns the association: a label pointing somewhere else would
    // leave the control unnamed, so its for is rewritten.
    expect($html)->toContain('for="email"')
        ->toContain('id="email"')
        ->toContain('aria-describedby="email-error"')
        ->not->toContain('for="other"');
});

it('wires the label to the id a passed-in input already carries', function () {
    $html = (string) $this->blade('{{ $field }}', [
        'field' => Field::make(designSystemBase())
            ->label('Email')
            ->input(Input::make(designSystemBase())->name('email')->attributes(['id' => 'signup-email'])->component())
            ->error('Taken.'),
    ]);

    expect($html)->toContain('for="signup-email"')
        ->toContain('id="signup-email"')
        ->toContain('id="signup-email-error"');
});

it('wires the error of a passed-in input', function () {
    $html = (string) $this->blade('{{ $field }}', [
        'field' => Field::make(designSystemBase())
            ->label('Email')
            ->input(Input::make(designSystemBase())->name('email')->component())
            ->error('Taken.'),
    ]);

    expect($html)->toContain('aria-describedby="email-error"')
        ->toContain('aria-invalid="true"')
        ->toContain('id="email-error"');
});

it('gives a passed-in input the id the field was told to use', function () {
    $html = (string) $this->blade('{{ $field }}', [
        'field' => Field::make(designSystemBase())
            ->label('Email')
            ->input(Input::make(designSystemBase())->name('email')->component())
            ->id('billing-email'),
    ]);

    expect($html)->toContain('for="billing-email"')
        ->toContain('id="billing-email"');
});

it('marks a passed-in input required', function () {
    $html = (string) $this->blade('{{ $field }}', [
        'field' => Field::make(designSystemBase())
            ->label('Email')
            ->input(Input::make(designSystemBase())->name('email')->component())
            ->required(),
    ]);

    expect($html)->toContain('required');
});

it('exposes a plain backend component', function () {
    expect(Field::make(designSystemBase())->label('Email')->input('email')->component())->toBeInstanceOf(BackendComponent::class);
});
