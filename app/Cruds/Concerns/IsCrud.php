<?php

declare(strict_types=1);

namespace App\Cruds\Concerns;

trait IsCrud
{
    public static function description(): ?string
    {
        return null;
    }
}
