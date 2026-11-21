<?php

declare(strict_types=1);

use App\DesignSystem\Components\Checkbox;
use App\DesignSystem\Components\Radio;
use Juaniquillo\BaseBackendComponent\Contracts\BackendComponent;

it('wraps the control in a label, so no id is needed', function () {
    $html = (string) $this->blade('{{ $checkbox }}', [
        'checkbox' => Checkbox::make(designSystemBase())->name('terms')->label('I accept'),
    ]);

    expect($html)->toContain('<label')
        ->toContain('type="checkbox"')
        ->toContain('name="terms"')
        ->toContain('I accept')
        ->not->toContain('for=');
});

it('renders checked, disabled and required states', function () {
    $checked = (string) $this->blade('{{ $checkbox }}', [
        'checkbox' => Checkbox::make(designSystemBase())->name('terms')->label('Yes')->checked(),
    ]);
    $disabled = (string) $this->blade('{{ $checkbox }}', [
        'checkbox' => Checkbox::make(designSystemBase())->name('locked')->label('No')->disabled(),
    ]);
    $required = (string) $this->blade('{{ $checkbox }}', [
        'checkbox' => Checkbox::make(designSystemBase())->name('terms')->label('Yes')->required(),
    ]);

    expect($checked)->toContain('checked')
        ->and($disabled)->toContain('disabled')
        ->and($required)->toContain('required');
});

it('renders radios that share a group and carry their value', function () {
    $free = (string) $this->blade('{{ $radio }}', [
        'radio' => Radio::make(designSystemBase())->name('plan')->value('free')->label('Free')->checked(),
    ]);
    $pro = (string) $this->blade('{{ $radio }}', [
        'radio' => Radio::make(designSystemBase())->name('plan')->value('pro')->label('Pro'),
    ]);

    expect($free)->toContain('type="radio"')
        ->toContain('name="plan"')
        ->toContain('value="free"')
        ->toContain('checked')
        ->and($pro)->toContain('value="pro"')
        ->not->toContain('checked');
});

it('exposes plain backend components', function () {
    expect(Checkbox::make(designSystemBase())->name('terms')->component())->toBeInstanceOf(BackendComponent::class)
        ->and(Radio::make(designSystemBase())->name('plan')->value('a')->component())->toBeInstanceOf(BackendComponent::class);
});
