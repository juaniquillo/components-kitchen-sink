<?php

namespace App\Menu;

use App\Menu\Concerns\Links;
use App\Menu\Concerns\Nav;

final class MainMenu
{
    use Nav,
        Links;

    /** @return array<array{name: string, route: string}> */
    public static function items(): array
    {
        return [
            [
                'name' => 'Home',
                'route' => route('home'),
            ],
            [
                'name' => 'Components',
                'route' => route('components'),
            ],
            [
                'name' => 'Cruds',
                'route' => route('cruds'),
            ],
        ];
    }
}
