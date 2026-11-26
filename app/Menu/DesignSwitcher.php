<?php

declare(strict_types=1);

namespace App\Menu;

use Juaniquillo\BackendComponents\Enums\ComponentEnum;
use Juaniquillo\BackendComponents\Factories\LocalThemeComponentFactory;
use Juaniquillo\BackendComponents\MainBackendComponent;
use RuntimeException;

/**
 * The design chips the gallery and the dashboard share.
 *
 * The active design renders with the current theme server-side, so no
 * script is involved: the route name decides where a chip points, which
 * is what keeps each page in its own place.
 */
final class DesignSwitcher
{
    /**
     * @param  array<int, array{key: string, label: string}>  $designs
     */
    public static function make(array $designs, string $current, string $route): MainBackendComponent
    {
        $links = [];

        foreach ($designs as $option) {
            $isCurrent = $option['key'] === $current;

            $link = LocalThemeComponentFactory::make(ComponentEnum::LINK);

            if (! $link instanceof MainBackendComponent) {
                throw new RuntimeException('DesignSwitcher builds MainBackendComponent instances.');
            }

            $link
                ->setContent($option['label'])
                ->setAttribute('href', route($route, ['design' => $option['key']]))
                ->setThemes(['nav' => $isCurrent ? 'current-sm' : 'item-sm']);

            if ($isCurrent) {
                $link->setAttribute('aria-current', 'page');
            }

            $links[] = $link;
        }

        $group = LocalThemeComponentFactory::make(ComponentEnum::DIV);

        if (! $group instanceof MainBackendComponent) {
            throw new RuntimeException('DesignSwitcher builds MainBackendComponent instances.');
        }

        return $group
            ->setContents($links)
            ->setThemes(['nav' => 'group-center']);
    }
}
