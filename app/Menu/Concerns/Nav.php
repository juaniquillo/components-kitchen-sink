<?php

declare(strict_types=1);

namespace App\Menu\Concerns;

use Juaniquillo\BackendComponents\Enums\ComponentEnum;
use Juaniquillo\BackendComponents\Factories\LocalThemeComponentFactory;
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
        return LocalThemeComponentFactory::make(ComponentEnum::UL)
            ->setContents($contents)
            ->setThemes(['nav' => 'group']);
    }

    public static function getNavListItem(array $link): CompoundComponent
    {
        return LocalThemeComponentFactory::make(name: ComponentEnum::LI)
            ->setContent(
                content: self::getLink($link)
            );
    }

    public static function getLink(array $link): CompoundComponent
    {
        $name = $link['name'] ?? 'name not provided';
        $route = $link['route'] ?? 'route not provided';

        $isCurrent = self::isCurrentRoute((string) $route);

        $component = LocalThemeComponentFactory::make(name: ComponentEnum::LINK)
            ->setContent(content: $name)
            ->setAttributes(attributes: [
                'href' => $route,
            ])
            ->setThemes(['nav' => $isCurrent ? 'current' : 'item']);

        if ($isCurrent) {
            $component->setAttribute('aria-current', 'page');
        }

        return $component;
    }

    /**
     * The section whose pages live under its address counts as current, so
     * /components/bootstrap still marks Components and the dashboard still
     * marks Design System. Only the home page matches exactly.
     */
    private static function isCurrentRoute(string $url): bool
    {
        $path = parse_url($url, PHP_URL_PATH) ?? '/';

        if (! is_string($path) || $path === '') {
            $path = '/';
        }

        $current = '/'.ltrim(request()->path(), '/');

        if ($path === '/') {
            return $current === '/';
        }

        $prefix = rtrim($path, '/').'/';

        return $current === $path || str_starts_with($current, $prefix);
    }

    /** @return array<array{name: string, route: string}> */
    public static function items(): array
    {
        return [];
    }
}
