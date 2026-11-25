<?php

declare(strict_types=1);

namespace App\Components\Contracts;

use App\Components\Options\GroupOptions;

interface Component
{
    public static function list(): array;

    public static function options(): GroupOptions;
}
