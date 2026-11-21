<?php

declare(strict_types=1);

namespace App\DesignSystem\Theme;

enum ButtonVariant: string
{
    case Default = 'default';
    case Primary = 'primary';
    case Secondary = 'secondary';
    case Danger = 'danger';
    case Ghost = 'ghost';
}
