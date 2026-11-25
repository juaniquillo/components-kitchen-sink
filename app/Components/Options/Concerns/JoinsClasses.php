<?php

declare(strict_types=1);

namespace App\Components\Options\Concerns;

/**
 * Joins resolved CSS values without stray spaces.
 */
trait JoinsClasses
{
    /**
     * @param  array<int, string>  $classes
     */
    protected static function classes(array $classes): string
    {
        return implode(' ', array_filter($classes, static fn (string $class): bool => $class !== ''));
    }
}
