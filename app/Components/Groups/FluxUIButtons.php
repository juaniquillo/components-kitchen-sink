<?php

declare(strict_types=1);

namespace App\Components\Groups;

use App\Components\Builders\FluxComponentBuilder;
use App\Components\Contracts\Component;
use App\Components\ThirdParty\Flux\FluxComponentEnum;

class FluxUIButtons implements Component
{
    const NAME = 'Flux UI Buttons';

    public static function list(): array
    {
        return [
            self::primary(),
            self::danger(),
            self::ghost(),
            self::outline(),
            self::sizes(),
            self::withIcon(),
        ];
    }

    public static function options(): array
    {
        return [];
    }

    public static function primary(): mixed
    {
        return FluxComponentBuilder::make(FluxComponentEnum::BUTTON)
            ->setAttribute('variant', 'primary')
            ->setContent('Primary');
    }

    public static function danger(): mixed
    {
        return FluxComponentBuilder::make(FluxComponentEnum::BUTTON)
            ->setAttribute('variant', 'danger')
            ->setContent('Danger');
    }

    public static function ghost(): mixed
    {
        return FluxComponentBuilder::make(FluxComponentEnum::BUTTON)
            ->setAttribute('variant', 'ghost')
            ->setContent('Ghost');
    }

    public static function outline(): mixed
    {
        return FluxComponentBuilder::make(FluxComponentEnum::BUTTON)
            ->setAttribute('variant', 'outline')
            ->setContent('Outline');
    }

    public static function sizes(): mixed
    {
        return FluxComponentBuilder::make(FluxComponentEnum::BUTTON)
            ->setAttribute('variant', 'primary')
            ->setAttribute('size', 'sm')
            ->setContent('Small');
    }

    public static function withIcon(): mixed
    {
        return FluxComponentBuilder::make(FluxComponentEnum::BUTTON)
            ->setAttribute('variant', 'primary')
            ->setAttribute('icon', 'plus')
            ->setContent('Add item');
    }
}
