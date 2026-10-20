<?php

declare(strict_types=1);

namespace App\Builder\Contracts;

/**
 * A composition saved on disk.
 */
final readonly class SavedComposition
{
    public function __construct(
        public string $slug,
        public string $name,
        public string $savedAt,
        public int $bytes,
    ) {}
}
