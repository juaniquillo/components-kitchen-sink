<?php

declare(strict_types=1);

namespace App\Menu;

use Juaniquillo\BackendComponents\Enums\ComponentEnum;
use Juaniquillo\BackendComponents\Factories\LocalThemeComponentFactory;
use Juaniquillo\BackendComponents\MainBackendComponent;
use RuntimeException;

/**
 * The light/auto/dark switch, built like the rest of the site.
 *
 * Each button carries its own toggle theme; aria-pressed flips client-side
 * and the pressed fill comes from the theme, not from script-written
 * classes.
 */
final class AppearanceToggle
{
    public static function make(): MainBackendComponent
    {
        $group = LocalThemeComponentFactory::make(ComponentEnum::DIV);

        if (! $group instanceof MainBackendComponent) {
            throw new RuntimeException('AppearanceToggle builds MainBackendComponent instances.');
        }

        return $group
            ->setAttributes([
                'role' => 'group',
                'aria-label' => 'Colour theme',
                'data-appearance-group' => '',
            ])
            ->setContents([
                self::button('light', 'Light', false),
                self::button('system', 'Auto', true),
                self::button('dark', 'Dark', false),
            ])
            ->setThemes(['nav' => 'group']);
    }

    private static function button(string $value, string $label, bool $pressed): MainBackendComponent
    {
        $button = LocalThemeComponentFactory::make(ComponentEnum::BUTTON);

        if (! $button instanceof MainBackendComponent) {
            throw new RuntimeException('AppearanceToggle builds MainBackendComponent instances.');
        }

        return $button
            ->setContent($label)
            ->setAttributes([
                'type' => 'button',
                'data-appearance' => $value,
                'aria-pressed' => $pressed ? 'true' : 'false',
            ])
            ->setThemes(['nav' => 'toggle']);
    }
}
