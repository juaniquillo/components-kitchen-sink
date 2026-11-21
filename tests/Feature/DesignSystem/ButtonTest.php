<?php

declare(strict_types=1);

use App\DesignSystem\Components\Button;
use App\DesignSystem\Theme\ButtonVariant;
use App\DesignSystem\Theme\Size;
use Juaniquillo\BaseBackendComponent\Contracts\BackendComponent;

it('renders a primary button', function () {
    $html = (string) $this->blade('{{ $button }}', [
        'button' => Button::make(designSystemBase())->label('Save')->variant(ButtonVariant::Primary),
    ]);

    expect($html)->toContain('<button')
        ->toContain('Save')
        ->toContain('bg-zinc-900')
        ->toContain('text-white')
        ->toContain('type="button"');
});

it('renders button sizes', function () {
    $small = (string) $this->blade('{{ $button }}', [
        'button' => Button::make(designSystemBase())->label('Small')->size(Size::Sm),
    ]);
    $large = (string) $this->blade('{{ $button }}', [
        'button' => Button::make(designSystemBase())->label('Large')->size(Size::Lg),
    ]);

    expect($small)->toContain('px-2 py-1 text-xs')
        ->and($large)->toContain('px-4 py-2 text-base');
});

it('renders a disabled button', function () {
    $html = (string) $this->blade('{{ $button }}', [
        'button' => Button::make(designSystemBase())->label('Save')->disabled(),
    ]);

    expect($html)->toContain('disabled')
        ->toContain('opacity-50');
});

it('merges interaction attributes without replacing the design', function () {
    $html = (string) $this->blade('{{ $button }}', [
        'button' => Button::make(designSystemBase())->label('Save')->variant(ButtonVariant::Primary)->attributes(['wire:click' => 'save']),
    ]);

    expect($html)->toContain('wire:click="save"')
        ->toContain('bg-zinc-900');
});

it('exposes a plain backend component for anything the slots do not cover', function () {
    $component = Button::make(designSystemBase())->label('Save')->component();

    expect($component)->toBeInstanceOf(BackendComponent::class);

    $html = (string) $this->blade('{{ $button }}', [
        'button' => $component->setAttribute('id', 'save-button'),
    ]);

    expect($html)->toContain('id="save-button"');
});
