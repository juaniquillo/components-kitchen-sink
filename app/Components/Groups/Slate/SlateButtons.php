<?php

declare(strict_types=1);

namespace App\Components\Groups\Slate;

use App\Components\ContainerOptions;
use App\Components\Contracts\Component;
use App\Components\ThirdParty\Slate\SlateBackendComponent;
use Juaniquillo\BackendComponents\Contracts\BackendComponent;

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
        $button = SlateBackendComponent::make('button')
            ->setContent('Simple Button');

        return $button;
    }

    public static function secondaryButton(): BackendComponent
    {
        $button = SlateBackendComponent::make('button')
            ->setContent('Secondary Button')
            ->setAttribute('variant', 'secondary');

        return $button;
    }

    public static function outlineButton(): BackendComponent
    {
        $button = SlateBackendComponent::make('button')
            ->setContent('Outline Button')
            ->setAttribute('variant', 'outline');

        return $button;
    }

    public static function ghostButton(): BackendComponent
    {
        $button = SlateBackendComponent::make('button')
            ->setContent('Ghost Button')
            ->setAttribute('variant', 'ghost');

        return $button;
    }

    public static function destructiveButton(): BackendComponent
    {
        $button = SlateBackendComponent::make('button')
            ->setContent('Destructive Button')
            ->setAttribute('variant', 'destructive');

        return $button;
    }

    public static function linkButton(): BackendComponent
    {
        $button = SlateBackendComponent::make('button')
            ->setContent('Link Button')
            ->setAttribute('variant', 'link');

        return $button;
    }

    public static function xsButton(): BackendComponent
    {
        $button = SlateBackendComponent::make('button')
            ->setContent('XS Button')
            ->setAttribute('size', 'xs');

        return $button;
    }

    public static function smButton(): BackendComponent
    {
        $button = SlateBackendComponent::make('button')
            ->setContent('SM Button')
            ->setAttribute('size', 'sm');

        return $button;
    }

    public static function lgButton(): BackendComponent
    {
        $button = SlateBackendComponent::make('button')
            ->setContent('LG Button')
            ->setAttribute('size', 'lg');

        return $button;
    }
}
