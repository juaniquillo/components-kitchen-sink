<?php

declare(strict_types=1);

namespace App\Menu;

use Juaniquillo\BackendComponents\Enums\ComponentEnum;
use Juaniquillo\BackendComponents\Factories\LocalThemeComponentFactory;
use Juaniquillo\BackendComponents\MainBackendComponent;
use RuntimeException;

/**
 * The light/auto/dark switch, built from the site chrome theme.
 *
 * It renders through the app's nav theme, never a design's button theme:
 * the switch is page chrome and looks the same on every page and design.
 * The pressed fill lives in the theme (`aria-pressed:` utilities), so no
 * script-written classes are needed.
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
                self::button('light', 'Light', 'sun', false),
                self::button('system', 'Auto', 'auto', true),
                self::button('dark', 'Dark', 'moon', false),
            ])
            ->setThemes(['nav' => 'toggle-group']);
    }

    private static function button(string $value, string $label, string $icon, bool $pressed): MainBackendComponent
    {
        $glyph = LocalThemeComponentFactory::make(ComponentEnum::SPAN);

        if (! $glyph instanceof MainBackendComponent) {
            throw new RuntimeException('AppearanceToggle builds MainBackendComponent instances.');
        }

        $button = LocalThemeComponentFactory::make(ComponentEnum::BUTTON);

        if (! $button instanceof MainBackendComponent) {
            throw new RuntimeException('AppearanceToggle builds MainBackendComponent instances.');
        }

        return $button
            ->setContent(
                $glyph
                    ->setAttributes(['aria-hidden' => 'true'])
                    ->setThemes(['nav' => 'icon-'.$icon])
            )
            ->setAttributes([
                'type' => 'button',
                'data-appearance' => $value,
                'aria-pressed' => $pressed ? 'true' : 'false',
                'aria-label' => $label,
                'title' => $label,
            ])
            ->setThemes(['nav' => 'toggle-button']);
    }
}
