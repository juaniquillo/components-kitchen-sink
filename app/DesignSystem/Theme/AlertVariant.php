<?php

declare(strict_types=1);

namespace App\DesignSystem\Theme;

enum AlertVariant: string
{
    case Info = 'info';
    case Success = 'success';
    case Warning = 'warning';
    case Danger = 'danger';
}
