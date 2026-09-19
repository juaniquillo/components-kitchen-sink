<?php

declare(strict_types=1);

namespace App\Components\Groups\Slate;

use App\Components\ContainerOptions;
use App\Components\Contracts\Component;
use App\Components\ThirdParty\Slate\SlateBackendComponent;
use Juaniquillo\BackendComponents\Builders\ComponentBuilder;
use Juaniquillo\BackendComponents\Contracts\BackendComponent;
use Juaniquillo\BackendComponents\Enums\ComponentEnum;

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
            ]);
    }

    public static function simple(): BackendComponent
    {
        $carousel = SlateBackendComponent::make('carousel');

        $carousel->setContent(
            SlateBackendComponent::make('carousel-content')
                ->setContents([
                    SlateBackendComponent::make('carousel-item')
                        ->setContent(
                            SlateBackendComponent::make('card')
                                ->setAttribute('class', 'p-6 text-center')
                                ->setContent('Slide 1')
                        ),
                    SlateBackendComponent::make('carousel-item')
                        ->setContent(
                            SlateBackendComponent::make('card')
                                ->setAttribute('class', 'p-6 text-center')
                                ->setContent('Slide 2')
                        ),
                    SlateBackendComponent::make('carousel-item')
                        ->setContent(
                            SlateBackendComponent::make('card')
                                ->setAttribute('class', 'p-6 text-center')
                                ->setContent('Slide 3')
                        ),
                ])
        );

        $carousel->setContents([
            SlateBackendComponent::make('carousel-previous'),
            SlateBackendComponent::make('carousel-next'),
        ]);

        return $carousel;
    }

    public static function withImages(): BackendComponent
    {
        $carousel = SlateBackendComponent::make('carousel');

        $carousel->setContent(
            SlateBackendComponent::make('carousel-content')
                ->setContents([
                    SlateBackendComponent::make('carousel-item')
                        ->setContent(
                            ComponentBuilder::make(ComponentEnum::DIV)
                                ->setTheme('border-radius', 'sm')
                                ->setTheme('overflow', 'hidden')
                                ->setContent(self::placeholder())
                        ),
                    SlateBackendComponent::make('carousel-item')
                        ->setContent(
                            ComponentBuilder::make(ComponentEnum::DIV)
                                ->setTheme('border-radius', 'sm')
                                ->setTheme('overflow', 'hidden')
                                ->setContent(self::placeholder())
                        ),
                    SlateBackendComponent::make('carousel-item')
                        ->setContent(
                            ComponentBuilder::make(ComponentEnum::DIV)
                                ->setTheme('border-radius', 'sm')
                                ->setTheme('overflow', 'hidden')
                                ->setContent(self::placeholder())
                        ),
                ])
        );

        $carousel->setContents([
            SlateBackendComponent::make('carousel-previous'),
            SlateBackendComponent::make('carousel-next'),
        ]);

        return $carousel;
    }
}
