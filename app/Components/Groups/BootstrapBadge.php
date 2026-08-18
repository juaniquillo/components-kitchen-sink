<?php

declare(strict_types=1);

namespace App\Components\Groups;

use App\Components\Contracts\Component;
use Juaniquillo\BackendComponents\Builders\ComponentBuilder;
use Juaniquillo\BackendComponents\Contracts\BackendComponent;
use Juaniquillo\BackendComponents\Enums\ComponentEnum;

class BootstrapBadge implements Component
{
    const NAME = 'Bootstrap Badge';

    public static function list(): array
    {
        return [
            'primary' => self::primary(),
            'secondary' => self::secondary(),
            'success' => self::success(),
            'danger' => self::danger(),
            'warning' => self::warning(),
            'info' => self::info(),
            'light' => self::light(),
            'dark' => self::dark(),
            'link' => self::link(),
        ];
    }

    public static function options(): array
    {
        return [];
    }

    public static function primary(): BackendComponent
    {
        return ComponentBuilder::make(ComponentEnum::SPAN)
            ->setContent('Primary')
            ->setAttributes([
                'class' => 'badge bg-primary text-white',
            ]);
    }

    public static function secondary(): BackendComponent
    {
        return ComponentBuilder::make(ComponentEnum::SPAN)
            ->setContent('Secondary')
            ->setAttributes([
                'class' => 'badge bg-secondary text-white',
            ]);
    }

    public static function success(): BackendComponent
    {
        return ComponentBuilder::make(ComponentEnum::SPAN)
            ->setContent('Success')
            ->setAttributes([
                'class' => 'badge bg-success text-white',
            ]);
    }

    public static function danger(): BackendComponent
    {
        return ComponentBuilder::make(ComponentEnum::SPAN)
            ->setContent('Danger')
            ->setAttributes([
                'class' => 'badge bg-danger text-white',
            ]);
    }

    public static function warning(): BackendComponent
    {
        return ComponentBuilder::make(ComponentEnum::SPAN)
            ->setContent('Warning')
            ->setAttributes([
                'class' => 'badge bg-warning text-dark',
            ]);
    }

    public static function info(): BackendComponent
    {
        return ComponentBuilder::make(ComponentEnum::SPAN)
            ->setContent('Info')
            ->setAttributes([
                'class' => 'badge bg-info text-white',
            ]);
    }

    public static function light(): BackendComponent
    {
        return ComponentBuilder::make(ComponentEnum::SPAN)
            ->setContent('Light')
            ->setAttributes([
                'class' => 'badge bg-light text-dark',
            ]);
    }

    public static function dark(): BackendComponent
    {
        return ComponentBuilder::make(ComponentEnum::SPAN)
            ->setContent('Dark')
            ->setAttributes([
                'class' => 'badge bg-dark text-white',
            ]);
    }

    public static function link(): BackendComponent
    {
        return ComponentBuilder::make(ComponentEnum::SPAN)
            ->setContent('Link')
            ->setAttributes([
                'class' => 'badge text-reset',
            ]);
    }
}
