<?php

declare(strict_types=1);

namespace App\Components\Options\Enums;

use App\Components\Options\Contracts\CssValue;

enum Padding: string implements CssValue
{
    case None = 'none';
    case Sm = 'sm';

    public function getCSS(): string
    {
        return match ($this) {
            self::None => '',
            self::Sm => 'p-sm',
        };
    }
}
