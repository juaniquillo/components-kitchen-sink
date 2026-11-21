<?php

declare(strict_types=1);

namespace App\DesignSystem\Theme;

/**
 * The states a component can be in, beyond its look.
 */
enum State: string
{
    case Disabled = 'state.disabled';
}
