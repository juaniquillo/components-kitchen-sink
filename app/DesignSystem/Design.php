<?php

declare(strict_types=1);

namespace App\DesignSystem;

use Juaniquillo\BaseBackendComponent\Contracts\ThemeManager;
use Juaniquillo\BaseBackendComponent\Themes\GenericThemeManager;

/**
 * One named look of the design system.
 *
 * A design is a key, a label and the theme manager that resolves its
 * folder. Paint lives entirely in that folder; components never name
 * classes, so a new design is a new folder, not new code.
 */
final class Design
{
    public function __construct(
        public readonly string $key,
        public readonly string $label,
        public readonly ThemeManager $themeManager,
        public readonly DarkMode $darkMode = DarkMode::None,
    ) {}

    public static function base(): static
    {
        $themeManager = (new GenericThemeManager(base_path('resources/views/_themes/designs/base')))
            ->setThemeExtension('.blade.php');

        return new self('base', 'Base', $themeManager);
    }

    public static function baseDark(): static
    {
        $themeManager = (new GenericThemeManager(base_path('resources/views/_themes/designs/base-dark')))
            ->setThemeExtension('.blade.php');

        return new self('base-dark', 'Base Dark', $themeManager, darkMode: DarkMode::Always);
    }

    public static function baseDual(): static
    {
        $themeManager = (new GenericThemeManager(base_path('resources/views/_themes/designs/base-dual')))
            ->setThemeExtension('.blade.php');

        return new self('base-dual', 'Base Dual', $themeManager, darkMode: DarkMode::Adaptive);
    }
}
