<?php

namespace  App\Components\Groups;

use App\Components\Contracts\Component;
use Juaniquillo\BackendComponents\Builders\ComponentBuilder;
use Juaniquillo\BackendComponents\Contracts\BackendComponent;
use Juaniquillo\BackendComponents\Enums\ComponentEnum;

class Modals implements Component
{
    
    CONST NAME = 'Modals';

    public static function make() : array
    {
        return [
            'Basic modal' => self::basic(),
            'Modal with header and footer' => self::withHeaderAndFooter(),
        ];
    }

    public static function basic() : BackendComponent
    {
        return ComponentBuilder::make(ComponentEnum::MODAL)
            ->setContent('This is my modal')
            ->setTheme(
                'modal', [
                    'default',
                    '2xl'
                ]
            )
            ->setTheme('padding', 'sm')
            ->setSlot(
                'button', 
                ComponentBuilder::make(ComponentEnum::BUTTON)
                    ->setTheme('color', 'light')
                    ->setContent('Simple Modal')
                    ->setAttribute('type', 'button')
                    ->setAttribute('@click', 'showModal = true')
                    ->setTheme('action', 'success')
                    ->setTheme('padding', 'button-compact')
                    ->setTheme('border-radius', 'sm')
            );
    }

    public static function withHeaderAndFooter() : BackendComponent
    {
        return ComponentBuilder::make(ComponentEnum::MODAL)
            ->setContent(
                ComponentBuilder::make(ComponentEnum::DIV)
                    ->setContent('This is the modal content area.')
                    ->setTheme('padding', 'sm')
            )
            ->setTheme(
                'modal', [
                    'default',
                    '2xl'
                ]
            )
            ->setSlot(
                'button', 
                ComponentBuilder::make(ComponentEnum::BUTTON)
                    ->setTheme('color', 'light')
                    ->setContent('With Header and Footer')
                    ->setAttribute('type', 'button')
                    ->setAttribute('@click', 'showModal = true')
                    ->setTheme('action', 'info')
                    ->setTheme('padding', 'button-compact')
                    ->setTheme('border-radius', 'sm')
            )
            ->setSlot(
                'title',
                ComponentBuilder::make(ComponentEnum::DIV)
                    ->setContent('Modal Header')
                    ->setTheme('background', 'info')
                    ->setTheme('padding', 'xs')
            )
            ->setSlot(
                'footer',
                ComponentBuilder::make(ComponentEnum::DIV)
                    ->setTheme('background', 'secondary')
                    ->setTheme('padding', 'xs')
                    ->setTheme('text', 'right')
                    ->setContent(
                        ComponentBuilder::make(ComponentEnum::BUTTON)
                            ->setTheme('color', 'light')
                            ->setContent('Close')
                            ->setAttribute('type', 'button')
                            ->setAttribute('@click', 'showModal = false')
                            ->setTheme('action', 'error')
                            ->setTheme('padding', 'button-compact')
                            ->setTheme('border-radius', 'sm')
                    )
            );
    }
}
