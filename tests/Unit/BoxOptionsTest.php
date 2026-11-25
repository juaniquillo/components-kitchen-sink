<?php

declare(strict_types=1);

use App\Components\Options\BoxOptions;
use App\Components\Options\Enums\Overflow;
use App\Components\Options\Enums\Padding;

it('resolves the default box', function () {
    expect((new BoxOptions)->getCSS())->toBe('p-sm');
});

it('resolves a box without padding', function () {
    expect((new BoxOptions(padding: Padding::None))->getCSS())->toBe('');
});

it('emits nothing for visible overflow', function () {
    expect((new BoxOptions(overflow: Overflow::Visible))->getCSS())->toBe('p-sm');
});

it('maps box overflow to its class', function (Overflow $overflow, string $class) {
    expect((new BoxOptions(overflow: $overflow))->getCSS())->toContain($class);
})->with([
    [Overflow::Auto, 'overflow-auto'],
    [Overflow::Hidden, 'overflow-hidden'],
    [Overflow::Scroll, 'overflow-scroll'],
    [Overflow::XAuto, 'overflow-x-auto'],
    [Overflow::XHidden, 'overflow-x-hidden'],
    [Overflow::XScroll, 'overflow-x-scroll'],
    [Overflow::YAuto, 'overflow-y-auto'],
    [Overflow::YHidden, 'overflow-y-hidden'],
    [Overflow::YScroll, 'overflow-y-scroll'],
]);
