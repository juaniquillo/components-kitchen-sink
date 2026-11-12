<?php

declare(strict_types=1);

namespace App\Components\Groups\Slate;

use App\Components\ContainerOptions;
use App\Components\Contracts\Component;
use Juaniquillo\BaseBackendComponent\Contracts\BackendComponent;
use Juaniquillo\SlateBackendComponents\Factories\SlateComponentFactory;
use Juaniquillo\SlateBackendComponents\SlateComponentEnum;

class SlateButtons implements Component
{
    const NAME = 'Buttons';

    public static function list(): array
    {
        return [
            self::defaultButton(),
            self::secondaryButton(),
            self::outlineButton(),
            self::ghostButton(),
            self::destructiveButton(),
            self::linkButton(),
            self::xsButton(),
            self::smButton(),
            self::lgButton(),
        ];
    }

    public static function options(): ContainerOptions
    {
        return new ContainerOptions;
    }

    public static function defaultButton(): BackendComponent
    {
        return SlateComponentFactory::make(SlateComponentEnum::BUTTON)
            ->setContent('Simple Button');
    }

    public static function secondaryButton(): BackendComponent
    {
        return SlateComponentFactory::make(SlateComponentEnum::BUTTON)
            ->setContent('Secondary Button')
            ->setAttribute('variant', 'secondary');
    }

    public static function outlineButton(): BackendComponent
    {
        return SlateComponentFactory::make(SlateComponentEnum::BUTTON)
            ->setContent('Outline Button')
            ->setAttribute('variant', 'outline');
    }

    public static function ghostButton(): BackendComponent
    {
        return SlateComponentFactory::make(SlateComponentEnum::BUTTON)
            ->setContent('Ghost Button')
            ->setAttribute('variant', 'ghost');
    }

    public static function destructiveButton(): BackendComponent
    {
        return SlateComponentFactory::make(SlateComponentEnum::BUTTON)
            ->setContent('Destructive Button')
            ->setAttribute('variant', 'destructive');
    }

    public static function linkButton(): BackendComponent
    {
        return SlateComponentFactory::make(SlateComponentEnum::BUTTON)
            ->setContent('Link Button')
            ->setAttribute('variant', 'link');
    }

    public static function xsButton(): BackendComponent
    {
        return SlateComponentFactory::make(SlateComponentEnum::BUTTON)
            ->setContent('XS Button')
            ->setAttribute('size', 'xs');
    }

    public static function smButton(): BackendComponent
    {
        return SlateComponentFactory::make(SlateComponentEnum::BUTTON)
            ->setContent('SM Button')
            ->setAttribute('size', 'sm');
    }

    public static function lgButton(): BackendComponent
    {
        return SlateComponentFactory::make(SlateComponentEnum::BUTTON)
            ->setContent('LG Button')
            ->setAttribute('size', 'lg');
    }
}
