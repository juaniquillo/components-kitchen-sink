<?php

declare(strict_types=1);

use App\DesignSystem\Components\Button;
use App\DesignSystem\Components\Checkbox;
use App\DesignSystem\Components\Field;
use App\DesignSystem\Components\Form;
use Juaniquillo\BaseBackendComponent\Contracts\BackendComponent;

it('renders a form with its fields and actions', function () {
    $html = (string) $this->blade('{{ $form }}', [
        'form' => Form::make(designSystemBase())
            ->action('/subscribe')
            ->fields([
                Field::make(designSystemBase())->label('Email')->input('form-email'),
                Checkbox::make(designSystemBase())->name('form-terms')->label('I accept'),
            ])
            ->actions(Button::make(designSystemBase())->label('Subscribe')->type('submit')->component()),
    ]);

    expect($html)->toContain('<form')
        ->toContain('action="/subscribe"')
        ->toContain('method="POST"')
        ->toContain('form-email')
        ->toContain('type="submit"')
        ->toContain('Subscribe');
});

it('emits plain html unless the framework extras are asked for', function () {
    $plain = (string) $this->blade('{{ $form }}', [
        'form' => Form::make(designSystemBase())->fields([]),
    ]);

    expect($plain)->toContain('<form')
        ->not->toContain('_token')
        ->not->toContain('_method');
});

it('can emit the framework extras', function () {
    $html = (string) $this->blade('{{ $form }}', [
        'form' => Form::make(designSystemBase())->method('patch')->csrf()->methodInput()->fields([]),
    ]);

    expect($html)->toContain('_token')
        ->toContain('_method')
        ->toContain('value="PATCH"');
});

it('exposes a plain backend component', function () {
    expect(Form::make(designSystemBase())->fields([])->component())->toBeInstanceOf(BackendComponent::class);
});
