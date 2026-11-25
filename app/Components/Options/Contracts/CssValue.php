<?php

declare(strict_types=1);

namespace App\Components\Options\Contracts;

interface CssValue
{
    public function getCSS(): string;
}
