<?php

namespace  App\Components\Groups;

use App\Components\Contracts\Component;
use Juaniquillo\BackendComponents\Builders\ComponentBuilder;
use Juaniquillo\BackendComponents\Contracts\BackendComponent;
use Juaniquillo\BackendComponents\Enums\ComponentEnum;
use Juaniquillo\BackendComponents\Utils\ModalUtil;

class MainPackage implements Component
{
    
    CONST NAME = 'Main Package';

    public static function make() : array
    {
        return [
            'Default modal' => self::default(),
            'Basic modal' => self::basic(),
            'Modal with header and footer' => self::withHeaderAndFooter(),
            'Dialog HTML Tag' => self::dialog(),
        ];
    }

    public static function default(): BackendComponent
    {
        return ModalUtil::make(
            'This is a default modal.'
        )->getComponent();
    }

    public static function basic() : BackendComponent
    {
        return ModalUtil::make(
            ComponentBuilder::make(ComponentEnum::DIV)
                ->setContents([
                    ComponentBuilder::make(ComponentEnum::PARAGRAPH)
                        ->setContent('This is the modal content area.'),
                    ComponentBuilder::make(ComponentEnum::PARAGRAPH)
                        ->setContent('You can put any content here, including forms, images, etc.'),
                ])
                ->setTheme('padding', 'md')
                ->setTheme('border', 'solid'),
            ComponentBuilder::make(ComponentEnum::BUTTON)
                ->setTheme('color', 'light')
                ->setContent('Simple Modal')
                ->setAttribute('type', 'button')
                ->setAttribute('@click', 'showModal = true')
                ->setTheme('action', 'success')
                ->setTheme('padding', 'button-compact')
                ->setTheme('border-radius', 'sm')
        )
        ->setTheme(
            'modal', [
                'default',
                '2xl'
            ]
        )
        ->getComponent();
    }

    public static function withHeaderAndFooter() : BackendComponent
    {
        return ModalUtil::make(
            content: ComponentBuilder::make(ComponentEnum::DIV)
                ->setContent('This is the modal content area.')
                ->setTheme('padding', 'sm'),
            button: ComponentBuilder::make(ComponentEnum::BUTTON)
                ->setTheme('color', 'light')
                ->setContent('With Header and Footer')
                ->setAttribute('type', 'button')
                ->setAttribute('@click', 'showModal = true')
                ->setTheme('action', 'info')
                ->setTheme('padding', 'button-compact')
                ->setTheme('border-radius', 'sm'),
            title: ComponentBuilder::make(ComponentEnum::DIV)
                ->setContent('Modal Header')
                ->setTheme('background', 'info')
                ->setTheme('padding', 'xs'),
            footer: ComponentBuilder::make(ComponentEnum::DIV)
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
        )
        ->setTheme(
            'modal', [
                'default',
                '2xl'
            ]
        )
        ->getComponent();

    }

    public  static function dialog(): BackendComponent
    {
        return ComponentBuilder::make(ComponentEnum::COLLECTION)
            ->setContent(
                ComponentBuilder::make(ComponentEnum::BUTTON)
                    ->setContent('Dialog HTML Tag')
                    ->setTheme('action', 'default')
                    ->setTheme('padding', 'button-compact')
                    ->setTheme('border-radius', 'sm')
                    ->setAttributes([
                        'command' => 'show-modal',
                        'commandfor'=> "my-dialog-1",

                    ])
                    
            )
            ->setContent(
                ComponentBuilder::make(ComponentEnum::DIALOG)
                    ->setAttribute('id', 'my-dialog-1')
                    ->setTheme('modal', [
                        'default',
                        'lg'
                    ])
                    ->setContent(
                        ComponentBuilder::make(ComponentEnum::DIV)
                            ->setTheme('padding', [
                                'top-sm',
                                'bottom-sm',
                                'right-md',
                                'left-md'
                            ])
                            ->setContents([
                                ComponentBuilder::make(ComponentEnum::PARAGRAPH)
                                    ->setTheme('margin', 'bottom-sm')
                                    ->setContent('This is a dialog HTML tag. You can put any content here, including forms, images, etc.'),
                                ComponentBuilder::make(ComponentEnum::BUTTON)
                                    ->setContent('Close')
                                    ->setTheme('action', 'default')
                                    ->setTheme('padding', 'button-compact')
                                    ->setAttributes([
                                        'command' => 'close',
                                        'commandfor'=> "my-dialog-1",

                                    ])
                            ])
                    )
            );
    }
}
