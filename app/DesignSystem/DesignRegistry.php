<?php

declare(strict_types=1);

namespace App\DesignSystem;

use InvalidArgumentException;
use Juaniquillo\BaseBackendComponent\Contracts\ThemeManager;
use Juaniquillo\BaseBackendComponent\Themes\GenericThemeManager;

/**
 * The designs the showcase can render.
 *
 * The list comes from config/design-system.php. A packaged design names a
 * ThemeManager class that knows its own folder; an app design names a folder
 * under resources/views instead, which the registry wraps in the generic
 * manager. Either way, adding a design is configuration, never code.
 */
final class DesignRegistry
{
    /**
     * @return array<string, Design>
     */
    public static function all(): array
    {
        $designs = [];

        foreach ((array) config('design-system.designs', []) as $key => $definition) {
            $designs[$key] = new Design(
                key: (string) $key,
                label: (string) ($definition['label'] ?? $key),
                themeManager: self::manager((string) $key, (array) $definition),
                darkMode: self::darkMode((string) $key, $definition['dark'] ?? 'none'),
            );
        }

        return $designs;
    }

    public static function get(string $key): ?Design
    {
        return self::all()[$key] ?? null;
    }

    /**
     * The design components render with when none is passed to them.
     *
     * Configured, because a consumer works with one design and should not
     * repeat it on every call. An unknown key throws: a typo in the config
     * is a mistake to fix, not a reason to render something else quietly.
     */
    public static function current(): Design
    {
        $key = (string) config('design-system.default', 'base');

        $design = self::get($key);

        if ($design === null) {
            throw new InvalidArgumentException(
                "Unknown design '{$key}'. Available designs: ".implode(', ', array_keys(self::all()))
            );
        }

        return $design;
    }

    /**
     * @param  array<string, mixed>  $definition
     */
    private static function manager(string $key, array $definition): ThemeManager
    {
        $manager = $definition['manager'] ?? null;

        if (is_string($manager) && $manager !== '') {
            if (! is_a($manager, ThemeManager::class, true)) {
                throw new InvalidArgumentException("Design '{$key}' names '{$manager}', which is not a ThemeManager.");
            }

            return new $manager;
        }

        $path = $definition['path'] ?? null;

        if (! is_string($path) || trim($path) === '') {
            throw new InvalidArgumentException("Design '{$key}' needs a manager class or a theme path.");
        }

        return (new GenericThemeManager(resource_path(trim($path))))->setThemeExtension('.blade.php');
    }

    private static function darkMode(string $key, mixed $mode): DarkMode
    {
        $darkMode = is_string($mode) ? DarkMode::tryFrom($mode) : null;

        if ($darkMode === null) {
            throw new InvalidArgumentException("Design '{$key}' has an unknown dark mode.");
        }

        return $darkMode;
    }
}
