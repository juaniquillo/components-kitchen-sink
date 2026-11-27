<?php

declare(strict_types=1);

use App\DesignSystem\Icons;

it('builds maskable theme icons', function () {
    $directory = sys_get_temp_dir().'/theme-icons-'.uniqid();

    Icons::build($directory);

    foreach (Icons::FILES as $file) {
        $svg = file_get_contents($directory.'/'.$file);

        expect($svg)
            ->toContain('<svg')
            ->toContain('viewBox="0 0 24 24"')
            ->not->toContain('#');
    }
});

it('commits the built icons', function () {
    foreach (Icons::FILES as $file) {
        expect(file_exists(public_path('icons/'.$file)))->toBeTrue();
    }
});
