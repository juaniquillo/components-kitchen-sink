<?php

declare(strict_types=1);

namespace App\Components\Groups\Slate;

use App\Components\ContainerOptions;
use App\Components\Contracts\Component;
use App\Components\ThirdParty\Slate\SlateBackendComponent;
use Juaniquillo\BackendComponents\Contracts\BackendComponent;

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
        $dialog = SlateBackendComponent::make('dialog');

        $dialog->setContent(
            SlateBackendComponent::make('dialog-trigger')
                ->setContent(
                    SlateBackendComponent::make('button')
                        ->setContent('Open')
                )
        );

        $dialog->setContent(
            SlateBackendComponent::make('dialog-content')
                ->setAttribute('title', 'Cure Dialog')
                ->setContent(
                    'Yo, I\'m here'
                )
        );

        return $dialog;
    }

    public static function confirm(): BackendComponent
    {
        $dialog = SlateBackendComponent::make('dialog');

        $dialog->setContent(
            SlateBackendComponent::make('dialog-trigger')
                ->setContent(
                    SlateBackendComponent::make('button')
                        ->setAttribute('variant', 'outline')
                        ->setContent('Open')
                )
        );

        $dialog->setContent(
            SlateBackendComponent::make('dialog-content')
                ->setAttribute('title', 'Are you sure?')
                ->setAttribute('description', 'This action cannot be undone.')
                ->setAttribute('show-close-button', 'false')
                ->setContent(
                    SlateBackendComponent::make('dialog-footer')
                        ->setContent(
                            SlateBackendComponent::make('dialog-close')
                                ->setContent(
                                    SlateBackendComponent::make('button')
                                        ->setContent('Cancel')
                                )
                        )
                )
        );

        return $dialog;
    }
}
