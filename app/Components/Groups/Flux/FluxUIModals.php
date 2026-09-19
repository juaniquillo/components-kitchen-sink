<?php

declare(strict_types=1);

namespace App\Components\Groups\Flux;

use App\Components\ContainerOptions;
use App\Components\Contracts\Component;
use Juaniquillo\BackendComponents\Builders\ComponentBuilder;
use Juaniquillo\BackendComponents\Builders\LocalThemeComponentBuilder;
use Juaniquillo\BackendComponents\Contracts\BackendComponent;
use Juaniquillo\BackendComponents\Enums\ComponentEnum;
use Juaniquillo\FluxBackendComponents\Builders\FluxComponentBuilder;
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
        return ComponentBuilder::make(ComponentEnum::COLLECTION)
            ->setContents([
                'button' => FluxComponentBuilder::make(FluxComponentEnum::MODAL_TRIGGER)
                    ->setAttribute('name', 'flux-modal-test')
                    ->setContent(
                        FluxComponentBuilder::make(FluxComponentEnum::BUTTON)
                            ->setTheme('cursor', 'pointer')
                            ->setContent('Open Modal')
                    ),
                'modal' => FluxComponentBuilder::make(FluxComponentEnum::MODAL)
                    ->setAttribute('name', 'flux-modal-test')
                    ->setContent(
                        ComponentBuilder::make(ComponentEnum::DIV)
                            ->setTheme('padding', 'md')
                            ->setContent('This is a Flux Modal'),

                    ),
            ]);
    }

    public static function modalConfirm(): BackendComponent
    {
        return ComponentBuilder::make(ComponentEnum::COLLECTION)
            ->setContents([
                'button' => FluxComponentBuilder::make(FluxComponentEnum::MODAL_TRIGGER)
                    ->setAttribute('name', 'flux-modal-confirm')
                    ->setContent(
                        FluxComponentBuilder::make(FluxComponentEnum::BUTTON)
                            ->setTheme('cursor', 'pointer')
                            ->setAttribute('variant', 'danger')
                            ->setContent('Confirm')
                    ),
                'modal' => FluxComponentBuilder::make(FluxComponentEnum::MODAL)
                    ->setAttribute('name', 'flux-modal-confirm')
                    // ->setAttribute(':dismissible', 'false')
                    ->setContent(
                        LocalThemeComponentBuilder::make(ComponentEnum::DIV)
                            ->setTheme('modal', 'flux-spacing')
                            ->setTheme('padding', 'x-md')
                            ->setContents([
                                FluxComponentBuilder::make(FluxComponentEnum::HEADING)
                                    ->setContent('Delete project?'),
                                FluxComponentBuilder::make(FluxComponentEnum::TEXT)
                                    ->setAttribute('margin', 'top-xs')
                                    ->setContents([
                                        ComponentBuilder::make(ComponentEnum::PARAGRAPH)
                                            ->setContent('You\'re about to delete this project.'),
                                    ]),

                                FluxComponentBuilder::make(FluxComponentEnum::SPACER),
                                FluxComponentBuilder::make(FluxComponentEnum::MODAL_CLOSE)
                                    ->setContent(
                                        FluxComponentBuilder::make('button')
                                            ->setAttribute('variant', 'ghost')
                                            ->setContent('Cancel'),

                                    ),
                                FluxComponentBuilder::make('button')
                                    ->setAttribute('type', 'submit')
                                    ->setAttribute('variant', 'danger')
                                    ->setContent('Delete project'),

                            ])
                    ),
            ]);
    }

    public static function modalFlyout(): BackendComponent
    {
        return ComponentBuilder::make(ComponentEnum::COLLECTION)
            ->setContents([
                'button' => FluxComponentBuilder::make('modal.trigger')
                    ->setAttribute('name', 'flux-modal-flyout')
                    ->setContent(
                        FluxComponentBuilder::make(FluxComponentEnum::BUTTON)
                            ->setTheme('cursor', 'pointer')
                            ->setContent('Open side panel')
                    ),
                'modal' => FluxComponentBuilder::make(FluxComponentEnum::MODAL)
                    ->setAttribute('name', 'flux-modal-flyout')
                    ->setAttribute('variant', 'flyout')
                    ->setAttribute('position', 'left')
                    ->setContent(
                        ComponentBuilder::make(ComponentEnum::DIV)
                            ->setTheme('padding', 'md')
                            ->setContent('This is a Flux panel'),
                    ),
            ]);
    }
}
