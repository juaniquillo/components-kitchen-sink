<?php

declare(strict_types=1);

namespace App\Components\Groups\Svg;

use App\Components\ContainerOptions;
use App\Components\Contracts\Component;
use Juaniquillo\BackendComponents\Contracts\BackendComponent;
use Juaniquillo\SvgBackendComponents\Factories\SvgComponentFactory;
use Juaniquillo\SvgBackendComponents\Shapes\SvgShape;
use Juaniquillo\SvgBackendComponents\SvgComponentEnum;

class SvgPaint implements Component
{
    const NAME = 'SVG Paint and Patterns';

    public static function list(): array
    {
        return [
            'gradients' => self::gradients(),
            'clip and pattern' => self::clipAndPattern(),
            'opacity' => self::opacity(),
            'mask' => self::mask(),
        ];
    }

    public static function options(): ContainerOptions
    {
        return new ContainerOptions;
    }

    public static function gradients(): BackendComponent
    {
        return SvgComponentFactory::make(SvgComponentEnum::SVG)
            ->setXmlns()
            ->setViewBox(0, 0, 200, 100)
            ->setSize(200, 100)
            ->setContents([
                SvgComponentFactory::make(SvgComponentEnum::DEFS)
                    ->setContents([
                        SvgComponentFactory::make(SvgComponentEnum::LINEAR_GRADIENT)
                            ->setAttributes(['id' => 'fade', 'x1' => '0', 'y1' => '0', 'x2' => '1', 'y2' => '0'])
                            ->setContents([
                                SvgComponentFactory::make(SvgComponentEnum::STOP)
                                    ->setAttributes(['offset' => '0%', 'stop-color' => '#3b82f6']),
                                SvgComponentFactory::make(SvgComponentEnum::STOP)
                                    ->setAttributes(['offset' => '100%', 'stop-color' => '#a855f7']),
                            ]),
                        SvgComponentFactory::make(SvgComponentEnum::RADIAL_GRADIENT)
                            ->setAttributes(['id' => 'glow'])
                            ->setContents([
                                SvgComponentFactory::make(SvgComponentEnum::STOP)
                                    ->setAttributes(['offset' => '0%', 'stop-color' => '#fde047']),
                                SvgComponentFactory::make(SvgComponentEnum::STOP)
                                    ->setAttributes(['offset' => '100%', 'stop-color' => '#f97316']),
                            ]),
                    ]),
                SvgShape::rect(10, 10, 85, 80)
                    ->setFill('url(#fade)'),
                SvgShape::circle(150, 50, 40)
                    ->setFill('url(#glow)'),
            ]);
    }

    public static function clipAndPattern(): BackendComponent
    {
        return SvgComponentFactory::make(SvgComponentEnum::SVG)
            ->setXmlns()
            ->setViewBox(0, 0, 200, 100)
            ->setSize(200, 100)
            ->setContents([
                SvgComponentFactory::make(SvgComponentEnum::DEFS)
                    ->setContents([
                        SvgComponentFactory::make(SvgComponentEnum::CLIP_PATH)
                            ->setAttributes(['id' => 'round'])
                            ->setContents([
                                SvgShape::circle(50, 50, 40),
                            ]),
                        SvgComponentFactory::make(SvgComponentEnum::PATTERN)
                            ->setAttribute('id', 'dots')
                            ->setAttribute('patternUnits', 'userSpaceOnUse')
                            ->setSize(12)
                            ->setContents([
                                SvgShape::circle(3, 3, 2)
                                    ->setFill('#3b82f6'),
                            ]),
                    ]),
                SvgShape::rect(10, 10, 80, 80)
                    ->setFill('#f97316')
                    ->setAttribute('clip-path', 'url(#round)'),
                SvgShape::rect(110, 10, 80, 80)
                    ->setFill('url(#dots)'),
            ]);
    }

    public static function opacity(): BackendComponent
    {
        return SvgComponentFactory::make(SvgComponentEnum::SVG)
            ->setXmlns()
            ->setViewBox(0, 0, 200, 100)
            ->setSize(200, 100)
            ->setContents([
                SvgShape::circle(85, 50, 40)
                    ->setFill('#3b82f6')
                    ->setOpacity(0.6),
                SvgShape::circle(115, 50, 40)
                    ->setFill('#f59e0b')
                    ->setOpacity(0.6),
            ]);
    }

    public static function mask(): BackendComponent
    {
        return SvgComponentFactory::make(SvgComponentEnum::SVG)
            ->setXmlns()
            ->setViewBox(0, 0, 200, 100)
            ->setSize(200, 100)
            ->setContents([
                SvgComponentFactory::make(SvgComponentEnum::DEFS)
                    ->setContents([
                        SvgComponentFactory::make(SvgComponentEnum::RADIAL_GRADIENT)
                            ->setAttribute('id', 'mask-fade')
                            ->setContents([
                                SvgComponentFactory::make(SvgComponentEnum::STOP)
                                    ->setAttributes(['offset' => '30%', 'stop-color' => '#ffffff']),
                                SvgComponentFactory::make(SvgComponentEnum::STOP)
                                    ->setAttributes(['offset' => '100%', 'stop-color' => '#000000']),
                            ]),
                        SvgComponentFactory::make(SvgComponentEnum::MASK)
                            ->setAttribute('id', 'fade')
                            ->setContents([
                                SvgShape::rect(10, 10, 180, 80)
                                    ->setFill('url(#mask-fade)'),
                            ]),
                    ]),
                SvgShape::rect(10, 10, 180, 80)
                    ->setFill('#3b82f6')
                    ->setAttribute('mask', 'url(#fade)'),
            ]);
    }
}
