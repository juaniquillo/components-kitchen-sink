<?php

declare(strict_types=1);

namespace App\DesignSystem;

/**
 * How a design handles dark mode.
 *
 * None means light only, Always means dark only: neither carries
 * conditional branches. Adaptive means every color has a dark
 * counterpart, and the test suite holds each mode to its rule.
 */
enum DarkMode: string
{
    case None = 'none';
    case Adaptive = 'adaptive';
    case Always = 'always';
}
