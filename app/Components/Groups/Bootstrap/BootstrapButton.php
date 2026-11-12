<?php

declare(strict_types=1);

namespace App\Components\Groups\Bootstrap;

use App\Components\ContainerOptions;
use App\Components\Contracts\Component;
use Juaniquillo\BackendComponents\Enums\ComponentEnum;
use Juaniquillo\BackendComponents\Factories\ComponentFactory;
use Juaniquillo\BaseBackendComponent\Contracts\BackendComponent;

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

    public static function options(): ContainerOptions
    {
        return new ContainerOptions;
    }

    public static function primary(): BackendComponent
    {
        return ComponentFactory::make(ComponentEnum::BUTTON)
            ->setContent('Primary')
            ->setAttributes([
                'class' => 'btn btn-primary',
                'type' => 'button',
            ]);
    }

    public static function secondary(): BackendComponent
    {
        return ComponentFactory::make(ComponentEnum::BUTTON)
            ->setContent('Secondary')
            ->setAttributes([
                'class' => 'btn btn-secondary',
                'type' => 'button',
            ]);
    }

    public static function success(): BackendComponent
    {
        return ComponentFactory::make(ComponentEnum::BUTTON)
            ->setContent('Success')
            ->setAttributes([
                'class' => 'btn btn-success',
                'type' => 'button',
            ]);
    }

    public static function danger(): BackendComponent
    {
        return ComponentFactory::make(ComponentEnum::BUTTON)
            ->setContent('Danger')
            ->setAttributes([
                'class' => 'btn btn-danger',
                'type' => 'button',
            ]);
    }

    public static function link(): BackendComponent
    {
        return ComponentFactory::make(ComponentEnum::BUTTON)
            ->setContent('Link')
            ->setAttributes([
                'class' => 'btn btn-link',
                'type' => 'button',
            ]);
    }
}
