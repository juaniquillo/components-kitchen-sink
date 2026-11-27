<?php

declare(strict_types=1);

namespace App\DesignSystem;

use Juaniquillo\SvgBackendComponents\Factories\SvgComponentFactory;
use Juaniquillo\SvgBackendComponents\Shapes\SvgPath;
use Juaniquillo\SvgBackendComponents\Shapes\SvgShape;
use Juaniquillo\SvgBackendComponents\SvgBackendComponent;
use Juaniquillo\SvgBackendComponents\SvgComponentEnum;
use Juaniquillo\SvgBackendComponents\SvgRenderer;

/**
 * The theme switch icons, rendered once into static files.
 *
 * The shapes are monochrome masks, not coloured images: the toggle paints
 * them with the button's text colour, so light and dark need no second
 * set. Regenerate with `php artisan icons:build` after changing a shape.
 */
final class Icons
{
    /**
     * @var array<int, string>
     */
    public const FILES = ['sun.svg', 'moon.svg', 'auto.svg', 'overview.svg', 'invoices.svg', 'clients.svg', 'settings.svg', 'menu.svg', 'close.svg', 'bolt.svg', 'shield.svg', 'layers.svg'];

    public static function build(string $directory): void
    {
        $icons = [
            'sun' => self::sun(),
            'moon' => self::moon(),
            'auto' => self::auto(),
            'overview' => self::overview(),
            'invoices' => self::invoices(),
            'clients' => self::clients(),
            'settings' => self::settings(),
            'menu' => self::menu(),
            'close' => self::close(),
            'bolt' => self::bolt(),
            'shield' => self::shield(),
            'layers' => self::layers(),
        ];

        foreach ($icons as $name => $icon) {
            SvgRenderer::make($icon)->saveTo($directory.'/'.$name.'.svg');
        }
    }

    public static function sun(): SvgBackendComponent
    {
        $rays = [];

        for ($i = 0; $i < 8; $i++) {
            $angle = deg2rad($i * 45);

            $rays[] = SvgShape::line(
                self::coord(12 + 7 * cos($angle)),
                self::coord(12 + 7 * sin($angle)),
                self::coord(12 + 10 * cos($angle)),
                self::coord(12 + 10 * sin($angle)),
            )
                ->setAttribute('stroke', 'black')
                ->setAttribute('stroke-width', '2')
                ->setAttribute('stroke-linecap', 'round');
        }

        return SvgComponentFactory::make(SvgComponentEnum::SVG)
            ->setXmlns()
            ->setViewBox(0, 0, 24, 24)
            ->setContents([
                SvgShape::circle(12, 12, 4)->setAttribute('fill', 'black'),
                ...$rays,
            ]);
    }

    public static function moon(): SvgBackendComponent
    {
        return SvgComponentFactory::make(SvgComponentEnum::SVG)
            ->setXmlns()
            ->setViewBox(0, 0, 24, 24)
            ->setContent(
                SvgShape::path('M21 12.79A9 9 0 1 1 11.21 3 7 7 0 0 0 21 12.79z')
                    ->setAttribute('fill', 'black')
            );
    }

    /**
     * Three round-capped lines for the mobile menu button.
     */
    public static function menu(): SvgBackendComponent
    {
        $lines = [];

        foreach ([6, 12, 18] as $y) {
            $lines[] = SvgShape::line(4, $y, 20, $y)
                ->setAttribute('stroke', 'black')
                ->setAttribute('stroke-width', '2')
                ->setAttribute('stroke-linecap', 'round');
        }

        return SvgComponentFactory::make(SvgComponentEnum::SVG)
            ->setXmlns()
            ->setViewBox(0, 0, 24, 24)
            ->setContents($lines);
    }

    public static function auto(): SvgBackendComponent
    {
        return SvgComponentFactory::make(SvgComponentEnum::SVG)
            ->setXmlns()
            ->setViewBox(0, 0, 24, 24)
            ->setContents([
                SvgShape::circle(12, 12, 8)
                    ->setAttribute('fill', 'none')
                    ->setAttribute('stroke', 'black')
                    ->setAttribute('stroke-width', '2'),
                SvgShape::path(
                    SvgPath::start(12, 4)->arcTo(8, 8, 0, false, true, 12, 20)->close()
                )->setAttribute('fill', 'black'),
            ]);
    }

    /**
     * A 2x2 grid of rounded squares.
     */
    public static function overview(): SvgBackendComponent
    {
        $cells = [];

        foreach ([[3, 3], [14, 3], [3, 14], [14, 14]] as [$x, $y]) {
            $cells[] = SvgShape::rect($x, $y, 7, 7)
                ->setAttribute('fill', 'black')
                ->setAttribute('rx', 2);
        }

        return SvgComponentFactory::make(SvgComponentEnum::SVG)
            ->setXmlns()
            ->setViewBox(0, 0, 24, 24)
            ->setContents($cells);
    }

    /**
     * A page outline with three ruled lines.
     */
    public static function invoices(): SvgBackendComponent
    {
        $lines = [];

        foreach ([[9, 8, 15, 8], [9, 12, 15, 12], [9, 16, 13, 16]] as [$x1, $y1, $x2, $y2]) {
            $lines[] = SvgShape::line($x1, $y1, $x2, $y2)
                ->setAttribute('stroke', 'black')
                ->setAttribute('stroke-width', '2')
                ->setAttribute('stroke-linecap', 'round');
        }

        return SvgComponentFactory::make(SvgComponentEnum::SVG)
            ->setXmlns()
            ->setViewBox(0, 0, 24, 24)
            ->setContents([
                SvgShape::rect(6, 3, 12, 18)
                    ->setAttribute('fill', 'none')
                    ->setAttribute('stroke', 'black')
                    ->setAttribute('stroke-width', '2')
                    ->setAttribute('rx', '2'),
                ...$lines,
            ]);
    }

    /**
     * Two heads over two shoulder arcs.
     */
    public static function clients(): SvgBackendComponent
    {
        return SvgComponentFactory::make(SvgComponentEnum::SVG)
            ->setXmlns()
            ->setViewBox(0, 0, 24, 24)
            ->setContents([
                SvgShape::circle(9, 8, 3)
                    ->setAttribute('fill', 'black'),
                SvgShape::path('M3.5 19c0-3 2.5-5 5.5-5s5.5 2 5.5 5')
                    ->setAttribute('fill', 'none')
                    ->setAttribute('stroke', 'black')
                    ->setAttribute('stroke-width', '2')
                    ->setAttribute('stroke-linecap', 'round'),
                SvgShape::circle(17, 9, 2)
                    ->setAttribute('fill', 'black'),
                SvgShape::path('M15.5 14.3c2.4.5 4 2.2 4 4.7')
                    ->setAttribute('fill', 'none')
                    ->setAttribute('stroke', 'black')
                    ->setAttribute('stroke-width', '2')
                    ->setAttribute('stroke-linecap', 'round'),
            ]);
    }

    /**
     * Three sliders with knobs.
     */
    public static function settings(): SvgBackendComponent
    {
        $contents = [];

        foreach ([[6, 9], [12, 15], [18, 8]] as [$y, $knob]) {
            $contents[] = SvgShape::line(3, $y, 21, $y)
                ->setAttribute('stroke', 'black')
                ->setAttribute('stroke-width', '2')
                ->setAttribute('stroke-linecap', 'round');
            $contents[] = SvgShape::circle($knob, $y, 3)
                ->setAttribute('fill', 'black');
        }

        return SvgComponentFactory::make(SvgComponentEnum::SVG)
            ->setXmlns()
            ->setViewBox(0, 0, 24, 24)
            ->setContents($contents);
    }

    /**
     * An X for the sheet close button.
     */
    public static function close(): SvgBackendComponent
    {
        $lines = [];

        foreach ([[6, 6, 18, 18], [18, 6, 6, 18]] as [$x1, $y1, $x2, $y2]) {
            $lines[] = SvgShape::line($x1, $y1, $x2, $y2)
                ->setAttribute('stroke', 'black')
                ->setAttribute('stroke-width', '2')
                ->setAttribute('stroke-linecap', 'round');
        }

        return SvgComponentFactory::make(SvgComponentEnum::SVG)
            ->setXmlns()
            ->setViewBox(0, 0, 24, 24)
            ->setContents($lines);
    }

    /**
     * A lightning bolt for the landing feature cards.
     */
    public static function bolt(): SvgBackendComponent
    {
        return SvgComponentFactory::make(SvgComponentEnum::SVG)
            ->setXmlns()
            ->setViewBox(0, 0, 24, 24)
            ->setContent(
                SvgShape::path('M13 2 4 14h6l-1 8 9-12h-6l1-8z')
                    ->setAttribute('fill', 'black')
            );
    }

    /**
     * A shield outline with a check for the landing feature cards.
     */
    public static function shield(): SvgBackendComponent
    {
        return SvgComponentFactory::make(SvgComponentEnum::SVG)
            ->setXmlns()
            ->setViewBox(0, 0, 24, 24)
            ->setContents([
                SvgShape::path('M12 2l8 3v6c0 5-3.5 9-8 11-4.5-2-8-6-8-11V5l8-3z')
                    ->setAttribute('fill', 'none')
                    ->setAttribute('stroke', 'black')
                    ->setAttribute('stroke-width', '2')
                    ->setAttribute('stroke-linejoin', 'round'),
                SvgShape::path('M8.5 12l2.5 2.5 4.5-5')
                    ->setAttribute('fill', 'none')
                    ->setAttribute('stroke', 'black')
                    ->setAttribute('stroke-width', '2')
                    ->setAttribute('stroke-linecap', 'round')
                    ->setAttribute('stroke-linejoin', 'round'),
            ]);
    }

    /**
     * Three stacked diamonds for the landing feature cards.
     */
    public static function layers(): SvgBackendComponent
    {
        return SvgComponentFactory::make(SvgComponentEnum::SVG)
            ->setXmlns()
            ->setViewBox(0, 0, 24, 24)
            ->setContents([
                SvgShape::path('M12 3l9 5-9 5-9-5 9-5z')
                    ->setAttribute('fill', 'black'),
                SvgShape::polyline('3,12 12,17 21,12')
                    ->setAttributes([
                        'fill' => 'none',
                        'stroke' => 'black',
                        'stroke-width' => '2',
                        'stroke-linecap' => 'round',
                        'stroke-linejoin' => 'round',
                    ]),
                SvgShape::polyline('3,16 12,21 21,16')
                    ->setAttributes([
                        'fill' => 'none',
                        'stroke' => 'black',
                        'stroke-width' => '2',
                        'stroke-linecap' => 'round',
                        'stroke-linejoin' => 'round',
                    ]),
            ]);
    }

    private static function coord(float $value): string
    {
        return sprintf('%.2f', $value);
    }
}
