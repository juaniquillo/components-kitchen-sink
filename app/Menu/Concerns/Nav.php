<?php

declare(strict_types=1);

namespace App\Menu\Concerns;

use Juaniquillo\BackendComponents\Enums\ComponentEnum;
use Juaniquillo\BackendComponents\Factories\ComponentFactory;
use Juaniquillo\BaseBackendComponent\Contracts\BackendComponent;
use Juaniquillo\BaseBackendComponent\Contracts\CompoundComponent;

trait Nav
{
    public static function makeNav(): CompoundComponent
    {
        $listItems = [];

        foreach (self::items() as $link) {
            $listItems[] = self::getNavListItem($link);
        }

        return self::getNavList($listItems);
    }

    /**
     * @param  array<string|int, string|int|CompoundComponent|BackendComponent>  $contents
     */
    public static function getNavList(array $contents): CompoundComponent
    {
        return ComponentFactory::make(ComponentEnum::UL)
            ->setContents($contents)
            ->setThemes(self::getNavListTheme());
    }

    public static function getNavListItem(array $link): CompoundComponent
    {
        return ComponentFactory::make(name: ComponentEnum::LI)
            ->setContent(
                content: self::getLink($link)
            )
            ->setThemes(self::getNavListItemsTheme());
    }

    public static function getLink(array $link): CompoundComponent
    {
        $name = $link['name'] ?? 'name not provided';
        $route = $link['route'] ?? 'route not provided';

        return ComponentFactory::make(name: ComponentEnum::LINK)
            ->setContent(content: $name)
            ->setAttributes(attributes: [
                'href' => $route,
            ])
            ->setThemes(self::getLinkTheme());
    }

    public static function getNavListTheme(): array
    {
        return [
            'text' => 'center',
            'margin' => 'bottom-md',
        ];
    }

    public static function getNavListItemsTheme(): array
    {
        return [
            'display' => 'inline-block',
            'margin' => [
                'right-sm',
                'left-sm',
                'top-sm',
            ],
        ];
    }

    public static function getLinkTheme(): array
    {
        return [
            'action' => 'link',
            'font' => 'bold',
            'text' => [
                'lg',
            ],
        ];
    }

    /** @return array<array{name: string, route: string}> */
    public static function items(): array
    {
        return [];
    }
}
