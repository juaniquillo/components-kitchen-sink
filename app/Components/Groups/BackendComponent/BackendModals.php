<?php

declare(strict_types=1);

namespace App\Components\Groups\BackendComponent;

use App\Components\ContainerOptions;
use App\Components\Contracts\Component;
use Juaniquillo\BackendComponents\Enums\ComponentEnum;
use Juaniquillo\BackendComponents\Factories\ComponentFactory;
use Juaniquillo\BackendComponents\Utils\ModalUtil;
use Juaniquillo\BaseBackendComponent\Contracts\BackendComponent;

class BackendModals implements Component
{
    const NAME = 'Alpine JS Modals';

    public static function list(): array
    {
        return [
            'Default modal' => self::default(),
            'Basic modal' => self::basic(),
            'Modal with header and footer' => self::withHeaderAndFooter(),
            'Dialog HTML Tag' => self::dialog(),
        ];
    }

    public static function options(): ContainerOptions
    {
        return new ContainerOptions;
    }

    public static function default(): BackendComponent
    {
        return ModalUtil::make(
            'This is a default modal.'
        )->getComponent();
    }

    public static function basic(): BackendComponent
    {
        return ModalUtil::make(
            ComponentFactory::make(ComponentEnum::DIV)
                ->setContents([
                    ComponentFactory::make(ComponentEnum::PARAGRAPH)
                        ->setContent('This is the modal content area.'),
                    ComponentFactory::make(ComponentEnum::PARAGRAPH)
                        ->setContent('You can put any content here, including forms, images, etc.'),
                ])
                ->setTheme('padding', 'md')
                ->setTheme('border', 'solid'),
            ComponentFactory::make(ComponentEnum::BUTTON)
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
                    '2xl',
                ]
            )
            ->getComponent();
    }

    public static function withHeaderAndFooter(): BackendComponent
    {
        return ModalUtil::make(
            content: ComponentFactory::make(ComponentEnum::DIV)
                ->setContent('This is the modal content area.')
                ->setTheme('padding', 'sm'),
            button: ComponentFactory::make(ComponentEnum::BUTTON)
                ->setTheme('color', 'light')
                ->setContent('With Header and Footer')
                ->setAttribute('type', 'button')
                ->setAttribute('@click', 'showModal = true')
                ->setTheme('action', 'info')
                ->setTheme('padding', 'button-compact')
                ->setTheme('border-radius', 'sm'),
            title: ComponentFactory::make(ComponentEnum::DIV)
                ->setContent('Modal Header')
                ->setTheme('background', 'info')
                ->setTheme('padding', 'xs'),
            footer: ComponentFactory::make(ComponentEnum::DIV)
                ->setTheme('background', 'secondary')
                ->setTheme('padding', 'xs')
                ->setTheme('text', 'right')
                ->setContent(
                    ComponentFactory::make(ComponentEnum::BUTTON)
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
                    '2xl',
                ]
            )
            ->getComponent();

    }

    public static function dialog(): BackendComponent
    {
        return ComponentFactory::make(ComponentEnum::COLLECTION)
            ->setContent(
                ComponentFactory::make(ComponentEnum::BUTTON)
                    ->setContent('Dialog HTML Tag')
                    ->setTheme('action', 'default')
                    ->setTheme('padding', 'button-compact')
                    ->setTheme('border-radius', 'sm')
                    ->setTheme('color', 'light')
                    ->setAttributes([
                        'command' => 'show-modal',
                        'commandfor' => 'my-dialog-1',

                    ])

            )
            ->setContent(
                ComponentFactory::make(ComponentEnum::DIALOG)
                    ->setAttribute('id', 'my-dialog-1')
                    ->setTheme('modal', [
                        'default',
                        'lg',
                    ])
                    ->setContent(
                        ComponentFactory::make(ComponentEnum::DIV)
                            ->setTheme('padding', [
                                'top-sm',
                                'bottom-sm',
                                'right-md',
                                'left-md',
                            ])
                            ->setContents([
                                ComponentFactory::make(ComponentEnum::PARAGRAPH)
                                    ->setTheme('margin', 'bottom-sm')
                                    ->setContent('This is a dialog HTML tag. You can put any content here, including forms, images, etc.'),
                                ComponentFactory::make(ComponentEnum::BUTTON)
                                    ->setContent('Close')
                                    ->setTheme('action', 'default')
                                    ->setTheme('padding', 'button-compact')
                                    ->setAttributes([
                                        'command' => 'close',
                                        'commandfor' => 'my-dialog-1',

                                    ]),
                            ])
                    )
            );
    }
}
