<?php

namespace  App\Components\Groups;

use App\Components\Builders\FluxComponentBuilder;
use App\Components\Contracts\Component;
use Juaniquillo\BackendComponents\Builders\ComponentBuilder;
use Juaniquillo\BackendComponents\Builders\LocalThemeComponentBuilder;
use Juaniquillo\BackendComponents\Contracts\BackendComponent;
use Juaniquillo\BackendComponents\Enums\ComponentEnum;

class FluxModals implements Component 
{
    const NAME = 'Modals (Livewire Flux)';
    
    public static function make(): array
    {
        return [
            self::default(),
            self::confirm(),
            self::flyout(),
        ];
    }

    public static function config(): array
    {
        return [];
    }
    
    public static function default(): BackendComponent
    {
        return ComponentBuilder::make(ComponentEnum::COLLECTION)
            ->setContents([
                'button' => FluxComponentBuilder::make('modal.trigger')
                    ->setAttribute('name', 'flux-modal-test')
                    ->setContent(
                        FluxComponentBuilder::make('button')
                            ->setContent('Open Modal')
                    ),
                'modal' => FluxComponentBuilder::make('modal')
                    ->setAttribute('name', 'flux-modal-test')
                    ->setContent(
                        ComponentBuilder::make(ComponentEnum::DIV)
                            ->setTheme('padding', 'md')
                            ->setContent('This is a Flux Modal'),
                        
                    ),
            ]);
    }

    public static function confirm(): BackendComponent
    {
         return ComponentBuilder::make(ComponentEnum::COLLECTION)
            ->setContents([
                    'button' => FluxComponentBuilder::make('modal.trigger')
                    ->setAttribute('name', 'flux-modal-confirm')
                    ->setContent(
                        FluxComponentBuilder::make('button')
                            ->setAttribute('variant', 'danger')
                            ->setContent('Confirm')
                    ),
                'modal' =>  FluxComponentBuilder::make('modal')
                    ->setAttribute('name', 'flux-modal-confirm')
                    // ->setAttribute(':dismissible', 'false')
                    ->setContent(
                        LocalThemeComponentBuilder::make(ComponentEnum::DIV)
                            ->setTheme('modal', 'flux-spacing')
                            ->setTheme('padding', 'x-md')
                            ->setContents([
                                FluxComponentBuilder::make('heading')
                                    ->setContent('Delete project?'),
                                FluxComponentBuilder::make('text')
                                    ->setAttribute('margin', 'top-xs')
                                    ->setContents([
                                        ComponentBuilder::make(ComponentEnum::PARAGRAPH)
                                            ->setContent('You\'re about to delete this project.'),
                                    ]),
                                
                                FluxComponentBuilder::make('spacer'),
                                FluxComponentBuilder::make('modal.close')
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

    public static function flyout(): BackendComponent
    {
        return ComponentBuilder::make(ComponentEnum::COLLECTION)
            ->setContents([
                'button' => FluxComponentBuilder::make('modal.trigger')
                    ->setAttribute('name', 'flux-modal-flyout')
                    ->setContent(
                        FluxComponentBuilder::make('button')
                            ->setContent('Open side panel')
                    ),
                'modal' =>  FluxComponentBuilder::make('modal')
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

