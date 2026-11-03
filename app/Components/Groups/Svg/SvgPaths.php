<?php

declare(strict_types=1);

namespace App\Components\Groups\Svg;

use App\Components\ContainerOptions;
use App\Components\Contracts\Component;
use Juaniquillo\BackendComponents\Contracts\BackendComponent;
use Juaniquillo\SvgBackendComponents\Builders\SvgComponentBuilder;
use Juaniquillo\SvgBackendComponents\Shapes\SvgPath;
use Juaniquillo\SvgBackendComponents\Shapes\SvgShape;
use Juaniquillo\SvgBackendComponents\SvgComponentEnum;

class SvgPaths implements Component
{
    const NAME = 'SVG Paths and Polygons';

    public static function list(): array
    {
        return [
            'paths' => self::paths(),
            'drawn' => self::drawn(),
            'commands' => self::commands(),
        ];
    }

    public static function options(): ContainerOptions
    {
        return new ContainerOptions;
    }

    public static function paths(): BackendComponent
    {
        return SvgComponentBuilder::make(SvgComponentEnum::SVG)
            ->setXmlns()
            ->setViewBox(0, 0, 200, 120)
            ->setSize(200, 120)
            ->setContents([
                SvgShape::polyline('10,110 40,60 70,90 100,40')
                    ->setFill('none')
                    ->setStroke('#8b5cf6')
                    ->setStrokeWidth(4),
                SvgShape::polygon('130,100 150,60 170,100')
                    ->setFill('#f59e0b'),
                SvgShape::path(
                    SvgPath::start(120, 30)->curveTo(140, 10, 170, 10, 185, 30)
                )
                    ->setFill('none')
                    ->setStroke('#64748b')
                    ->setStrokeWidth(4),
            ]);
    }

    public static function drawn(): BackendComponent
    {
        return SvgComponentBuilder::make(SvgComponentEnum::SVG)
            ->setXmlns()
            ->setViewBox(0, 0, 200, 120)
            ->setSize(200, 120)
            ->setContents([
                SvgShape::path(
                    SvgPath::start(20, 100)
                        ->lineTo(20, 40)
                        ->lineTo(100, 10)
                        ->lineTo(180, 40)
                        ->lineTo(180, 100)
                        ->close()
                )
                    ->setFill('#e0e7ff')
                    ->setStroke('#4f46e5')
                    ->setStrokeWidth(4),
                SvgShape::rect(85, 65, 30, 35)
                    ->setFill('#4f46e5'),
            ]);
    }

    public static function commands(): BackendComponent
    {
        return SvgComponentBuilder::make(SvgComponentEnum::SVG)
            ->setXmlns()
            ->setViewBox(0, 0, 200, 120)
            ->setSize(200, 120)
            ->setContents([
                SvgShape::path(
                    SvgPath::start(30, 100)
                        ->lineToRelative(0, -50)
                        ->arcTo(40, 40, 0, false, true, 110, 50)
                        ->lineToRelative(0, 50)
                        ->close()
                )
                    ->setFill('#e0e7ff')
                    ->setStroke('#4f46e5')
                    ->setStrokeWidth(4),
                SvgShape::path(
                    SvgPath::start(130, 90)
                        ->quadraticTo(140, 55, 150, 90)
                        ->smoothQuadraticTo(170, 90)
                        ->smoothQuadraticTo(190, 90)
                )
                    ->setFill('none')
                    ->setStroke('#64748b')
                    ->setStrokeWidth(4),
            ]);
    }
}
