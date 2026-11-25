<?php

declare(strict_types=1);

namespace App\Components\Options\Enums;

use App\Components\Options\Contracts\CssValue;

enum Items: string implements CssValue
{
    case Start = 'start';
    case Center = 'center';
    case End = 'end';
    case Baseline = 'baseline';
    case Stretch = 'stretch';

    public function getCSS(): string
    {
        return match ($this) {
            self::Start => 'items-start',
            self::Center => 'items-center',
            self::End => 'items-end',
            self::Baseline => 'items-baseline',
            self::Stretch => 'items-stretch',
        };
    }
}
