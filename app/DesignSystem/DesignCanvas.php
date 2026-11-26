<?php

declare(strict_types=1);

namespace App\DesignSystem;

/**
 * The page surface designs preview on.
 *
 * The token boxes keep the shared showcase surface everywhere; the
 * dashboard and the in-context examples sit on a surface that matches the
 * design, so a dark card reads against its page instead of sinking into
 * it. Designs without an entry keep the zinc surface they always had.
 */
final class DesignCanvas
{
    /**
     * @var array<string, string>
     */
    private const OVERRIDES = [
        'classic' => 'bg-slate-100 dark:bg-slate-950',
    ];

    public static function override(string $key): ?string
    {
        return self::OVERRIDES[$key] ?? null;
    }

    public static function dashboard(string $key): string
    {
        return self::override($key) ?? 'bg-zinc-100 dark:bg-zinc-900';
    }

    public static function example(string $key): string
    {
        return self::override($key) ?? 'component-box-bg';
    }
}
