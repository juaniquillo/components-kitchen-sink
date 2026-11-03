<?php

declare(strict_types=1);

use App\Components\Groups\Svg\SvgBasicShapes;
use App\Components\Groups\Svg\SvgBrandIcons;
use App\Components\Groups\Svg\SvgMedia;
use App\Components\Groups\Svg\SvgPaint;
use App\Components\Groups\Svg\SvgPaths;
use App\Components\Groups\Svg\SvgReuse;
use App\Components\Groups\Svg\SvgThemedIcons;
use Juaniquillo\SvgBackendComponents\Builders\SvgComponentBuilder;
use Juaniquillo\SvgBackendComponents\Shapes\SvgShape;
use Juaniquillo\SvgBackendComponents\SvgComponentEnum;
use Juaniquillo\SvgBackendComponents\SvgRenderer;

it('has the expected group names', function () {
    expect(SvgBasicShapes::NAME)->toBe('SVG Basic Shapes')
        ->and(SvgPaths::NAME)->toBe('SVG Paths and Polygons')
        ->and(SvgPaint::NAME)->toBe('SVG Paint and Patterns')
        ->and(SvgReuse::NAME)->toBe('SVG Reuse and Text')
        ->and(SvgMedia::NAME)->toBe('SVG Media')
        ->and(SvgThemedIcons::NAME)->toBe('SVG Themed Icons')
        ->and(SvgBrandIcons::NAME)->toBe('SVG Brand Icons');
});

it('returns default options', function () {
    $groups = [new SvgBasicShapes, new SvgPaths, new SvgPaint, new SvgReuse, new SvgMedia, new SvgThemedIcons, new SvgBrandIcons];

    foreach ($groups as $group) {
        $options = $group->options();

        expect($options->disableFlex)->toBeFalse()
            ->and($options->flexColumn)->toBeFalse()
            ->and($options->flexGap)->toBe('flex-gap-sm');
    }
});

it('renders the basic shapes as one svg', function () {
    $html = (string) $this->blade('{{ $svg }}', ['svg' => SvgBasicShapes::shapes()]);

    expect($html)->toContain('<svg')
        ->toContain('xmlns="http://www.w3.org/2000/svg"')
        ->toContain('viewBox="0 0 200 120"')
        ->toContain('width="200"')
        ->toContain('height="120"')
        ->toContain('<rect')
        ->toContain('<circle')
        ->toContain('<ellipse')
        ->toContain('<line');
});

it('renders the paths, the drawn house, and the command families as three svgs', function () {
    $html = (string) $this->blade('{{ $svg }}', ['svg' => SvgPaths::paths()]);
    $drawn = (string) $this->blade('{{ $svg }}', ['svg' => SvgPaths::drawn()]);
    $commands = (string) $this->blade('{{ $svg }}', ['svg' => SvgPaths::commands()]);

    expect($html)->toContain('<svg')
        ->toContain('<polyline')
        ->toContain('<polygon')
        ->toContain('<path')
        ->toContain('C 140 10, 170 10, 185 30')
        ->and($drawn)->toContain('<svg')
        ->toContain('<path')
        ->toContain('d="M 20 100 L 20 40 L 100 10 L 180 40 L 180 100 Z"')
        ->toContain('<rect')
        ->and($commands)->toContain('d="M 30 100 l 0 -50 A 40 40 0 0 1 110 50 l 0 50 Z"')
        ->toContain('d="M 130 90 Q 140 55, 150 90 T 170 90 T 190 90"');
});

it('renders the gradients, the patterns, the opacity, and the mask as four svgs', function () {
    $gradients = (string) $this->blade('{{ $svg }}', ['svg' => SvgPaint::gradients()]);
    $patterns = (string) $this->blade('{{ $svg }}', ['svg' => SvgPaint::clipAndPattern()]);
    $opacity = (string) $this->blade('{{ $svg }}', ['svg' => SvgPaint::opacity()]);
    $mask = (string) $this->blade('{{ $svg }}', ['svg' => SvgPaint::mask()]);

    expect($gradients)->toContain('<linearGradient')
        ->toContain('<radialGradient')
        ->toContain('<stop')
        ->and($patterns)->toContain('<clipPath')
        ->toContain('<pattern')
        ->and($opacity)->toContain('<circle')
        ->toContain('opacity="0.6"')
        ->and($mask)->toContain('<mask')
        ->toContain('mask="url(#fade)"');
});

it('renders the reuse, the symbol, and the caption as three svgs', function () {
    $reuse = (string) $this->blade('{{ $svg }}', ['svg' => SvgReuse::reuse()]);
    $symbol = (string) $this->blade('{{ $svg }}', ['svg' => SvgReuse::symbol()]);
    $caption = (string) $this->blade('{{ $svg }}', ['svg' => SvgReuse::caption()]);

    expect($reuse)->toContain('<defs')
        ->toContain('<use')
        ->toContain('<title')
        ->toContain('<desc')
        ->and($symbol)->toContain('<symbol')
        ->toContain('viewBox="0 0 24 24"')
        ->toContain('width="48"')
        ->toContain('width="24"')
        ->and($caption)->toContain('<text')
        ->toContain('<tspan')
        ->toContain('fill-slate-600')
        ->toContain('fill-blue-600');
});

it('renders an image with its href and size', function () {
    $html = (string) $this->blade('{{ $svg }}', ['svg' => SvgMedia::image()]);

    expect($html)->toContain('<image')
        ->toContain('href="/images/mail/yellow-lightning-bolt.png"')
        ->toContain('width="60"')
        ->toContain('height="80"');
});

it('paints the themed icons with the app fill group', function () {
    foreach (SvgThemedIcons::list() as $icon) {
        $html = (string) $this->blade('{{ $svg }}', ['svg' => $icon]);

        expect($html)->toContain('<svg')
            ->toContain('<path')
            ->toContain('width="56"')
            ->toContain('height="56"');
    }

    $icons = SvgThemedIcons::list();

    expect((string) $this->blade('{{ $svg }}', ['svg' => $icons['plus']]))->toContain('fill-indigo-600')
        ->and((string) $this->blade('{{ $svg }}', ['svg' => $icons['check']]))->toContain('fill-emerald-600')
        ->and((string) $this->blade('{{ $svg }}', ['svg' => $icons['heart']]))->toContain('fill-rose-600')
        ->and((string) $this->blade('{{ $svg }}', ['svg' => $icons['play']]))->toContain('fill-amber-500');
});

it('renders brand marks with their fills and labels', function () {
    $icons = SvgBrandIcons::list();

    foreach ($icons as $icon) {
        expect((string) $this->blade('{{ $svg }}', ['svg' => $icon]))
            ->toContain('<svg')
            ->toContain('role="img"')
            ->toContain('viewBox="0 0 24 24"')
            ->toContain('<path');
    }

    expect((string) $this->blade('{{ $svg }}', ['svg' => $icons['github']]))
        ->toContain('aria-label="GitHub"')
        ->toContain('fill="currentColor"')
        ->toContain('text-black')
        ->toContain('dark:text-white')
        ->and((string) $this->blade('{{ $svg }}', ['svg' => $icons['x']]))
        ->toContain('aria-label="X"')
        ->and((string) $this->blade('{{ $svg }}', ['svg' => $icons['facebook']]))
        ->toContain('aria-label="Facebook"')
        ->toContain('fill="#1877f2"')
        ->and((string) $this->blade('{{ $svg }}', ['svg' => $icons['instagram']]))
        ->toContain('aria-label="Instagram"')
        ->toContain('<linearGradient')
        ->toContain('fill="url(#instagram-gradient)"');
});

it('registers the svg group on the components pages', function () {
    $this->get('/components')->assertSee('SVG Backend Components');

    $this->get('/components/svg')
        ->assertOk()
        ->assertSee('<svg', false);
});

it('exports a standalone document, adding xmlns when the root lacks it', function () {
    $path = storage_path('framework/testing/svg/showcase.svg');
    @unlink($path);

    $bare = SvgComponentBuilder::make(SvgComponentEnum::SVG)
        ->setViewBox(0, 0, 10, 10)
        ->setSize(10)
        ->setContent(SvgShape::circle(5, 5, 4));

    expect(SvgRenderer::make($bare)->saveTo($path))->toBe($path)
        ->and(file_get_contents($path))
        ->toContain('xmlns="http://www.w3.org/2000/svg"')
        ->toEndWith("\n");

    SvgRenderer::make($bare)->saveTo($path, prolog: true);

    expect(file_get_contents($path))
        ->toStartWith('<?xml version="1.0" encoding="UTF-8"?>');

    @unlink($path);
});
