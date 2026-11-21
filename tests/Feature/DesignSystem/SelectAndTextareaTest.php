<?php

declare(strict_types=1);

use App\DesignSystem\Components\Select;
use App\DesignSystem\Components\Textarea;
use Juaniquillo\BaseBackendComponent\Contracts\BackendComponent;

it('renders a select with its options', function () {
    $html = (string) $this->blade('{{ $select }}', [
        'select' => Select::make(designSystemBase())->name('country')->options([
            '' => 'Choose a country',
            'es' => 'Spain',
            'fr' => 'France',
        ]),
    ]);

    expect($html)->toContain('<select')
        ->toContain('name="country"')
        ->toContain('<option')
        ->toContain('value="es"')
        ->toContain('Spain');
});

it('marks the selected option', function () {
    $html = (string) $this->blade('{{ $select }}', [
        'select' => Select::make(designSystemBase())->name('country')->options(['es' => 'Spain', 'fr' => 'France'])->value('fr'),
    ]);

    expect($html)->toContain('value="fr" selected="selected"');
});

it('renders a disabled select', function () {
    $html = (string) $this->blade('{{ $select }}', [
        'select' => Select::make(designSystemBase())->name('country')->options(['' => 'Not available'])->disabled(),
    ]);

    expect($html)->toContain('disabled')
        ->toContain('opacity-50');
});

it('renders a textarea with its content', function () {
    $html = (string) $this->blade('{{ $textarea }}', [
        'textarea' => Textarea::make(designSystemBase())->name('notes')->rows(4)->value('Ship it'),
    ]);

    expect($html)->toContain('<textarea')
        ->toContain('name="notes"')
        ->toContain('rows="4"')
        ->toContain('Ship it');
});

it('keeps the look on a disabled textarea', function () {
    $html = (string) $this->blade('{{ $textarea }}', [
        'textarea' => Textarea::make(designSystemBase())->name('notes')->disabled(),
    ]);

    expect($html)->toContain('opacity-50')
        ->toContain('bg-white');
});

it('exposes plain backend components', function () {
    expect(Select::make(designSystemBase())->name('a')->component())->toBeInstanceOf(BackendComponent::class)
        ->and(Textarea::make(designSystemBase())->name('b')->component())->toBeInstanceOf(BackendComponent::class);
});
