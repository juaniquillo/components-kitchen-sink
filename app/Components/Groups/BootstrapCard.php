<?php

declare(strict_types=1);

namespace App\Components\Groups;

use App\Components\ContainerOptions;
use App\Components\Contracts\Component;
use Juaniquillo\BackendComponents\Builders\ComponentBuilder;
use Juaniquillo\BackendComponents\Contracts\CompoundComponent;
use Juaniquillo\BackendComponents\Enums\ComponentEnum;

class BootstrapCard implements Component
{
    const NAME = 'Bootstrap Cards';

    public static function list(): array
    {
        return [
            self::simple(),
            self::noImage(),
        ];
    }

    public static function options(): ContainerOptions
    {
        return new ContainerOptions;
    }

    public static function simple(): CompoundComponent
    {
        return ComponentBuilder::make(ComponentEnum::DIV)
            ->setAttributes([
                'class' => 'card',
            ])
            ->setContents([
                self::placeholder(),
                self::body([
                    self::title('Simple card'),
                    self::content('This is simple content'),
                ]),
            ]);
    }

    public static function noImage(): CompoundComponent
    {
        return ComponentBuilder::make(ComponentEnum::DIV)
            ->setAttributes([
                'class' => 'card',
            ])
            ->setContents([
                self::body([
                    self::title('Simple card without image'),
                    self::subTitle('Subtitle included'),
                    self::content('Look mom... No image. Lorem ipsum dolor sit, amet consectetur adipisicing elit. Architecto porro, quo necessitatibus, soluta, similique illum est esse deleniti ut omnis dolorum assumenda laboriosam neque a ipsum dignissimos. Dolorum, autem eos.'),
                ]),
            ]);
    }

    public static function placeholder(string $alt = ''): CompoundComponent
    {
        return ComponentBuilder::make(ComponentEnum::IMG)
            ->setAttributes([
                'alt' => $alt,
                'src' => 'https://placehold.co/400x200',
                'class' => 'card-img-top',
            ]);
    }

    public static function body(array $contents): CompoundComponent
    {
        return ComponentBuilder::make(ComponentEnum::DIV)
            ->setAttribute('class', 'card-body')
            ->setContents($contents);
    }

    public static function title(string $title): CompoundComponent
    {
        return ComponentBuilder::make(ComponentEnum::H5)
            ->setAttribute('class', 'card-title')
            ->setContent($title);
    }

    public static function subTitle(string $subTitle): CompoundComponent
    {
        return ComponentBuilder::make(ComponentEnum::H6)
            ->setAttribute('class', 'card-subtitle mb-2 text-body-secondary')
            ->setContent($subTitle);
    }

    public static function content(string $content): CompoundComponent
    {
        return ComponentBuilder::make(ComponentEnum::PARAGRAPH)
            ->setAttribute('class', 'card-text')
            ->setContent($content);
    }
}
