<?php

declare(strict_types=1);

namespace App\Components\Groups\Flux;

use App\Components\ContainerOptions;
use App\Components\Contracts\Component;
use Juaniquillo\BackendComponents\Contracts\BackendComponent;
use Juaniquillo\BackendComponents\Enums\ComponentEnum;
use Juaniquillo\BackendComponents\Factories\ComponentFactory;
use Juaniquillo\BackendComponents\Factories\LocalThemeComponentFactory;
use Juaniquillo\FluxBackendComponents\Factories\FluxComponentFactory;
use Juaniquillo\FluxBackendComponents\FluxComponentEnum;

class FluxUIModals implements Component
{
    const NAME = 'Flux UI Modals';

    public static function list(): array
    {
        return [
            self::modalDefault(),
            self::modalConfirm(),
            self::modalFlyout(),
        ];
    }

    public static function options(): ContainerOptions
    {
        return new ContainerOptions;
    }

    public static function modalDefault(): BackendComponent
    {
        return ComponentFactory::make(ComponentEnum::COLLECTION)
            ->setContents([
                'button' => FluxComponentFactory::make(FluxComponentEnum::MODAL_TRIGGER)
                    ->setAttribute('name', 'flux-modal-test')
                    ->setContent(
                        FluxComponentFactory::make(FluxComponentEnum::BUTTON)
                            ->setTheme('cursor', 'pointer')
                            ->setContent('Open Modal')
                    ),
                'modal' => FluxComponentFactory::make(FluxComponentEnum::MODAL)
                    ->setAttribute('name', 'flux-modal-test')
                    ->setContent(
                        ComponentFactory::make(ComponentEnum::DIV)
                            ->setTheme('padding', 'md')
                            ->setContent('This is a Flux Modal'),

                    ),
            ]);
    }

    public static function modalConfirm(): BackendComponent
    {
        return ComponentFactory::make(ComponentEnum::COLLECTION)
            ->setContents([
                'button' => FluxComponentFactory::make(FluxComponentEnum::MODAL_TRIGGER)
                    ->setAttribute('name', 'flux-modal-confirm')
                    ->setContent(
                        FluxComponentFactory::make(FluxComponentEnum::BUTTON)
                            ->setTheme('cursor', 'pointer')
                            ->setAttribute('variant', 'danger')
                            ->setContent('Confirm')
                    ),
                'modal' => FluxComponentFactory::make(FluxComponentEnum::MODAL)
                    ->setAttribute('name', 'flux-modal-confirm')
                    // ->setAttribute(':dismissible', 'false')
                    ->setContent(
                        LocalThemeComponentFactory::make(ComponentEnum::DIV)
                            ->setTheme('modal', 'flux-spacing')
                            ->setTheme('padding', 'x-md')
                            ->setContents([
                                FluxComponentFactory::make(FluxComponentEnum::HEADING)
                                    ->setContent('Delete project?'),
                                FluxComponentFactory::make(FluxComponentEnum::TEXT)
                                    ->setAttribute('margin', 'top-xs')
                                    ->setContents([
                                        ComponentFactory::make(ComponentEnum::PARAGRAPH)
                                            ->setContent('You\'re about to delete this project.'),
                                    ]),

                                FluxComponentFactory::make(FluxComponentEnum::SPACER),
                                FluxComponentFactory::make(FluxComponentEnum::MODAL_CLOSE)
                                    ->setContent(
                                        FluxComponentFactory::make('button')
                                            ->setAttribute('variant', 'ghost')
                                            ->setContent('Cancel'),

                                    ),
                                FluxComponentFactory::make('button')
                                    ->setAttribute('type', 'submit')
                                    ->setAttribute('variant', 'danger')
                                    ->setContent('Delete project'),

                            ])
                    ),
            ]);
    }

    public static function modalFlyout(): BackendComponent
    {
        return ComponentFactory::make(ComponentEnum::COLLECTION)
            ->setContents([
                'button' => FluxComponentFactory::make('modal.trigger')
                    ->setAttribute('name', 'flux-modal-flyout')
                    ->setContent(
                        FluxComponentFactory::make(FluxComponentEnum::BUTTON)
                            ->setTheme('cursor', 'pointer')
                            ->setContent('Open side panel')
                    ),
                'modal' => FluxComponentFactory::make(FluxComponentEnum::MODAL)
                    ->setAttribute('name', 'flux-modal-flyout')
                    ->setAttribute('variant', 'flyout')
                    ->setAttribute('position', 'left')
                    ->setContent(
                        ComponentFactory::make(ComponentEnum::DIV)
                            ->setTheme('padding', 'md')
                            ->setContent('This is a Flux panel'),
                    ),
            ]);
    }
}
