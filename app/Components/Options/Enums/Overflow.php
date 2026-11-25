<?php

declare(strict_types=1);

namespace App\Components\Options\Enums;

use App\Components\Options\Contracts\CssValue;

enum Overflow: string implements CssValue
{
    case Visible = 'visible';
    case Auto = 'auto';
    case Hidden = 'hidden';
    case Scroll = 'scroll';
    case XAuto = 'x-auto';
    case XHidden = 'x-hidden';
    case XScroll = 'x-scroll';
    case YAuto = 'y-auto';
    case YHidden = 'y-hidden';
    case YScroll = 'y-scroll';

    public function getCSS(): string
    {
        return match ($this) {
            self::Visible => '',
            self::Auto => 'overflow-auto',
            self::Hidden => 'overflow-hidden',
            self::Scroll => 'overflow-scroll',
            self::XAuto => 'overflow-x-auto',
            self::XHidden => 'overflow-x-hidden',
            self::XScroll => 'overflow-x-scroll',
            self::YAuto => 'overflow-y-auto',
            self::YHidden => 'overflow-y-hidden',
            self::YScroll => 'overflow-y-scroll',
        };
    }
}
