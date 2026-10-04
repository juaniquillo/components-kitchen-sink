<?php

declare(strict_types=1);

namespace App\Components\Groups\Slate;

use App\Components\ContainerOptions;
use App\Components\Contracts\Component;
use Juaniquillo\BackendComponents\Contracts\BackendComponent;
use Juaniquillo\SlateBackendComponents\Builders\SlateComponentBuilder;
use Juaniquillo\SlateBackendComponents\SlateComponentEnum;

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
        $dropdown = SlateComponentBuilder::make(SlateComponentEnum::DROPDOWN_MENU);

        $dropdown->setContent(
            SlateComponentBuilder::make(SlateComponentEnum::DROPDOWN_MENU_TRIGGER)
                ->setContent(
                    SlateComponentBuilder::make(SlateComponentEnum::BUTTON)
                        ->setAttribute('variant', 'outline')
                        ->setContent('Open')
                )
        );

        $dropdown->setContent(
            SlateComponentBuilder::make(SlateComponentEnum::DROPDOWN_MENU_CONTENT)
                ->setAttribute('class', 'w-48')
                ->setContents([
                    SlateComponentBuilder::make(SlateComponentEnum::DROPDOWN_MENU_LABEL)
                        ->setContent('My Account'),
                    SlateComponentBuilder::make(SlateComponentEnum::DROPDOWN_MENU_SEPARATOR),
                    SlateComponentBuilder::make(SlateComponentEnum::DROPDOWN_MENU_ITEM)
                        ->setContent('Profile'),
                    SlateComponentBuilder::make(SlateComponentEnum::DROPDOWN_MENU_ITEM)
                        ->setContent('Billing'),
                    SlateComponentBuilder::make(SlateComponentEnum::DROPDOWN_MENU_ITEM)
                        ->setContent('Settings'),
                    SlateComponentBuilder::make(SlateComponentEnum::DROPDOWN_MENU_SEPARATOR),
                    SlateComponentBuilder::make(SlateComponentEnum::DROPDOWN_MENU_ITEM)
                        ->setAttribute('variant', 'destructive')
                        ->setContent('Log out'),
                ])
        );

        return $dropdown;
    }
}