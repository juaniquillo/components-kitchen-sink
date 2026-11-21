<?php

declare(strict_types=1);

use App\DesignSystem\Components\Badge;
use App\DesignSystem\Theme\BadgeVariant;
use Juaniquillo\BaseBackendComponent\Contracts\BackendComponent;

it('renders a default badge', function () {
    $html = (string) $this->blade('{{ $badge }}', [
        'badge' => Badge::make(designSystemBase())->label('Draft'),
    ]);

    expect($html)->toContain('<span')
        ->toContain('Draft')
        ->toContain('bg-zinc-200');
});

it('renders badge variants', function () {
    $success = (string) $this->blade('{{ $badge }}', [
        'badge' => Badge::make(designSystemBase())->label('Active')->variant(BadgeVariant::Success),
    ]);
    $danger = (string) $this->blade('{{ $badge }}', [
        'badge' => Badge::make(designSystemBase())->label('Failed')->variant(BadgeVariant::Danger),
    ]);

    expect($success)->toContain('bg-green-100')
        ->toContain('text-green-900')
        ->and($danger)->toContain('bg-red-100')
        ->toContain('text-red-900');
});

it('exposes a plain backend component', function () {
    expect(Badge::make(designSystemBase())->label('Draft')->component())->toBeInstanceOf(BackendComponent::class);
});
