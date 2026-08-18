<?php

declare(strict_types=1);

namespace App\Components;

readonly class ContainerOptions
{
    public function __construct(
        public bool $disableFlex = false,
        public bool $flexColumn = false,
        public string $flexGap = 'flex-gap-sm',
    ) {}
}
