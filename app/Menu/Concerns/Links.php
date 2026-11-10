<?php

declare(strict_types=1);

namespace App\Menu\Concerns;

use Juaniquillo\BackendComponents\Contracts\CompoundComponent;
use Juaniquillo\BackendComponents\Enums\ComponentEnum;
use Juaniquillo\BackendComponents\Factories\ComponentFactory;

trait Links
{
    public static function makeLinks(): CompoundComponent
    {
        $listItems = [];

        foreach (self::items() as $link) {
            $listItems[] = self::getLinksListItem($link);
        }

        return self::getLinksList($listItems);
    }

    /** @param array<int, CompoundComponent> $items */
    public static function getLinksList(array $items): CompoundComponent
    {
        return ComponentFactory::make(ComponentEnum::DIV)
            ->setContents($items)
            ->setAttribute('class', 'mt-sm display-flex flex-center flex-gap-sm flex-wrap p-sm');
    }

    public static function getLinksListItem(array $link): CompoundComponent
    {
        return ComponentFactory::make(ComponentEnum::LINK)
            ->setAttribute('href', $link['route'])
            ->setContent($link['name'])
            ->setAttribute('class', 'text-blue-500 underline hover:no-underline font-bold text-lg');

    }

    /** @return array<array{name: string, route: string}> */
    public static function items(): array
    {
        return [];
    }
}
