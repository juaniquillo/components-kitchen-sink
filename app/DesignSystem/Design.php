<?php

declare(strict_types=1);

namespace App\DesignSystem;

use Juaniquillo\BaseBackendComponent\Contracts\ThemeManager;

/**
 * One named look of the design system.
 *
 * A design is a key, a label and the theme manager that resolves it. It
 * holds no path and no filesystem knowledge: the manager owns where the
 * theme lives, which is what lets a packaged design ship a manager with its
 * own folder baked in, while an app can point a generic manager at a folder
 * it keeps locally.
 */
final readonly class Design
{
    public function __construct(
        public string $key,
        public string $label,
        public ThemeManager $themeManager,
        public DarkMode $darkMode = DarkMode::None,
    ) {}
}
