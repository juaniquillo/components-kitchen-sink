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
 * Three icon buttons instead of text pills, so the switch cannot be
 * mistaken for navigation. The icons are static mask files painted with
 * the button's text colour; aria-pressed flips client-side and the
 * pressed fill comes from the theme, not from script-written classes.
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
        $button = LocalThemeComponentFactory::make(ComponentEnum::BUTTON);

        if (! $button instanceof MainBackendComponent) {
            throw new RuntimeException('AppearanceToggle builds MainBackendComponent instances.');
        }

        $glyph = LocalThemeComponentFactory::make(ComponentEnum::SPAN);

        if (! $glyph instanceof MainBackendComponent) {
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
            ->setThemes(['nav' => 'toggle-item']);
    }
}
