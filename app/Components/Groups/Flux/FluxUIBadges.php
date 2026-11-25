<?php

declare(strict_types=1);

namespace App\Components\Groups\Flux;

use App\Components\Contracts\Component;
use App\Components\Options\GroupOptions;
use Juaniquillo\BaseBackendComponent\Contracts\BackendComponent;
use Juaniquillo\FluxBackendComponents\Factories\FluxComponentFactory;
use Juaniquillo\FluxBackendComponents\FluxBackendComponent;
use Juaniquillo\FluxBackendComponents\FluxComponentEnum;

class FluxUIBadges implements Component
{
    const NAME = 'Flux UI Badges';

    public static function list(): array
    {
        return [
            self::default(),
            self::success(),
            self::danger(),
            self::warning(),
            self::info(),
            self::solid(),
            self::pill(),
            self::small(),
            self::large(),
            self::withIcon(),
            self::withTrailingIcon(),
            self::dismissible(),
        ];
    }

    public static function options(): GroupOptions
    {
        return new GroupOptions;
    }

    public static function default(): BackendComponent
    {
        return self::badge('Default');
    }

    public static function success(): BackendComponent
    {
        return self::badge('Active')->setAttribute('color', 'green');
    }

    public static function danger(): BackendComponent
    {
        return self::badge('Failed')->setAttribute('color', 'red');
    }

    public static function warning(): BackendComponent
    {
        return self::badge('Expiring')->setAttribute('color', 'amber');
    }

    public static function info(): BackendComponent
    {
        return self::badge('New')->setAttribute('color', 'blue');
    }

    public static function solid(): BackendComponent
    {
        return self::badge('Solid')
            ->setAttribute('variant', 'solid')
            ->setAttribute('color', 'green');
    }

    public static function pill(): BackendComponent
    {
        return self::badge('Pill')
            ->setProp('rounded', true)
            ->setAttribute('color', 'indigo');
    }

    public static function small(): BackendComponent
    {
        return self::badge('Small')->setAttribute('size', 'sm');
    }

    public static function large(): BackendComponent
    {
        return self::badge('Large')->setAttribute('size', 'lg');
    }

    public static function withIcon(): BackendComponent
    {
        return self::badge('Verified')
            ->setAttribute('color', 'green')
            ->setAttribute('icon', 'check');
    }

    public static function withTrailingIcon(): BackendComponent
    {
        return self::badge('Next')
            ->setAttribute('color', 'blue')
            ->setAttribute('icon:trailing', 'chevron-right');
    }

    public static function dismissible(): BackendComponent
    {
        return FluxComponentFactory::make(FluxComponentEnum::BADGE)
            ->setAttribute('color', 'amber')
            ->setContents([
                'Draft',
                FluxComponentFactory::make(FluxComponentEnum::BADGE_CLOSE),
            ]);
    }

    private static function badge(string $label): FluxBackendComponent
    {
        return (new FluxBackendComponent(FluxComponentEnum::BADGE))
            ->setContent($label);
    }
}
