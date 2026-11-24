<?php

declare(strict_types=1);

use App\Components\ContainerOptions;

it('resolves the default container classes', function () {
    expect((new ContainerOptions)->getContainerCSS())
        ->toBe('mt-sm flex flex-gap-sm flex-wrap justify-center items-center');
});

it('resolves a bare box when flex is disabled', function () {
    expect((new ContainerOptions(disableFlex: true))->getContainerCSS())->toBe('mt-sm');
});

it('resolves a column layout aligned to the start', function () {
    $css = (new ContainerOptions(flexColumn: true, justify: 'start', items: 'stretch'))->getContainerCSS();

    expect($css)->toBe('mt-sm flex flex-gap-sm flex-wrap justify-start items-stretch flex-columns');
});

it('resolves a custom gap', function () {
    expect((new ContainerOptions(flexGap: 'flex-gap'))->getContainerCSS())
        ->toBe('mt-sm flex flex-gap flex-wrap justify-center items-center');
});

it('maps every justification to its class', function (string $value, string $class) {
    expect((new ContainerOptions(justify: $value))->getContainerCSS())->toContain($class);
})->with([
    ['start', 'justify-start'],
    ['center', 'justify-center'],
    ['end', 'justify-end'],
    ['between', 'justify-between'],
    ['around', 'justify-around'],
    ['evenly', 'justify-evenly'],
    ['stretch', 'justify-stretch'],
]);

it('maps every item alignment to its class', function (string $value, string $class) {
    expect((new ContainerOptions(items: $value))->getContainerCSS())->toContain($class);
})->with([
    ['start', 'items-start'],
    ['center', 'items-center'],
    ['end', 'items-end'],
    ['baseline', 'items-baseline'],
    ['stretch', 'items-stretch'],
]);

it('keeps justification and alignment independent', function () {
    $css = (new ContainerOptions(justify: 'between', items: 'start'))->getContainerCSS();

    expect($css)->toContain('justify-between')
        ->toContain('items-start')
        ->not->toContain('justify-center');
});

it('refuses unknown alignments', function () {
    expect(fn () => new ContainerOptions(justify: 'middle'))->toThrow(InvalidArgumentException::class);

    expect(fn () => new ContainerOptions(items: 'middle'))->toThrow(InvalidArgumentException::class);
});
