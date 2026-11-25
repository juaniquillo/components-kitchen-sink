<?php

declare(strict_types=1);

use App\Components\Options\ContainerOptions;
use App\Components\Options\Enums\Items;
use App\Components\Options\Enums\Justify;

it('resolves the default container classes', function () {
    expect((new ContainerOptions)->getCSS())
        ->toBe('mt-sm flex flex-gap-sm flex-wrap justify-center items-center');
});

it('resolves a bare box when flex is disabled', function () {
    expect((new ContainerOptions(disableFlex: true))->getCSS())->toBe('mt-sm');
});

it('resolves a column layout aligned to the start', function () {
    $css = (new ContainerOptions(flexColumn: true, justify: Justify::Start, items: Items::Stretch))->getCSS();

    expect($css)->toBe('mt-sm flex flex-gap-sm flex-wrap justify-start items-stretch flex-columns');
});

it('resolves a custom gap', function () {
    expect((new ContainerOptions(flexGap: 'flex-gap'))->getCSS())
        ->toBe('mt-sm flex flex-gap flex-wrap justify-center items-center');
});

it('maps every justification to its class', function (Justify $justify, string $class) {
    expect((new ContainerOptions(justify: $justify))->getCSS())->toContain($class);
})->with([
    [Justify::Start, 'justify-start'],
    [Justify::Center, 'justify-center'],
    [Justify::End, 'justify-end'],
    [Justify::Between, 'justify-between'],
    [Justify::Around, 'justify-around'],
    [Justify::Evenly, 'justify-evenly'],
    [Justify::Stretch, 'justify-stretch'],
]);

it('maps every item alignment to its class', function (Items $items, string $class) {
    expect((new ContainerOptions(items: $items))->getCSS())->toContain($class);
})->with([
    [Items::Start, 'items-start'],
    [Items::Center, 'items-center'],
    [Items::End, 'items-end'],
    [Items::Baseline, 'items-baseline'],
    [Items::Stretch, 'items-stretch'],
]);

it('keeps justification and alignment independent', function () {
    $css = (new ContainerOptions(justify: Justify::Between, items: Items::Start))->getCSS();

    expect($css)->toContain('justify-between')
        ->toContain('items-start')
        ->not->toContain('justify-center');
});
