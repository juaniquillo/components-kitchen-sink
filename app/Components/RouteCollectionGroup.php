<?php

namespace App\Components;

use App\Components\Groups\BootstrapButton;
use App\Components\Groups\FluxUIModals;
use App\Menu\Concerns\Links;

class RouteCollectionGroup
{
    use Links;

    public  static function get(string $group): ?array
    {
        return self::list()[$group] ?? null;
    }

    /** @return array<
     * string, 
     * array{
     *   name: string, 
     *   components: ComponentCollection, 
     *   assets: array<string>
     * }> */
    public static function list(): array
    {
        return [
            'bootstrap' => [
                'name' => 'Bootstrap',
                'components' => ComponentCollection::make()->addComponents([
                    new BootstrapButton()
                ]),
                'assets' => ['resources/sass/bootstrap.scss', 'resources/js/bootstrap.js'],
            ],
            'flux' => [
                'name' => 'Flux UI',
                'components' => ComponentCollection::make()->addComponents([
                    new FluxUIModals
                ]),
                'assets' => [],
            ],
        ];
    }

    /** @return array<array{name: string, route: string}> */
    public static function items(): array
    {
        $items = [];

        foreach (self::list() as $name =>  $route) {
            $items[] = [
                'name' => $route['name'],
                'route' => route('component', [$name])
            ];
        }

        return $items;
    }
}
