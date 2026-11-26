<?php

declare(strict_types=1);

namespace App\Menu\Concerns;

use Juaniquillo\BackendComponents\Enums\ComponentEnum;
use Juaniquillo\BackendComponents\Factories\LocalThemeComponentFactory;
use Juaniquillo\BaseBackendComponent\Contracts\CompoundComponent;

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
        return LocalThemeComponentFactory::make(ComponentEnum::DIV)
            ->setContents($items)
            ->setAttribute('class', 'mt-sm display-flex flex-center flex-gap-sm flex-wrap p-sm');
    }

    public static function getLinksListItem(array $link): CompoundComponent
    {
        return LocalThemeComponentFactory::make(ComponentEnum::LINK)
            ->setAttribute('href', $link['route'])
            ->setContent($link['name'])
            ->setThemes(['nav' => 'item-sm']);

    }

    /** @return array<array{name: string, route: string}> */
    public static function items(): array
    {
        return [];
    }
}
