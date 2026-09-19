<?php

declare(strict_types=1);

namespace App\Components\Groups\Slate;

use App\Components\ContainerOptions;
use App\Components\Contracts\Component;
use App\Components\ThirdParty\Slate\SlateBackendComponent;
use Juaniquillo\BackendComponents\Builders\ComponentBuilder;
use Juaniquillo\BackendComponents\Contracts\BackendComponent;
use Juaniquillo\BackendComponents\Enums\ComponentEnum;

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
        $tabs = SlateBackendComponent::make('tabs')
            ->setAttribute('default-value', 'account');

        $tabs->setContents([
            SlateBackendComponent::make('tabs-list')
                ->setContents([
                    SlateBackendComponent::make('tabs-trigger')
                        ->setAttribute('value', 'account')
                        ->setContent('Account'),
                    SlateBackendComponent::make('tabs-trigger')
                        ->setAttribute('value', 'password')
                        ->setContent('Password'),
                ]),

        ]);
        $tabs->setContent(
            SlateBackendComponent::make('tabs-content')
                ->setAttribute('value', 'account')
                ->setContent(
                    ComponentBuilder::make(ComponentEnum::DIV)
                        ->setTheme('text', 'center')
                        ->setContent('This is account')
                )
        );

        $tabs->setContent(
            SlateBackendComponent::make('tabs-content')
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
