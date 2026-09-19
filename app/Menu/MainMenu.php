<?php

declare(strict_types=1);

namespace App\Menu;

use App\Menu\Concerns\Links;
use App\Menu\Concerns\Nav;

final class MainMenu
{
    use Links,
        Nav;

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
