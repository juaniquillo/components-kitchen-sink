<?php

declare(strict_types=1);

namespace App\Components\Groups\Slate;

use App\Components\ContainerOptions;
use App\Components\Contracts\Component;
use App\Components\ThirdParty\Slate\SlateBackendComponent;
use Juaniquillo\BackendComponents\Contracts\BackendComponent;

class SlateDropDownMenu implements Component
{
    const NAME = 'Drop Down Menus';

    public static function list(): array
    {
        return [
            self::simple(),
        ];
    }

    public static function options(): ContainerOptions
    {
        return new ContainerOptions;
    }

    public static function simple(): BackendComponent
    {
        $dropdown = SlateBackendComponent::make('dropdown-menu');

        $dropdown->setContent(
            SlateBackendComponent::make('dropdown-menu-trigger')
                ->setContent(
                    SlateBackendComponent::make('button')
                        ->setAttribute('variant', 'outline')
                        ->setContent('Open')
                )
        );

        $dropdown->setContent(
            SlateBackendComponent::make('dropdown-menu-content')
                ->setAttribute('class', 'w-48')
                ->setContents([
                    SlateBackendComponent::make('dropdown-menu-label')
                        ->setContent('My Account'),
                    SlateBackendComponent::make('dropdown-menu-separator'),
                    SlateBackendComponent::make('dropdown-menu-item')
                        ->setContent('Profile'),
                    SlateBackendComponent::make('dropdown-menu-item')
                        ->setContent('Billing'),
                    SlateBackendComponent::make('dropdown-menu-item')
                        ->setContent('Settings'),
                    SlateBackendComponent::make('dropdown-menu-separator'),
                    SlateBackendComponent::make('dropdown-menu-item')
                        ->setAttribute('variant', 'destructive')
                        ->setContent('Log out'),
                ])

        );

        return $dropdown;
    }
}
