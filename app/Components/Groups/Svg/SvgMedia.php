<?php

declare(strict_types=1);

namespace App\Components\Groups\Svg;

use App\Components\ContainerOptions;
use App\Components\Contracts\Component;
use Juaniquillo\BackendComponents\Contracts\BackendComponent;
use Juaniquillo\SvgBackendComponents\Builders\SvgComponentBuilder;
use Juaniquillo\SvgBackendComponents\SvgComponentEnum;

class SvgMedia implements Component
{
    const NAME = 'SVG Media';

    public static function list(): array
    {
        return [
            'image' => self::image(),
        ];
    }

    public static function options(): ContainerOptions
    {
        return new ContainerOptions;
    }

    public static function image(): BackendComponent
    {
        return SvgComponentBuilder::make(SvgComponentEnum::SVG)
            ->setXmlns()
            ->setViewBox(0, 0, 200, 100)
            ->setSize(200, 100)
            ->setContents([
                SvgComponentBuilder::make(SvgComponentEnum::IMAGE)
                    ->setAttribute('href', '/images/mail/yellow-lightning-bolt.png')
                    ->setAttribute('preserveAspectRatio', 'xMidYMid meet')
                    ->setAttributes(['x' => '70', 'y' => '10'])
                    ->setSize(60, 80),
            ]);
    }
}
