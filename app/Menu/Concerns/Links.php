<?php

namespace App\Menu\Concerns;

use Juaniquillo\BackendComponents\Builders\ComponentBuilder;
use Juaniquillo\BackendComponents\Contracts\CompoundComponent;
use Juaniquillo\BackendComponents\Enums\ComponentEnum;

trait Links
{
    public static function makeLinks() : CompoundComponent
    {
        $listItems = [];

        foreach (self::items() as  $link) {
            $listItems[] = self::getLinksListItem($link);
        }

        return self::getLinksList($listItems);
    }

    /** @param array<int, CompoundComponent> */
    public  static function getLinksList(array $items): CompoundComponent
    {
        return ComponentBuilder::make(ComponentEnum::DIV)
            ->setContents($items)
            ->setThemes([
                'display' => 'flex',
                'margin' => 'top-md',
                'flex' => [
                    'justify-center',
                    'items-center',
                    'gap-md'
                ],
            ]);
    }

    public  static function getLinksListItem($link): CompoundComponent
    {
        return ComponentBuilder::make(ComponentEnum::LINK)
            ->setAttribute('href', $link['route'])
            ->setContent($link['name'])
            ->setThemes([
                'action' => 'link',
                'display' => 'block',
                'padding' => 'sm',
                'background' => 'success',
                'border' => 'solid',
                'border-radius' => 'default',
                'font' => 'bold',
            ]);
            
    }
    
    /** @return array<array{name: string, route: string}> */
    public static function items(): array
    {
        return [];
    }
}
