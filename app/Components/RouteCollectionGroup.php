<?php

declare(strict_types=1);

namespace App\Components;

use App\Components\Groups\Bootstrap\BootstrapBadge;
use App\Components\Groups\Bootstrap\BootstrapButton;
use App\Components\Groups\Bootstrap\BootstrapCard;
use App\Components\Groups\Bootstrap\BootstrapCarousel;
use App\Components\Groups\Bootstrap\BootstrapTables;
use App\Components\Groups\Flux\FluxUIButtons;
use App\Components\Groups\Flux\FluxUICards;
use App\Components\Groups\Flux\FluxUIFileInputs;
use App\Components\Groups\Flux\FluxUIModals;
use App\Components\Groups\Flux\FluxUISkeletons;
use App\Components\Groups\Flux\FluxUITables;
use App\Components\Groups\MainPackage;
use App\Components\Groups\Slate\SlateButtons;
use App\Components\Groups\Slate\SlateCarousel;
use App\Components\Groups\Slate\SlateDialog;
use App\Components\Groups\Slate\SlateDropDownMenu;
use App\Components\Groups\Slate\SlateTables;
use App\Components\Groups\Slate\SlateTabs;
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
                    new FluxUIFileInputs,
                ]),
                'assets' => [],
            ],
            'bootstrap' => [
                'name' => 'Bootstrap',
                'components' => ComponentCollection::make()->addComponents([
                    new BootstrapButton,
                    new BootstrapBadge,
                    new BootstrapCard,
                    new BootstrapCarousel,
                    new BootstrapTables,
                ]),
                'assets' => ['resources/sass/bootstrap.scss', 'resources/js/bootstrap.js'],
            ],
            'slate' => [
                'name' => 'Slate',
                'components' => ComponentCollection::make()->addComponents([
                    new SlateDialog,
                    new SlateTabs,
                    new SlateDropDownMenu,
                    new SlateButtons,
                    new SlateCarousel,
                    new SlateTables,
                ]),
                'assets' => ['resources/css/slate.css'],
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
