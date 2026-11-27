<?php

declare(strict_types=1);

use App\Builder\Support\RuntimeTailwind;

it('hands the runtime build the app theme and the dark variant', function () {
    $declarations = RuntimeTailwind::declarations();

    expect($declarations)
        ->not->toBe('')
        ->toContain('@theme')
        ->toContain('@custom-variant dark')
        ->toContain('--font-sans');
});
