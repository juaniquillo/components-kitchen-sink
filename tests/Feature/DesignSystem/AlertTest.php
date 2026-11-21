<?php

declare(strict_types=1);

use App\DesignSystem\Components\Alert;
use App\DesignSystem\Theme\AlertVariant;
use Juaniquillo\BaseBackendComponent\Contracts\BackendComponent;

it('renders every severity', function () {
    foreach (AlertVariant::cases() as $variant) {
        $html = (string) $this->blade('{{ $alert }}', [
            'alert' => Alert::make(designSystemBase())->variant($variant)->title('Title')->body('Body'),
        ]);

        expect($html)->toContain('Title')->toContain('Body');
    }
});

it('renders severity styles', function () {
    $info = (string) $this->blade('{{ $alert }}', [
        'alert' => Alert::make(designSystemBase())->variant(AlertVariant::Info)->title('Heads up'),
    ]);
    $danger = (string) $this->blade('{{ $alert }}', [
        'alert' => Alert::make(designSystemBase())->variant(AlertVariant::Danger)->title('Failed'),
    ]);

    expect($info)->toContain('bg-blue-50')
        ->and($danger)->toContain('bg-red-50');
});

it('omits parts that are not set', function () {
    $html = (string) $this->blade('{{ $alert }}', [
        'alert' => Alert::make(designSystemBase())->variant(AlertVariant::Info)->title('Heads up'),
    ]);

    expect($html)->toContain('Heads up');
});

it('stays silent unless asked to be live', function () {
    $html = (string) $this->blade('{{ $alert }}', [
        'alert' => Alert::make(designSystemBase())->variant(AlertVariant::Danger)->title('Failed'),
    ]);

    expect($html)->not->toContain('role=');
});

it('announces failures assertively and the rest politely', function () {
    $danger = (string) $this->blade('{{ $alert }}', [
        'alert' => Alert::make(designSystemBase())->variant(AlertVariant::Danger)->title('Failed')->live(),
    ]);
    $info = (string) $this->blade('{{ $alert }}', [
        'alert' => Alert::make(designSystemBase())->variant(AlertVariant::Info)->title('Heads up')->live(),
    ]);

    expect($danger)->toContain('role="alert"')
        ->and($info)->toContain('role="status"');
});

it('lets an explicit role win over the live default', function () {
    $html = (string) $this->blade('{{ $alert }}', [
        'alert' => Alert::make(designSystemBase())->variant(AlertVariant::Info)->live()->attributes(['role' => 'note']),
    ]);

    expect($html)->toContain('role="note"');
});

it('exposes a plain backend component', function () {
    expect(Alert::make(designSystemBase())->variant(AlertVariant::Info)->component())->toBeInstanceOf(BackendComponent::class);
});
