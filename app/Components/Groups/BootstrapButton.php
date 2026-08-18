<?php

declare(strict_types=1);

namespace App\Components\Groups;

use App\Components\Contracts\Component;
use Juaniquillo\BackendComponents\Builders\ComponentBuilder;
use Juaniquillo\BackendComponents\Contracts\BackendComponent;
use Juaniquillo\BackendComponents\Enums\ComponentEnum;

class BootstrapButton implements Component
{
    const NAME = 'Bootstrap Button';

    public static function list(): array
    {
        return [
            'primary' => self::primary(),
            'secondary' => self::secondary(),
            'success' => self::success(),
            'danger' => self::danger(),
            'link' => self::link(),
        ];
    }

    public static function options(): array
    {
        return [];
    }

    public static function primary(): BackendComponent
    {
        return ComponentBuilder::make(ComponentEnum::BUTTON)
            ->setContent('Primary')
            ->setAttributes([
                'class' => 'btn btn-primary',
                'type' => 'button',
            ]);
    }

    public static function secondary(): BackendComponent
    {
        return ComponentBuilder::make(ComponentEnum::BUTTON)
            ->setContent('Secondary')
            ->setAttributes([
                'class' => 'btn btn-secondary',
                'type' => 'button',
            ]);
    }

    public static function success(): BackendComponent
    {
        return ComponentBuilder::make(ComponentEnum::BUTTON)
            ->setContent('Success')
            ->setAttributes([
                'class' => 'btn btn-success',
                'type' => 'button',
            ]);
    }

    public static function danger(): BackendComponent
    {
        return ComponentBuilder::make(ComponentEnum::BUTTON)
            ->setContent('Danger')
            ->setAttributes([
                'class' => 'btn btn-danger',
                'type' => 'button',
            ]);
    }

    public static function link(): BackendComponent
    {
        return ComponentBuilder::make(ComponentEnum::BUTTON)
            ->setContent('Link')
            ->setAttributes([
                'class' => 'btn btn-link',
                'type' => 'button',
            ]);
    }
}
