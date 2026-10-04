<?php

declare(strict_types=1);

namespace App\Components\Groups\Slate;

use App\Components\ContainerOptions;
use App\Components\Contracts\Component;
use Juaniquillo\BackendComponents\Contracts\BackendComponent;
use Juaniquillo\SlateBackendComponents\Builders\SlateComponentBuilder;
use Juaniquillo\SlateBackendComponents\SlateComponentEnum;
use Juaniquillo\SlateBackendComponents\Utils\SlateOverlayUtil;

class SlateDialog implements Component
{
    const NAME = 'Dialogs';

    public static function list(): array
    {
        return [
            self::simple(),
            self::confirm(),
        ];
    }

    public static function options(): ContainerOptions
    {
        return new ContainerOptions;
    }

    public static function simple(): BackendComponent
    {
        return SlateOverlayUtil::make(
            root: SlateComponentEnum::DIALOG,
            content: 'Yo, I\'m here',
            trigger: SlateComponentBuilder::make(SlateComponentEnum::BUTTON)
                ->setContent('Open'),
            title: 'Cure Dialog',
        )->getComponent();
    }

    public static function confirm(): BackendComponent
    {
        return SlateOverlayUtil::make(
            root: SlateComponentEnum::DIALOG,
            content: [],
            trigger: SlateComponentBuilder::make(SlateComponentEnum::BUTTON)
                ->setAttribute('variant', 'outline')
                ->setContent('Open'),
            title: 'Are you sure?',
            description: 'This action cannot be undone.',
            footer: [
                SlateComponentBuilder::make(SlateComponentEnum::DIALOG_CLOSE)
                    ->setContent(
                        SlateComponentBuilder::make(SlateComponentEnum::BUTTON)
                            ->setContent('Cancel')
                    ),
            ],
        )
            ->setShowCloseButton(false)
            ->getComponent();
    }
}