<?php

declare(strict_types=1);

namespace App\Components\Options\Enums;

use App\Components\Options\Contracts\CssValue;

enum Justify: string implements CssValue
{
    case Start = 'start';
    case Center = 'center';
    case End = 'end';
    case Between = 'between';
    case Around = 'around';
    case Evenly = 'evenly';
    case Stretch = 'stretch';

    public function getCSS(): string
    {
        return match ($this) {
            self::Start => 'justify-start',
            self::Center => 'justify-center',
            self::End => 'justify-end',
            self::Between => 'justify-between',
            self::Around => 'justify-around',
            self::Evenly => 'justify-evenly',
            self::Stretch => 'justify-stretch',
        };
    }
}
