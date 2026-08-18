<?php

declare(strict_types=1);

namespace App\Components;

use App\Components\Groups\BootstrapBadge;
use App\Components\Groups\BootstrapButton;
use App\Components\Groups\BootstrapCard;
use App\Components\Groups\BootstrapCarousel;
use App\Components\Groups\FluxUIButtons;
use App\Components\Groups\FluxUIModals;
use App\Components\Groups\FluxUICards;
use App\Components\Groups\FluxUISkeletons;
use App\Components\Groups\FluxUITables;
use App\Components\Groups\MainPackage;
use App\Menu\Concerns\Links;

class RouteCollectionGroup
{
    use Links;

    public static function get(string $group): ?array
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
            'backend-component' => [
                'name' => 'Backend Components',
                'components' => ComponentCollection::make()->addComponents([
                    new MainPackage,
                ]),
                'assets' => [],
            ],
            'flux' => [
                'name' => 'Flux UI',
                'components' => ComponentCollection::make()->addComponents([
                    new FluxUIModals,
                    new FluxUIButtons,
                    new FluxUISkeletons,
                    new FluxUICards,
                    new FluxUITables,
                ]),
                'assets' => [],
            ],
            'bootstrap' => [
                'name' => 'Bootstrap',
                'components' => ComponentCollection::make()->addComponents([
                    new BootstrapButton,
                    new BootstrapBadge,
                    new BootstrapCarousel,
                    new BootstrapCard,
                ]),
                'assets' => ['resources/sass/bootstrap.scss', 'resources/js/bootstrap.js'],
            ],
        ];
    }

    /** @return array<array{name: string, route: string}> */
    public static function items(): array
    {
        $items = [];

        foreach (self::list() as $name => $route) {
            $items[] = [
                'name' => $route['name'],
                'route' => route('component', [$name]),
            ];
        }

        return $items;
    }
}
