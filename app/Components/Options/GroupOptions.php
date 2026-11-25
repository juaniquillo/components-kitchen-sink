<?php

declare(strict_types=1);

namespace App\Components\Options;

/**
 * Everything a component group needs to place its box, its row and (later)
 * its title. A new concern is a property here, never another parameter on
 * the container.
 */
readonly class GroupOptions
{
    public function __construct(
        public ContainerOptions $container = new ContainerOptions,
        public BoxOptions $box = new BoxOptions,
    ) {}
}
