<?php

declare(strict_types=1);

namespace App\DesignSystem;

use InvalidArgumentException;

/**
 * The designs the showcase can render.
 *
 * Registering a design is one entry here plus its theme folder; pages
 * and components read from this registry, so nothing else changes.
 */
final class DesignRegistry
{
    /**
     * @var array<string, Design>|null
     */
    private static ?array $designs = null;

    /**
     * @return array<string, Design>
     */
    public static function all(): array
    {
        // The designs are folders on disk and do not follow config, so the
        // set is built once per process. current() still reads config on
        // every call, which keeps tests free to change it.
        return self::$designs ??= [
            'base' => Design::base(),
            'base-dark' => Design::baseDark(),
            'base-dual' => Design::baseDual(),
        ];
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

    public static function default(): Design
    {
        return Design::base();
    }
}
