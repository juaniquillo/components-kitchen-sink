<?php

namespace App\Menu;

use App\Menu\Concerns\Nav;

final class MainMenu
{
    use Nav;

    public static function items(): array
    {
        return [
            [
                'name' => 'Home',
                'route' => route('home'),
            ],
            [
                'name' => 'Cruds',
                'route' => route('cruds'),
            ],
        ];
    }
}
