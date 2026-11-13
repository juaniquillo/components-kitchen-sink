<?php

declare(strict_types=1);

namespace App\Components\Groups\Slate;

use App\Components\ContainerOptions;
use App\Components\Contracts\Component;
use Juaniquillo\BackendComponents\Enums\ComponentEnum;
use Juaniquillo\BackendComponents\Factories\ComponentFactory;
use Juaniquillo\BaseBackendComponent\Contracts\BackendComponent;
use Juaniquillo\SlateBackendComponents\Utils\SlateTabsUtil;

class SlateTabs implements Component
{
    const NAME = 'Slate Tabs';

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
        return SlateTabsUtil::make(
            tabs: [
                'account' => [
                    'label' => 'Account',
                    'content' => ComponentFactory::make(ComponentEnum::DIV)
                        ->setTheme('text', 'center')
                        ->setContent('This is account'),
                ],
                'password' => [
                    'label' => 'Password',
                    'content' => ComponentFactory::make(ComponentEnum::DIV)
                        ->setTheme('text', 'center')
                        ->setContent('This is password'),
                ],
            ],
            defaultValue: null,
        )->getComponent()->setAttribute('default-value', 'account');
    }
}
