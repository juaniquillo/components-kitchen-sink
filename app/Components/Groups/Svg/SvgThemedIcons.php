<?php

declare(strict_types=1);

namespace App\Components\Groups\Svg;

use App\Components\ContainerOptions;
use App\Components\Contracts\Component;
use Juaniquillo\BackendComponents\Contracts\BackendComponent;
use Juaniquillo\SvgBackendComponents\Builders\SvgLocalThemeComponentBuilder;
use Juaniquillo\SvgBackendComponents\SvgComponentEnum;

class SvgThemedIcons implements Component
{
    const NAME = 'SVG Themed Icons';

    public static function list(): array
    {
        return [
            'plus' => self::icon('M11 5h2v6h6v2h-6v6h-2v-6H5v-2h6z', 'primary'),
            'check' => self::icon('M9 16.17L4.83 12l-1.42 1.41L9 19 21 7l-1.41-1.41z', 'success'),
            'heart' => self::icon('M12 21.35l-1.45-1.32C5.4 15.36 2 12.28 2 8.5 2 5.42 4.42 3 7.5 3c1.74 0 3.41.81 4.5 2.09C13.09 3.81 14.76 3 16.5 3 19.58 3 22 5.42 22 8.5c0 3.78-3.4 6.86-8.55 11.54L12 21.35z', 'danger'),
            'play' => self::icon('M8 5v14l11-7z', 'warning'),
        ];
    }

    public static function options(): ContainerOptions
    {
        return new ContainerOptions;
    }

    /**
     * An icon painted by the app, not by a hardcoded fill.
     *
     * The theme lives in the app's `fill` group, so the same icon restyles
     * itself wherever the file is pasted. The path inherits the paint from
     * the class on the root.
     */
    public static function icon(string $d, string $variant): BackendComponent
    {
        return SvgLocalThemeComponentBuilder::make(SvgComponentEnum::SVG)
            ->setXmlns()
            ->setViewBox(0, 0, 24, 24)
            ->setSize(56)
            ->setTheme('fill', $variant)
            ->setContent(
                SvgLocalThemeComponentBuilder::make(SvgComponentEnum::PATH)
                    ->setAttribute('d', $d)
            );
    }
}
