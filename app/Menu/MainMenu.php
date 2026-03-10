<?php

namespace App\Menu;

use App\Menu\Concerns\NavItems;

final class MainMenu
{
    use NavItems;

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
