<?php

declare(strict_types=1);

namespace App\Components\Options;

use App\Components\Options\Concerns\JoinsClasses;
use App\Components\Options\Contracts\CssOptions;
use App\Components\Options\Enums\Overflow;
use App\Components\Options\Enums\Padding;

/**
 * The outer shell a group of components sits in.
 *
 * Kept apart from the container: the box is the surface, the padding and the
 * scroll context, while the container is the flex row inside it. A new box
 * concern lands here instead of widening the container's API.
 */
readonly class BoxOptions implements CssOptions
{
    use JoinsClasses;

    public function __construct(
        public Padding $padding = Padding::Sm,
        public Overflow $overflow = Overflow::Visible,
    ) {}

    public function getCSS(): string
    {
        return self::classes([
            $this->padding->getCSS(),
            $this->overflow->getCSS(),
        ]);
    }
}
