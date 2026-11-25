<?php

declare(strict_types=1);

namespace App\Components\Options;

use App\Components\Options\Concerns\JoinsClasses;
use App\Components\Options\Contracts\CssOptions;
use App\Components\Options\Enums\Items;
use App\Components\Options\Enums\Justify;

/**
 * How a group of components sits in its box.
 *
 * Justification runs along the main axis, alignment across it. The values are
 * enums so a typo is a type error; the outer shell has its own options, see
 * BoxOptions.
 */
readonly class ContainerOptions implements CssOptions
{
    use JoinsClasses;

    public function __construct(
        public bool $disableFlex = false,
        public bool $flexColumn = false,
        public string $flexGap = 'flex-gap-sm',
        public Justify $justify = Justify::Center,
        public Items $items = Items::Center,
    ) {}

    public function getCSS(): string
    {
        $classes = ['mt-sm'];

        if (! $this->disableFlex) {
            $classes[] = 'flex';
            $classes[] = $this->flexGap;
            $classes[] = 'flex-wrap';
            $classes[] = $this->justify->getCSS();
            $classes[] = $this->items->getCSS();
        }

        if ($this->flexColumn) {
            $classes[] = 'flex-columns';
        }

        return self::classes($classes);
    }
}
