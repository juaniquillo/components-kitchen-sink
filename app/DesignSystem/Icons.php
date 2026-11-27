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
    public const FILES = ['sun.svg', 'moon.svg', 'auto.svg'];

    public static function build(string $directory): void
    {
        $icons = [
            'sun' => self::sun(),
            'moon' => self::moon(),
            'auto' => self::auto(),
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

    private static function coord(float $value): string
    {
        return sprintf('%.2f', $value);
    }
}
