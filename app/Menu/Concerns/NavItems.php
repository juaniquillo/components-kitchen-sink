<?php

namespace App\Menu\Concerns;

use Juaniquillo\BackendComponents\Builders\ComponentBuilder;
use Juaniquillo\BackendComponents\Contracts\BackendComponent;
use Juaniquillo\BackendComponents\Contracts\CompoundComponent;
use Juaniquillo\BackendComponents\Enums\ComponentEnum;

trait NavItems
{
    public static function make() : CompoundComponent
    {
        $listItems = [];

        foreach (self::items() as  $link) {
            $listItems[] = self::getListItem($link);
        }

        return self::getList($listItems);
    }

    /**
     * @param  array<string|int, string|int|CompoundComponent|BackendComponent>  $contents
     */
    public static function getList(array $contents) : CompoundComponent
    {
        return ComponentBuilder::make(ComponentEnum::UL)
            ->setContents($contents)
            ->setThemes(self::getListTheme());
    }

    public static function getListItem(array $link) : CompoundComponent
    {
        return ComponentBuilder::make(name: ComponentEnum::LI)
            ->setContent(
                content: self::getLink($link)
            )
            ->setThemes(self::getListItemsTheme());
    }

    public static function getLink(array $link) : CompoundComponent
    {
        $name = $link['name'] ?? 'name not provided';
        $route = $link['route'] ?? 'route not provided';

        return ComponentBuilder::make(name: ComponentEnum::LINK)
            ->setContent(content: $name)
            ->setAttributes(attributes: [
                'href' => $route,
            ])
            ->setThemes(self::getLinkTheme());
    }

    public static function getListTheme() : array
    {
        return [
            'text' => 'center',
            'margin' => 'bottom-md',
        ];  
    }
    public static function getListItemsTheme() : array
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

    public static function getLinkTheme() : array
    {
        return [
            'action' => 'link',
            'font' => 'bold',
            'text' => [
                'lg'
            ]
        ];
    }
}
    