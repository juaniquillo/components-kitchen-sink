<?php

declare(strict_types=1);

use App\Components\ContainerOptions;

it('resolves the default container classes', function () {
    expect((new ContainerOptions)->getContainerCSS())->toBe('mt-sm flex flex-gap-sm flex-wrap justify-center items-center');
});

it('resolves a bare box when flex is disabled', function () {
    expect((new ContainerOptions(disableFlex: true))->getContainerCSS())->toBe('mt-sm');
});

it('resolves a column layout without centering', function () {
    $css = (new ContainerOptions(flexColumn: true, justifyCenter: false))->getContainerCSS();

    expect($css)->toBe('mt-sm flex flex-gap-sm flex-wrap flex-columns');
});

it('resolves a custom gap', function () {
    expect((new ContainerOptions(flexGap: 'flex-gap'))->getContainerCSS())->toBe('mt-sm flex flex-gap flex-wrap justify-center items-center');
});
