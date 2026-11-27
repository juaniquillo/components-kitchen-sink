<?php

declare(strict_types=1);

namespace App\DesignSystem\Examples;

use Juaniquillo\BackendDesignSystem\Designs\Design;
use Juaniquillo\BaseBackendComponent\Exceptions\ThemeDoesNotExistsException;
use Juaniquillo\SvgBackendComponents\Factories\SvgComponentFactory;
use Juaniquillo\SvgBackendComponents\Shapes\SvgPath;
use Juaniquillo\SvgBackendComponents\Shapes\SvgShape;
use Juaniquillo\SvgBackendComponents\SvgBackendComponent;
use Juaniquillo\SvgBackendComponents\SvgComponentEnum;

/**
 * Small SVG charts for the dashboard example.
 *
 * Paint comes from the design's own `chart` theme group where one
 * exists (accent for the latest value, muted for context, grid for the
 * baseline). Designs without one fall back to currentColor, so the chart
 * inherits the card's text colour.
 */
final class Chart
{
    /**
     * A twelve-column bar chart with a baseline and month labels.
     *
     * @param  array<int, float|int>  $values
     * @param  array<int, string>  $labels
     * @param  array<int, string>  $tooltips
     */
    public static function bars(Design $design, array $values, array $labels, string $label, array $tooltips = []): SvgBackendComponent
    {
        $palette = self::palette($design);
        $max = max($values) ?: 1;
        $count = count($values);
        $step = 20;
        $barWidth = 12;
        $baseline = 72;

        $columns = [];

        foreach ($values as $index => $value) {
            $height = max(2, (int) round($value / $max * 60));

            $bar = SvgShape::rect($index * $step, $baseline - $height, $barWidth, $height)
                ->setAttribute('class', ($index === $count - 1 ? $palette['accent'] : $palette['muted']).' transition duration-150 group-hover:brightness-110 group-hover:opacity-100 motion-reduce:transition-none')
                ->setAttribute('opacity', $index === $count - 1 ? '1' : $palette['mutedOpacity'])
                ->setAttribute('rx', 2);

            if (isset($tooltips[$index])) {
                $bar->setContent(
                    SvgComponentFactory::make(SvgComponentEnum::TITLE)->setContent($tooltips[$index])
                );
            }

            $columns[] = SvgComponentFactory::make(SvgComponentEnum::G)
                ->setAttribute('class', 'group')
                ->setContents([
                    SvgShape::rect($index * $step, 0, $step, 96)
                        ->setAttribute('fill', 'transparent'),
                    $bar,
                    SvgComponentFactory::make(SvgComponentEnum::TEXT)
                        ->setContent($labels[$index] ?? '')
                        ->setAttributes([
                            'x' => $index * $step + 6,
                            'y' => 86,
                            'text-anchor' => 'middle',
                            'font-size' => '6',
                            'fill' => 'currentColor',
                            'opacity' => '0.6',
                            'class' => 'transition-opacity duration-150 group-hover:opacity-100 motion-reduce:transition-none',
                        ]),
                ]);
        }

        return SvgComponentFactory::make(SvgComponentEnum::SVG)
            ->setXmlns()
            ->setViewBox(0, 0, 232, 96)
            ->setAttributes([
                'class' => 'h-40 w-full',
                'role' => 'img',
                'aria-label' => $label,
            ])
            ->setContents([
                SvgComponentFactory::make(SvgComponentEnum::TITLE)->setContent($label),
                SvgComponentFactory::make(SvgComponentEnum::DESC)->setContent('Monthly revenue for the last twelve months.'),
                SvgShape::line(0, $baseline, 232, $baseline)
                    ->setAttribute('class', $palette['grid'])
                    ->setAttribute('opacity', '0.6'),
                ...$columns,
            ]);
    }

    /**
     * A donut with the total in the middle and a slice per channel.
     *
     * Slices are stroked circles sharing one circumference: each dash
     * is its share of the ring, offset by what came before, and the
     * group starts at twelve o'clock. Slices differ by stepped opacity
     * of the design's chart stroke, so no new theme variants are
     * needed; designs without a chart theme fall back to currentColor.
     *
     * @param  array<int, array{label: string, value: float}>  $channels
     */
    public static function donut(Design $design, array $channels, string $label): SvgBackendComponent
    {
        $palette = self::palette($design);
        $total = array_sum(array_column($channels, 'value')) ?: 1;

        $radius = 45;
        $circumference = 2 * M_PI * $radius;

        $offset = 0;
        $slices = [];

        foreach ($channels as $index => $channel) {
            $length = $channel['value'] / $total * $circumference;

            $slices[] = SvgShape::circle(60, 60, $radius)
                ->setAttributes([
                    'fill' => 'none',
                    'class' => $palette['stroke'],
                    'stroke-width' => '16',
                    'stroke-dasharray' => sprintf('%.2f %.2f', $length, $circumference),
                    'stroke-dashoffset' => sprintf('%.2f', -$offset),
                    'stroke-opacity' => (string) self::sliceOpacity($index),
                ]);

            $offset += $length;
        }

        $parts = [];

        foreach ($channels as $channel) {
            $share = (int) round($channel['value'] / $total * 100);
            $parts[] = $channel['label'].' €'.rtrim(rtrim(sprintf('%.1f', $channel['value']), '0'), '.').'k ('.$share.'%)';
        }

        return SvgComponentFactory::make(SvgComponentEnum::SVG)
            ->setXmlns()
            ->setViewBox(0, 0, 120, 120)
            ->setAttributes([
                'class' => 'mx-auto h-44 w-44',
                'role' => 'img',
                'aria-label' => $label,
            ])
            ->setContents([
                SvgComponentFactory::make(SvgComponentEnum::TITLE)->setContent($label),
                SvgComponentFactory::make(SvgComponentEnum::DESC)->setContent(implode(', ', $parts).'.'),
                SvgShape::circle(60, 60, $radius)
                    ->setAttributes([
                        'fill' => 'none',
                        'class' => $palette['grid'],
                        'stroke-width' => '16',
                        'opacity' => '0.25',
                    ]),
                SvgComponentFactory::make(SvgComponentEnum::G)
                    ->setAttribute('transform', 'rotate(-90 60 60)')
                    ->setContents($slices),
                SvgComponentFactory::make(SvgComponentEnum::TEXT)
                    ->setContent('€'.rtrim(rtrim(sprintf('%.1f', $total), '0'), '.').'k')
                    ->setAttributes([
                        'x' => '60',
                        'y' => '64',
                        'text-anchor' => 'middle',
                        'font-size' => '16',
                        'font-weight' => '700',
                        'fill' => 'currentColor',
                    ]),
                SvgComponentFactory::make(SvgComponentEnum::TEXT)
                    ->setContent('of revenue')
                    ->setAttributes([
                        'x' => '60',
                        'y' => '78',
                        'text-anchor' => 'middle',
                        'font-size' => '8',
                        'fill' => 'currentColor',
                        'opacity' => '0.6',
                    ]),
            ]);
    }

    /**
     * Legend swatch paint for the i-th donut slice: the chart stroke
     * repainted as a background, at the slice's own opacity.
     *
     * @return array{class: string, opacity: float}
     */
    public static function swatch(Design $design, int $index): array
    {
        return [
            'class' => self::palette($design)['swatch'],
            'opacity' => self::sliceOpacity($index),
        ];
    }

    /**
     * The stepped opacity that tells donut slices apart.
     */
    private static function sliceOpacity(int $index): float
    {
        return [1.0, 0.6, 0.35][$index % 3];
    }

    /**
     * A seven-point area sparkline. Decorative: the KPI number beside it
     * is the data, so the chart hides from assistive technology.
     *
     * @param  array<int, float|int>  $values
     */
    public static function sparkline(Design $design, array $values): SvgBackendComponent
    {
        $palette = self::palette($design);
        $max = max($values) ?: 1;
        $min = min($values);
        $range = ($max - $min) ?: 1;
        $last = count($values) - 1;

        $coords = [];

        foreach ($values as $index => $value) {
            $x = sprintf('%.1f', $index / max(1, $last) * 116 + 2);
            $y = sprintf('%.1f', 28 - (($value - $min) / $range) * 22);
            $coords[] = [$x, $y];
        }

        $area = SvgPath::start(2, 30);

        foreach ($coords as [$x, $y]) {
            $area->lineTo($x, $y);
        }

        $area->lineTo(118, 30)->close();

        $points = array_map(fn (array $point): string => $point[0].','.$point[1], $coords);

        [$lastX, $lastY] = $coords[$last];

        return SvgComponentFactory::make(SvgComponentEnum::SVG)
            ->setXmlns()
            ->setViewBox(0, 0, 120, 32)
            ->setAttributes([
                'class' => 'mt-2 h-8 w-full',
                'aria-hidden' => 'true',
                'focusable' => 'false',
            ])
            ->setContents([
                SvgShape::path($area)
                    ->setAttribute('class', $palette['accent'])
                    ->setAttribute('opacity', '0.15'),
                SvgShape::polyline(implode(' ', $points))
                    ->setAttributes([
                        'fill' => 'none',
                        'class' => $palette['stroke'],
                        'stroke-width' => '2',
                        'stroke-linecap' => 'round',
                        'stroke-linejoin' => 'round',
                    ]),
                SvgShape::circle($lastX, $lastY, 3)
                    ->setAttribute('class', $palette['accent']),
            ]);
    }

    /**
     * The design's chart paint, or currentColor everywhere when the
     * design ships no chart theme. A missing file and a missing variant
     * both throw, so one catch covers the whole fallback.
     *
     * @return array{accent: string, stroke: string, swatch: string, muted: string, mutedOpacity: string, grid: string}
     */
    private static function palette(Design $design): array
    {
        try {
            $stroke = (string) $design->themeManager->processTheme('chart', 'stroke');

            return [
                'accent' => (string) $design->themeManager->processTheme('chart', 'accent'),
                'stroke' => $stroke,
                'swatch' => str_replace('stroke-', 'bg-', $stroke),
                'muted' => (string) $design->themeManager->processTheme('chart', 'muted'),
                'mutedOpacity' => '1',
                'grid' => (string) $design->themeManager->processTheme('chart', 'grid'),
            ];
        } catch (ThemeDoesNotExistsException) {
            return [
                'accent' => 'fill-current',
                'stroke' => 'stroke-current',
                'swatch' => 'bg-current',
                'muted' => 'fill-current',
                'mutedOpacity' => '0.35',
                'grid' => 'stroke-current',
            ];
        }
    }
}
