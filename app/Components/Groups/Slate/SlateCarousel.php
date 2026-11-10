<?php

declare(strict_types=1);

namespace App\Components\Groups\Slate;

use App\Components\ContainerOptions;
use App\Components\Contracts\Component;
use Juaniquillo\BackendComponents\Contracts\BackendComponent;
use Juaniquillo\BackendComponents\Enums\ComponentEnum;
use Juaniquillo\BackendComponents\Factories\ComponentFactory;
use Juaniquillo\SlateBackendComponents\Factories\SlateComponentFactory;
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
        return ComponentFactory::make(ComponentEnum::IMG)
            ->setAttributes([
                'alt' => $alt,
                'src' => 'https://placehold.co/400x200/',
                'width' => 400,
                'height' => 200,
            ]);
    }

    public static function simple(): BackendComponent
    {
        $carousel = SlateComponentFactory::make(SlateComponentEnum::CAROUSEL);

        $carousel->setContent(
            SlateComponentFactory::make(SlateComponentEnum::CAROUSEL_CONTENT)
                ->setContents([
                    SlateComponentFactory::make(SlateComponentEnum::CAROUSEL_ITEM)
                        ->setContent(
                            SlateComponentFactory::make(SlateComponentEnum::CARD)
                                ->setAttribute('class', 'p-6 text-center')
                                ->setContent('Slide 1')
                        ),
                    SlateComponentFactory::make(SlateComponentEnum::CAROUSEL_ITEM)
                        ->setContent(
                            SlateComponentFactory::make(SlateComponentEnum::CARD)
                                ->setAttribute('class', 'p-6 text-center')
                                ->setContent('Slide 2')
                        ),
                    SlateComponentFactory::make(SlateComponentEnum::CAROUSEL_ITEM)
                        ->setContent(
                            SlateComponentFactory::make(SlateComponentEnum::CARD)
                                ->setAttribute('class', 'p-6 text-center')
                                ->setContent('Slide 3')
                        ),
                ])
        );

        $carousel->setContents([
            SlateComponentFactory::make(SlateComponentEnum::CAROUSEL_PREVIOUS),
            SlateComponentFactory::make(SlateComponentEnum::CAROUSEL_NEXT),
        ]);

        return $carousel;
    }

    public static function withImages(): BackendComponent
    {
        $carousel = SlateComponentFactory::make(SlateComponentEnum::CAROUSEL);

        $carousel->setContent(
            SlateComponentFactory::make(SlateComponentEnum::CAROUSEL_CONTENT)
                ->setContents([
                    SlateComponentFactory::make(SlateComponentEnum::CAROUSEL_ITEM)
                        ->setContent(
                            ComponentFactory::make(ComponentEnum::DIV)
                                ->setTheme('border-radius', 'sm')
                                ->setTheme('overflow', 'hidden')
                                ->setContent(self::placeholder())
                        ),
                    SlateComponentFactory::make(SlateComponentEnum::CAROUSEL_ITEM)
                        ->setContent(
                            ComponentFactory::make(ComponentEnum::DIV)
                                ->setTheme('border-radius', 'sm')
                                ->setTheme('overflow', 'hidden')
                                ->setContent(self::placeholder())
                        ),
                    SlateComponentFactory::make(SlateComponentEnum::CAROUSEL_ITEM)
                        ->setContent(
                            ComponentFactory::make(ComponentEnum::DIV)
                                ->setTheme('border-radius', 'sm')
                                ->setTheme('overflow', 'hidden')
                                ->setContent(self::placeholder())
                        ),
                ])
        );

        $carousel->setContents([
            SlateComponentFactory::make(SlateComponentEnum::CAROUSEL_PREVIOUS),
            SlateComponentFactory::make(SlateComponentEnum::CAROUSEL_NEXT),
        ]);

        return $carousel;
    }
}
