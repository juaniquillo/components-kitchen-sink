<?php

declare(strict_types=1);

namespace App\DesignSystem\Examples;

use Juaniquillo\SvgBackendComponents\Factories\SvgComponentFactory;
use Juaniquillo\SvgBackendComponents\Shapes\SvgPath;
use Juaniquillo\SvgBackendComponents\Shapes\SvgShape;
use Juaniquillo\SvgBackendComponents\SvgBackendComponent;
use Juaniquillo\SvgBackendComponents\SvgComponentEnum;

/**
 * Small SVG charts for the dashboard example.
 *
 * Everything paints currentColor, so the charts inherit the card body's
 * design-themed text colour in every design, light or dark. Opacity
 * carries the hierarchy: the latest value is full, context is quiet.
 */
final class Chart
{
    /**
     * A twelve-column bar chart with a baseline and month labels.
     *
     * @param  array<int, float|int>  $values
     * @param  array<int, string>  $labels
     */
    public static function bars(array $values, array $labels, string $label): SvgBackendComponent
    {
        $max = max($values) ?: 1;
        $count = count($values);
        $step = 20;
        $barWidth = 12;
        $baseline = 72;

        $bars = [];

        foreach ($values as $index => $value) {
            $height = max(2, (int) round($value / $max * 60));

            $bars[] = SvgShape::rect($index * $step, $baseline - $height, $barWidth, $height)
                ->setAttribute('fill', 'currentColor')
                ->setAttribute('opacity', $index === $count - 1 ? '1' : '0.35')
                ->setAttribute('rx', 2);
        }

        $months = [];

        foreach ($labels as $index => $month) {
            $months[] = SvgComponentFactory::make(SvgComponentEnum::TEXT)
                ->setContent($month)
                ->setAttributes([
                    'x' => $index * $step + 6,
                    'y' => 86,
                    'text-anchor' => 'middle',
                    'font-size' => '6',
                    'fill' => 'currentColor',
                    'opacity' => '0.6',
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
                    ->setAttribute('stroke', 'currentColor')
                    ->setAttribute('opacity', '0.2'),
                ...$bars,
                ...$months,
            ]);
    }

    /**
     * A seven-point area sparkline. Decorative: the KPI number beside it
     * is the data, so the chart hides from assistive technology.
     *
     * @param  array<int, float|int>  $values
     */
    public static function sparkline(array $values): SvgBackendComponent
    {
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
                    ->setAttribute('fill', 'currentColor')
                    ->setAttribute('opacity', '0.15'),
                SvgShape::polyline(implode(' ', $points))
                    ->setAttributes([
                        'fill' => 'none',
                        'stroke' => 'currentColor',
                        'stroke-width' => '2',
                        'stroke-linecap' => 'round',
                        'stroke-linejoin' => 'round',
                    ]),
                SvgShape::circle($lastX, $lastY, 3)
                    ->setAttribute('fill', 'currentColor'),
            ]);
    }
}
