<?php

declare(strict_types=1);

namespace App\DesignSystem\Theme;

enum BadgeVariant: string
{
    case Default = 'default';
    case Success = 'success';
    case Info = 'info';
    case Warning = 'warning';
    case Danger = 'danger';
}
