<?php

declare(strict_types=1);

namespace App\Components\Groups\Slate;

use App\Components\ContainerOptions;
use App\Components\Contracts\Component;
use Juaniquillo\BackendComponents\Builders\ComponentBuilder;
use Juaniquillo\BackendComponents\Contracts\BackendComponent;
use Juaniquillo\BackendComponents\Enums\ComponentEnum;
use Juaniquillo\SlateBackendComponents\Builders\SlateComponentBuilder;
use Juaniquillo\SlateBackendComponents\SlateComponentEnum;

class SlateTabs implements Component
{
    const NAME = 'Tabs';

    public static function list(): array
    {
        return [
            self::simple(),
        ];
    }

    public static function options(): ContainerOptions
    {
        return new ContainerOptions(
            flexColumn: false,
        );
    }

    public static function simple(): BackendComponent
    {
        $tabs = SlateComponentBuilder::make(SlateComponentEnum::TABS)
            ->setAttribute('default-value', 'account');

        $tabs->setContents([
            SlateComponentBuilder::make(SlateComponentEnum::TABS_LIST)
                ->setContents([
                    SlateComponentBuilder::make(SlateComponentEnum::TABS_TRIGGER)
                        ->setAttribute('value', 'account')
                        ->setContent('Account'),
                    SlateComponentBuilder::make(SlateComponentEnum::TABS_TRIGGER)
                        ->setAttribute('value', 'password')
                        ->setContent('Password'),
                ]),

        ]);
        $tabs->setContent(
            SlateComponentBuilder::make(SlateComponentEnum::TABS_CONTENT)
                ->setAttribute('value', 'account')
                ->setContent(
                    ComponentBuilder::make(ComponentEnum::DIV)
                        ->setTheme('text', 'center')
                        ->setContent('This is account')
                )
        );

        $tabs->setContent(
            SlateComponentBuilder::make(SlateComponentEnum::TABS_CONTENT)
                ->setAttribute('value', 'password')
                ->setContent(
                    ComponentBuilder::make(ComponentEnum::DIV)
                        ->setTheme('text', 'center')
                        ->setContent('This is password')
                )
        );

        return $tabs;
    }
}