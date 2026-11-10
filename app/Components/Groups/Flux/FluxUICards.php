<?php

declare(strict_types=1);

namespace App\Components\Groups\Flux;

use App\Components\ContainerOptions;
use App\Components\Contracts\Component;
use Juaniquillo\BackendComponents\Contracts\BackendComponent;
use Juaniquillo\BackendComponents\Enums\ComponentEnum;
use Juaniquillo\BackendComponents\Factories\LocalThemeComponentFactory;
use Juaniquillo\FluxBackendComponents\Factories\FluxLocalThemeComponentFactory;
use Juaniquillo\FluxBackendComponents\FluxComponentEnum;

class FluxUICards implements Component
{
    const NAME = 'Flux UI Cards';

    public static function list(): array
    {
        return [
            self::simple(),
            self::withHeader(),
            self::withFooter(),
            self::small(),
            self::interactive(),
            self::withImage(),
        ];
    }

    public static function options(): ContainerOptions
    {
        return new ContainerOptions(
            flexColumn: true,
        );
    }

    public static function simple(): BackendComponent
    {
        return FluxLocalThemeComponentFactory::make(FluxComponentEnum::CARD)
            ->setContents([
                FluxLocalThemeComponentFactory::make(FluxComponentEnum::HEADING)
                    ->setAttribute('level', 2)
                    ->setContent('Simple card'),
                FluxLocalThemeComponentFactory::make(FluxComponentEnum::TEXT)
                    ->setContent('This is a basic Flux card with a heading and some body text.'),
            ]);
    }

    public static function withHeader(): BackendComponent
    {
        return FluxLocalThemeComponentFactory::make(FluxComponentEnum::CARD)
            ->setContents([
                FluxLocalThemeComponentFactory::make(FluxComponentEnum::HEADING)
                    ->setAttribute('level', 2)
                    ->setContent('Card with header'),
                FluxLocalThemeComponentFactory::make(FluxComponentEnum::SEPARATOR)
                    ->setTheme('margin', [
                        'top-xs',
                        'bottom-xs',
                    ]),
                FluxLocalThemeComponentFactory::make(FluxComponentEnum::TEXT)
                    ->setContent('Content separated from the heading by a divider line.'),
            ]);
    }

    public static function withFooter(): BackendComponent
    {
        return FluxLocalThemeComponentFactory::make(FluxComponentEnum::CARD)
            ->setContents([
                FluxLocalThemeComponentFactory::make(FluxComponentEnum::HEADING)
                    ->setAttribute('level', 2)
                    ->setContent('Card with footer'),
                FluxLocalThemeComponentFactory::make(FluxComponentEnum::TEXT)
                    ->setContent('This card has action buttons in the footer area.'),
                FluxLocalThemeComponentFactory::make(FluxComponentEnum::SEPARATOR)
                    ->setTheme('margin', [
                        'top-xs',
                        'bottom-sm',
                    ]),
                LocalThemeComponentFactory::make(ComponentEnum::DIV)
                    ->setTheme('card', 'footer-actions')
                    ->setContents([
                        FluxLocalThemeComponentFactory::make(FluxComponentEnum::BUTTON)
                            ->setAttribute('variant', 'ghost')
                            ->setContent('Cancel'),
                        FluxLocalThemeComponentFactory::make(FluxComponentEnum::BUTTON)
                            ->setAttribute('variant', 'primary')
                            ->setContent('Save'),
                    ]),
            ]);
    }

    public static function small(): BackendComponent
    {
        return FluxLocalThemeComponentFactory::make(FluxComponentEnum::CARD)
            ->setAttribute('size', 'sm')
            ->setContents([
                FluxLocalThemeComponentFactory::make(FluxComponentEnum::HEADING)
                    ->setAttribute('level', 3)
                    ->setContent('Small card'),
                FluxLocalThemeComponentFactory::make(FluxComponentEnum::TEXT)
                    ->setContent('Compact padding variant.'),
            ]);
    }

    public static function interactive(): BackendComponent
    {
        return FluxLocalThemeComponentFactory::make(FluxComponentEnum::CARD)
            ->setAttributes([
                'x-on:click' => 'alert("clicked")',
            ])
            ->setTheme('card', 'interactive')
            ->setContents([
                FluxLocalThemeComponentFactory::make(FluxComponentEnum::HEADING)
                    ->setAttribute('level', 2)
                    ->setContent('Interactive card'),
                FluxLocalThemeComponentFactory::make(FluxComponentEnum::TEXT)
                    ->setContent('Click this card to trigger an action.'),
            ]);
    }

    public static function withImage(): BackendComponent
    {
        return FluxLocalThemeComponentFactory::make(FluxComponentEnum::CARD)
            ->setContents([
                LocalThemeComponentFactory::make(ComponentEnum::IMG)
                    ->setAttributes([
                        'src' => 'https://placehold.co/200x100',
                        'alt' => 'Placeholder image',
                    ])
                    ->setTheme('card', 'image'),
                FluxLocalThemeComponentFactory::make(FluxComponentEnum::HEADING)
                    ->setAttribute('level', 2)
                    ->setContent('Card with image')
                    ->setTheme('margin', 'top-sm'),
                FluxLocalThemeComponentFactory::make(FluxComponentEnum::TEXT)
                    ->setContent('An image sits at the top of this card followed by text content.'),
            ]);
    }
}
