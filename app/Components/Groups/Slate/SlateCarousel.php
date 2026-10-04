<?php

declare(strict_types=1);

namespace App\Components\Groups\Slate;

use App\Components\ContainerOptions;
use App\Components\Contracts\Component;
use Juaniquillo\BackendComponents\Builders\ComponentBuilder;
use Juaniquillo\BackendComponents\Contracts\BackendComponent;
use Juaniquillo\BackendComponents\Enums\ComponentEnum;
use Juaniquillo\SlateBackendComponents\Builders\SlateComponentBuilder;
use Juaniquillo\SlateBackendComponents\SlateComponentEnum;

class SlateCarousel implements Component
{
    const NAME = 'Carousel';

    public static function list(): array
    {
        return [
            self::simple(),
            self::withImages(),
        ];
    }

    public static function options(): ContainerOptions
    {
        return new ContainerOptions;
    }

    public static function placeholder(string $alt = ''): BackendComponent
    {
        return ComponentBuilder::make(ComponentEnum::IMG)
            ->setAttributes([
                'alt' => $alt,
                'src' => 'https://placehold.co/400x200/',
                'width' => 400,
                'height' => 200,
            ]);
    }

    public static function simple(): BackendComponent
    {
        $carousel = SlateComponentBuilder::make(SlateComponentEnum::CAROUSEL);

        $carousel->setContent(
            SlateComponentBuilder::make(SlateComponentEnum::CAROUSEL_CONTENT)
                ->setContents([
                    SlateComponentBuilder::make(SlateComponentEnum::CAROUSEL_ITEM)
                        ->setContent(
                            SlateComponentBuilder::make(SlateComponentEnum::CARD)
                                ->setAttribute('class', 'p-6 text-center')
                                ->setContent('Slide 1')
                        ),
                    SlateComponentBuilder::make(SlateComponentEnum::CAROUSEL_ITEM)
                        ->setContent(
                            SlateComponentBuilder::make(SlateComponentEnum::CARD)
                                ->setAttribute('class', 'p-6 text-center')
                                ->setContent('Slide 2')
                        ),
                    SlateComponentBuilder::make(SlateComponentEnum::CAROUSEL_ITEM)
                        ->setContent(
                            SlateComponentBuilder::make(SlateComponentEnum::CARD)
                                ->setAttribute('class', 'p-6 text-center')
                                ->setContent('Slide 3')
                        ),
                ])
        );

        $carousel->setContents([
            SlateComponentBuilder::make(SlateComponentEnum::CAROUSEL_PREVIOUS),
            SlateComponentBuilder::make(SlateComponentEnum::CAROUSEL_NEXT),
        ]);

        return $carousel;
    }

    public static function withImages(): BackendComponent
    {
        $carousel = SlateComponentBuilder::make(SlateComponentEnum::CAROUSEL);

        $carousel->setContent(
            SlateComponentBuilder::make(SlateComponentEnum::CAROUSEL_CONTENT)
                ->setContents([
                    SlateComponentBuilder::make(SlateComponentEnum::CAROUSEL_ITEM)
                        ->setContent(
                            ComponentBuilder::make(ComponentEnum::DIV)
                                ->setTheme('border-radius', 'sm')
                                ->setTheme('overflow', 'hidden')
                                ->setContent(self::placeholder())
                        ),
                    SlateComponentBuilder::make(SlateComponentEnum::CAROUSEL_ITEM)
                        ->setContent(
                            ComponentBuilder::make(ComponentEnum::DIV)
                                ->setTheme('border-radius', 'sm')
                                ->setTheme('overflow', 'hidden')
                                ->setContent(self::placeholder())
                        ),
                    SlateComponentBuilder::make(SlateComponentEnum::CAROUSEL_ITEM)
                        ->setContent(
                            ComponentBuilder::make(ComponentEnum::DIV)
                                ->setTheme('border-radius', 'sm')
                                ->setTheme('overflow', 'hidden')
                                ->setContent(self::placeholder())
                        ),
                ])
        );

        $carousel->setContents([
            SlateComponentBuilder::make(SlateComponentEnum::CAROUSEL_PREVIOUS),
            SlateComponentBuilder::make(SlateComponentEnum::CAROUSEL_NEXT),
        ]);

        return $carousel;
    }
}