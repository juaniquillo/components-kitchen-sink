<?php

declare(strict_types=1);

namespace App\Components\Groups;

use App\Components\Builders\FluxLocalThemeComponentBuilder;
use App\Components\Contracts\Component;
use App\Components\ThirdParty\Flux\FluxComponentEnum;
use Juaniquillo\BackendComponents\Builders\LocalThemeComponentBuilder;
use Juaniquillo\BackendComponents\Contracts\BackendComponent;
use Juaniquillo\BackendComponents\Enums\ComponentEnum;

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

    public static function options(): array
    {
        return [
            'flex-column' => true,
            'width' => 'component-box-lg-width',
        ];
    }

    public static function simple(): BackendComponent
    {
        return FluxLocalThemeComponentBuilder::make(FluxComponentEnum::CARD)
            ->setContents([
                FluxLocalThemeComponentBuilder::make(FluxComponentEnum::HEADING)
                    ->setAttribute('level', 2)
                    ->setContent('Simple card'),
                FluxLocalThemeComponentBuilder::make(FluxComponentEnum::TEXT)
                    ->setContent('This is a basic Flux card with a heading and some body text.'),
            ]);
    }

    public static function withHeader(): BackendComponent
    {
        return FluxLocalThemeComponentBuilder::make(FluxComponentEnum::CARD)
            ->setContents([
                FluxLocalThemeComponentBuilder::make(FluxComponentEnum::HEADING)
                    ->setAttribute('level', 2)
                    ->setContent('Card with header'),
                FluxLocalThemeComponentBuilder::make(FluxComponentEnum::SEPARATOR)
                    ->setTheme('margin', [
                        'top-xs',
                        'bottom-xs',
                    ]),
                FluxLocalThemeComponentBuilder::make(FluxComponentEnum::TEXT)
                    ->setContent('Content separated from the heading by a divider line.'),
            ]);
    }

    public static function withFooter(): BackendComponent
    {
        return FluxLocalThemeComponentBuilder::make(FluxComponentEnum::CARD)
            ->setContents([
                FluxLocalThemeComponentBuilder::make(FluxComponentEnum::HEADING)
                    ->setAttribute('level', 2)
                    ->setContent('Card with footer'),
                FluxLocalThemeComponentBuilder::make(FluxComponentEnum::TEXT)
                    ->setContent('This card has action buttons in the footer area.'),
                FluxLocalThemeComponentBuilder::make(FluxComponentEnum::SEPARATOR)
                    ->setTheme('margin', [
                        'top-xs',
                        'bottom-sm',
                    ]),
                LocalThemeComponentBuilder::make(ComponentEnum::DIV)
                    ->setTheme('card', 'footer-actions')
                    ->setContents([
                        FluxLocalThemeComponentBuilder::make(FluxComponentEnum::BUTTON)
                            ->setAttribute('variant', 'ghost')
                            ->setContent('Cancel'),
                        FluxLocalThemeComponentBuilder::make(FluxComponentEnum::BUTTON)
                            ->setAttribute('variant', 'primary')
                            ->setContent('Save'),
                    ]),
            ]);
    }

    public static function small(): BackendComponent
    {
        return FluxLocalThemeComponentBuilder::make(FluxComponentEnum::CARD)
            ->setAttribute('size', 'sm')
            ->setContents([
                FluxLocalThemeComponentBuilder::make(FluxComponentEnum::HEADING)
                    ->setAttribute('level', 3)
                    ->setContent('Small card'),
                FluxLocalThemeComponentBuilder::make(FluxComponentEnum::TEXT)
                    ->setContent('Compact padding variant.'),
            ]);
    }

    public static function interactive(): BackendComponent
    {
        return FluxLocalThemeComponentBuilder::make(FluxComponentEnum::CARD)
            ->setAttributes([
                'x-on:click' => 'alert("clicked")',
            ])
            ->setTheme('card', 'interactive')
            ->setContents([
                FluxLocalThemeComponentBuilder::make(FluxComponentEnum::HEADING)
                    ->setAttribute('level', 2)
                    ->setContent('Interactive card'),
                FluxLocalThemeComponentBuilder::make(FluxComponentEnum::TEXT)
                    ->setContent('Click this card to trigger an action.'),
            ]);
    }

    public static function withImage(): BackendComponent
    {
        return FluxLocalThemeComponentBuilder::make(FluxComponentEnum::CARD)
            ->setContents([
                LocalThemeComponentBuilder::make(ComponentEnum::IMG)
                    ->setAttributes([
                        'src' => 'https://placehold.co/200x100',
                        'alt' => 'Placeholder image',
                    ])
                    ->setTheme('card', 'image'),
                FluxLocalThemeComponentBuilder::make(FluxComponentEnum::HEADING)
                    ->setAttribute('level', 2)
                    ->setContent('Card with image')
                    ,
                FluxLocalThemeComponentBuilder::make(FluxComponentEnum::TEXT)
                    ->setContent('An image sits at the top of this card followed by text content.'),
            ]);
    }
}
