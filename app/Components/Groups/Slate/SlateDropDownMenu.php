<?php

declare(strict_types=1);

namespace App\Components\Groups\Slate;

use App\Components\ContainerOptions;
use App\Components\Contracts\Component;
use Juaniquillo\BaseBackendComponent\Contracts\BackendComponent;
use Juaniquillo\SlateBackendComponents\Factories\SlateComponentFactory;
use Juaniquillo\SlateBackendComponents\SlateComponentEnum;

class SlateDropDownMenu implements Component
{
    const NAME = 'Slate Drop Down Menus';

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
        $dropdown = SlateComponentFactory::make(SlateComponentEnum::DROPDOWN_MENU);

        $dropdown->setContent(
            SlateComponentFactory::make(SlateComponentEnum::DROPDOWN_MENU_TRIGGER)
                ->setContent(
                    SlateComponentFactory::make(SlateComponentEnum::BUTTON)
                        ->setAttribute('variant', 'outline')
                        ->setContent('Open')
                )
        );

        $dropdown->setContent(
            SlateComponentFactory::make(SlateComponentEnum::DROPDOWN_MENU_CONTENT)
                ->setAttribute('class', 'w-48')
                ->setContents([
                    SlateComponentFactory::make(SlateComponentEnum::DROPDOWN_MENU_LABEL)
                        ->setContent('My Account'),
                    SlateComponentFactory::make(SlateComponentEnum::DROPDOWN_MENU_SEPARATOR),
                    SlateComponentFactory::make(SlateComponentEnum::DROPDOWN_MENU_ITEM)
                        ->setContent('Profile'),
                    SlateComponentFactory::make(SlateComponentEnum::DROPDOWN_MENU_ITEM)
                        ->setContent('Billing'),
                    SlateComponentFactory::make(SlateComponentEnum::DROPDOWN_MENU_ITEM)
                        ->setContent('Settings'),
                    SlateComponentFactory::make(SlateComponentEnum::DROPDOWN_MENU_SEPARATOR),
                    SlateComponentFactory::make(SlateComponentEnum::DROPDOWN_MENU_ITEM)
                        ->setAttribute('variant', 'destructive')
                        ->setContent('Log out'),
                ])
        );

        return $dropdown;
    }
}
