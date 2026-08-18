<?php

declare(strict_types=1);

namespace App\Components\Groups;

use App\Components\ContainerOptions;
use App\Components\Contracts\Component;
use Juaniquillo\BackendComponents\Builders\ComponentBuilder;
use Juaniquillo\BackendComponents\Contracts\BackendComponent;
use Juaniquillo\BackendComponents\Contracts\CompoundComponent;
use Juaniquillo\BackendComponents\Enums\ComponentEnum;

class BootstrapCarousel implements Component
{
    const NAME = 'Bootstrap Carousel';

    public static function list(): array
    {
        return [
            'default' => self::default(),
            'with-indicators' => self::withIndicators(),
            'with-controls' => self::withControls(),
            // 'full' => self::full(),
        ];
    }

    public static function options(): ContainerOptions
    {
        return new ContainerOptions;
    }

    public static function default(): BackendComponent
    {
        return self::baseCarousel(
            carouselId: 'carousel-default',
            contents: [
                self::item(self::placeholder(), true),
                self::item(self::placeholder()),
                self::item(self::placeholder()),
            ],
            subtitle: 'Default',
        );
    }

    public static function withIndicators(): BackendComponent
    {
        return self::baseCarousel(
            carouselId: 'carousel-indicators',
            contents: [
                self::indicators('carousel-indicators', 3),
                self::item(self::placeholder(), true),
                self::item(self::placeholder()),
                self::item(self::placeholder()),
            ],
            subtitle: 'With Indicators',
        );
    }

    public static function withControls(): BackendComponent
    {
        return self::baseCarousel(
            carouselId: 'carousel-controls',
            contents: [
                self::item(self::placeholder(), true),
                self::item(self::placeholder()),
                self::item(self::placeholder()),
            ],
            withControls: true,
            subtitle: 'With controls',
        );
    }

    public static function full(): BackendComponent
    {
        return self::baseCarousel(
            carouselId: 'carousel-full',
            contents: [
                self::indicators('carousel-full', 3),
                self::item('Slide 1', true, 'First slide', 'Content for the first slide.'),
                self::item('Slide 2', false, 'Second slide', 'Content for the second slide.'),
                self::item('Slide 3', false, 'Third slide', 'Content for the third slide.'),
            ],
            withControls: true,
            subtitle: 'Full carousel',
        );
    }

    private static function item(string|CompoundComponent $label, bool $active = false, ?string $title = null, ?string $description = null): BackendComponent
    {
        $classes = 'carousel-item';
        if ($active) {
            $classes .= ' active';
        }

        $contents = [];

        $contents[] = ComponentBuilder::make(ComponentEnum::DIV)
            ->setContent($label)
            ->setAttributes(['class' => 'd-block w-100']);

        if ($title !== null) {
            $caption = ComponentBuilder::make(ComponentEnum::DIV)
                ->setAttributes(['class' => 'carousel-caption d-none d-md-block'])
                ->setContents([
                    ComponentBuilder::make(ComponentEnum::H5)
                        ->setContent($title),
                ]);

            if ($description !== null) {
                $caption->setContent(
                    ComponentBuilder::make(ComponentEnum::PARAGRAPH)
                        ->setContent($description)
                );
            }

            $contents[] = $caption;
        }

        return ComponentBuilder::make(ComponentEnum::DIV)
            ->setAttributes(['class' => $classes])
            ->setContents($contents);
    }

    private static function indicators(string $carouselId, int $count): BackendComponent
    {
        $buttons = [];

        for ($i = 0; $i < $count; $i++) {
            $attributes = [
                'type' => 'button',
                'data-bs-target' => '#'.$carouselId,
                'data-bs-slide-to' => (string) $i,
                'aria-label' => 'Slide '.($i + 1),
            ];

            if ($i === 0) {
                $attributes['class'] = 'active';
                $attributes['aria-current'] = 'true';
            }

            $buttons[] = ComponentBuilder::make(ComponentEnum::BUTTON)
                ->setAttributes($attributes);
        }

        return ComponentBuilder::make(ComponentEnum::DIV)
            ->setAttributes(['class' => 'carousel-indicators'])
            ->setContents($buttons);
    }

    public static function placeholder(string $alt = ''): CompoundComponent
    {
        return ComponentBuilder::make(ComponentEnum::IMG)
            ->setAttributes([
                'alt' => $alt,
                'src' => 'https://placehold.co/400x200',
                'class' => 'img-fluid',
            ]);
    }

    private static function baseCarousel(string $carouselId, array $contents, bool $withControls = false, ?string $subtitle = null): BackendComponent
    {
        $carouselContents = [];

        if ($subtitle) {
            $carouselContents[] = ComponentBuilder::make(ComponentEnum::H5)
                ->setContent($subtitle)
                ->setTheme('margin', 'bottom-xs');
        }

        $inner = ComponentBuilder::make(ComponentEnum::DIV)
            ->setAttributes(['class' => 'carousel-inner'])
            ->setContents($contents);

        $carouselContents[] = $inner;

        if ($withControls) {
            $carouselContents[] = ComponentBuilder::make(ComponentEnum::BUTTON)
                ->setAttributes([
                    'class' => 'carousel-control-prev',
                    'type' => 'button',
                    'data-bs-target' => '#'.$carouselId,
                    'data-bs-slide' => 'prev',
                ])
                ->setContents([
                    ComponentBuilder::make(ComponentEnum::SPAN)
                        ->setAttributes(['class' => 'carousel-control-prev-icon', 'aria-hidden' => 'true']),
                    ComponentBuilder::make(ComponentEnum::SPAN)
                        ->setAttributes(['class' => 'visually-hidden'])
                        ->setContent('Previous'),
                ]);

            $carouselContents[] = ComponentBuilder::make(ComponentEnum::BUTTON)
                ->setAttributes([
                    'class' => 'carousel-control-next',
                    'type' => 'button',
                    'data-bs-target' => '#'.$carouselId,
                    'data-bs-slide' => 'next',
                ])
                ->setContents([
                    ComponentBuilder::make(ComponentEnum::SPAN)
                        ->setAttributes(['class' => 'carousel-control-next-icon', 'aria-hidden' => 'true']),
                    ComponentBuilder::make(ComponentEnum::SPAN)
                        ->setAttributes(['class' => 'visually-hidden'])
                        ->setContent('Next'),
                ]);
        }

        return ComponentBuilder::make(ComponentEnum::DIV)
            ->setAttributes([
                'id' => $carouselId,
                'class' => 'carousel slide',
                'data-bs-ride' => 'carousel',
            ])
            ->setTheme('padding', [
                'top-xs',
                'top-xs',
            ])
            ->setContents($carouselContents);
    }
}
