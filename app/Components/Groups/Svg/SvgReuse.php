<?php

declare(strict_types=1);

namespace App\Components\Groups\Svg;

use App\Components\ContainerOptions;
use App\Components\Contracts\Component;
use Juaniquillo\BaseBackendComponent\Contracts\BackendComponent;
use Juaniquillo\SvgBackendComponents\Factories\SvgComponentFactory;
use Juaniquillo\SvgBackendComponents\Factories\SvgLocalThemeComponentFactory;
use Juaniquillo\SvgBackendComponents\Shapes\SvgShape;
use Juaniquillo\SvgBackendComponents\SvgComponentEnum;

class SvgReuse implements Component
{
    const NAME = 'SVG Reuse and Text';

    public static function list(): array
    {
        return [
            'reuse' => self::reuse(),
            'symbol' => self::symbol(),
            'caption' => self::caption(),
        ];
    }

    public static function options(): ContainerOptions
    {
        return new ContainerOptions;
    }

    public static function reuse(): BackendComponent
    {
        return SvgComponentFactory::make(SvgComponentEnum::SVG)
            ->setXmlns()
            ->setViewBox(0, 0, 200, 100)
            ->setSize(200, 100)
            ->setContents([
                SvgComponentFactory::make(SvgComponentEnum::DEFS)
                    ->setContents([
                        SvgShape::circle(0, 0, 15)
                            ->setAttribute('id', 'dot')
                            ->setFill('#06b6d4'),
                    ]),
                SvgComponentFactory::make(SvgComponentEnum::G)
                    ->setStroke('#64748b')
                    ->setStrokeWidth(2)
                    ->setContents([
                        SvgComponentFactory::make(SvgComponentEnum::TITLE)->setContent('Reused dots'),
                        SvgComponentFactory::make(SvgComponentEnum::DESC)->setContent('The same circle, placed twice with use.'),
                        SvgComponentFactory::make(SvgComponentEnum::USE)
                            ->setAttributes(['href' => '#dot', 'x' => '30', 'y' => '50']),
                        SvgComponentFactory::make(SvgComponentEnum::USE)
                            ->setAttributes(['href' => '#dot', 'x' => '90', 'y' => '50']),
                    ]),
            ]);
    }

    public static function symbol(): BackendComponent
    {
        return SvgComponentFactory::make(SvgComponentEnum::SVG)
            ->setXmlns()
            ->setViewBox(0, 0, 200, 100)
            ->setSize(200, 100)
            ->setContents([
                SvgComponentFactory::make(SvgComponentEnum::DEFS)
                    ->setContents([
                        SvgComponentFactory::make(SvgComponentEnum::SYMBOL)
                            ->setAttribute('id', 'badge')
                            ->setViewBox(0, 0, 24, 24)
                            ->setContent(
                                SvgShape::path('M12 2l3 7h7l-6 4 2 7-6-4-6 4 2-7-6-4h7z')
                                    ->setFill('#f59e0b')
                            ),
                    ]),
                SvgComponentFactory::make(SvgComponentEnum::USE)
                    ->setAttribute('href', '#badge')
                    ->setAttributes(['x' => '34', 'y' => '26'])
                    ->setSize(48),
                SvgComponentFactory::make(SvgComponentEnum::USE)
                    ->setAttribute('href', '#badge')
                    ->setAttributes(['x' => '118', 'y' => '38'])
                    ->setSize(24),
            ]);
    }

    public static function caption(): BackendComponent
    {
        return SvgComponentFactory::make(SvgComponentEnum::SVG)
            ->setXmlns()
            ->setViewBox(0, 0, 200, 80)
            ->setSize(200, 80)
            ->setContents([
                SvgLocalThemeComponentFactory::make(SvgComponentEnum::TEXT)
                    ->setTheme('fill', 'muted')
                    ->setAttributes(['x' => '100', 'y' => '40', 'text-anchor' => 'middle', 'font-size' => '20'])
                    ->setContents([
                        'Hello, ',
                        SvgLocalThemeComponentFactory::make(SvgComponentEnum::TSPAN)
                            ->setTheme('fill', 'accent')
                            ->setAttribute('font-weight', 'bold')
                            ->setContent('SVG'),
                    ]),
            ]);
    }
}
