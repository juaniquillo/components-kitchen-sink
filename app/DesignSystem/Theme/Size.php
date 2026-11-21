<?php

declare(strict_types=1);

namespace App\DesignSystem\Theme;

/**
 * The size vocabulary every sizeable component shares.
 *
 * The value is the theme key, prefix included, so no component builds it.
 */
enum Size: string
{
    case Sm = 'size.sm';
    case Md = 'size.md';
    case Lg = 'size.lg';
}
