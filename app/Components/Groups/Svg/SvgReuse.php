<?php

declare(strict_types=1);

namespace App\Components\Groups\Svg;

use App\Components\ContainerOptions;
use App\Components\Contracts\Component;
use Juaniquillo\BackendComponents\Contracts\BackendComponent;
use Juaniquillo\SvgBackendComponents\Builders\SvgComponentBuilder;
use Juaniquillo\SvgBackendComponents\Builders\SvgLocalThemeComponentBuilder;
use Juaniquillo\SvgBackendComponents\Shapes\SvgShape;
use Juaniquillo\SvgBackendComponents\SvgComponentEnum;

class SvgReuse implements Component
{
    const NAME = 'SVG Reuse and Text';

    public static function list(): array
    {
        return [
            'reuse' => self::reuse(),
            'caption' => self::caption(),
        ];
    }

    public static function options(): ContainerOptions
    {
        return new ContainerOptions;
    }

    public static function reuse(): BackendComponent
    {
        return SvgComponentBuilder::make(SvgComponentEnum::SVG)
            ->setXmlns()
            ->setViewBox(0, 0, 200, 100)
            ->setSize(200, 100)
            ->setContents([
                SvgComponentBuilder::make(SvgComponentEnum::DEFS)
                    ->setContents([
                        SvgShape::circle(0, 0, 15)
                            ->setAttribute('id', 'dot')
                            ->setFill('#06b6d4'),
                    ]),
                SvgComponentBuilder::make(SvgComponentEnum::G)
                    ->setStroke('#64748b')
                    ->setStrokeWidth(2)
                    ->setContents([
                        SvgComponentBuilder::make(SvgComponentEnum::TITLE)->setContent('Reused dots'),
                        SvgComponentBuilder::make(SvgComponentEnum::DESC)->setContent('The same circle, placed twice with use.'),
                        SvgComponentBuilder::make(SvgComponentEnum::USE)
                            ->setAttributes(['href' => '#dot', 'x' => '30', 'y' => '50']),
                        SvgComponentBuilder::make(SvgComponentEnum::USE)
                            ->setAttributes(['href' => '#dot', 'x' => '90', 'y' => '50']),
                    ]),
            ]);
    }

    public static function caption(): BackendComponent
    {
        return SvgComponentBuilder::make(SvgComponentEnum::SVG)
            ->setXmlns()
            ->setViewBox(0, 0, 200, 80)
            ->setSize(200, 80)
            ->setContents([
                SvgLocalThemeComponentBuilder::make(SvgComponentEnum::TEXT)
                    ->setTheme('fill', 'muted')
                    ->setAttributes(['x' => '100', 'y' => '40', 'text-anchor' => 'middle', 'font-size' => '20'])
                    ->setContents([
                        'Hello, ',
                        SvgLocalThemeComponentBuilder::make(SvgComponentEnum::TSPAN)
                            ->setTheme('fill', 'accent')
                            ->setAttribute('font-weight', 'bold')
                            ->setContent('SVG'),
                    ]),
            ]);
    }
}
