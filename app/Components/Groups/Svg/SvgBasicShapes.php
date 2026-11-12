<?php

declare(strict_types=1);

namespace App\Components\Groups\Svg;

use App\Components\ContainerOptions;
use App\Components\Contracts\Component;
use Juaniquillo\BaseBackendComponent\Contracts\BackendComponent;
use Juaniquillo\SvgBackendComponents\Factories\SvgComponentFactory;
use Juaniquillo\SvgBackendComponents\Shapes\SvgShape;
use Juaniquillo\SvgBackendComponents\SvgComponentEnum;

class SvgBasicShapes implements Component
{
    const NAME = 'SVG Basic Shapes';

    public static function list(): array
    {
        return [
            'shapes' => self::shapes(),
        ];
    }

    public static function options(): ContainerOptions
    {
        return new ContainerOptions;
    }

    public static function shapes(): BackendComponent
    {
        return SvgComponentFactory::make(SvgComponentEnum::SVG)
            ->setXmlns()
            ->setViewBox(0, 0, 200, 120)
            ->setSize(200, 120)
            ->setContents([
                SvgShape::rect(10, 10, 80, 50)
                    ->setAttribute('rx', 8)
                    ->setFill('#3b82f6'),
                SvgShape::circle(140, 35, 25)
                    ->setFill('#ef4444'),
                SvgShape::ellipse(50, 90, 35, 18)
                    ->setFill('#22c55e'),
                SvgShape::line(110, 80, 180, 100)
                    ->setStroke('#64748b')
                    ->setStrokeWidth(4),
            ]);
    }
}
