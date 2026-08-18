<?php

declare(strict_types=1);

namespace App\Components\Contracts;

interface Component
{
    public static function list(): array;

    public static function options(): array;
}
