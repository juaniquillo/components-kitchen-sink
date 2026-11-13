<?php

declare(strict_types=1);

namespace App\Components;

readonly class ContainerOptions
{
    public function __construct(
        public bool $disableFlex = false,
        public bool $flexColumn = false,
        public string $flexGap = 'flex-gap-sm',
        public bool $justifyCenter = true,
    ) {}

    /**
     * The classes of the box every group of components sits in.
     *
     * Resolved here instead of the Blade view, so the view only prints
     * one string.
     */
    public function getContainerCSS(): string
    {
        $classes = ['mt-sm'];

        if (! $this->disableFlex) {
            $classes[] = 'flex';
            $classes[] = $this->flexGap;
            $classes[] = 'flex-wrap';

            if ($this->justifyCenter) {
                $classes[] = 'justify-center';
                $classes[] = 'items-center';
            }
        }

        if ($this->flexColumn) {
            $classes[] = 'flex-columns';
        }

        return implode(' ', $classes);
    }
}
