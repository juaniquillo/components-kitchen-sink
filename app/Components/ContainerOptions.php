<?php

declare(strict_types=1);

namespace App\Components;

use InvalidArgumentException;

/**
 * How a group of components sits in its box.
 *
 * Justification runs along the main axis, alignment across it; both accept
 * the names Tailwind uses and default to their previous behaviour. The
 * utilities live in the maps rather than being built with string
 * concatenation, because Tailwind only generates a class it can read in the
 * source.
 */
readonly class ContainerOptions
{
    /** @var array<string, string> */
    private const JUSTIFY = [
        'start' => 'justify-start',
        'center' => 'justify-center',
        'end' => 'justify-end',
        'between' => 'justify-between',
        'around' => 'justify-around',
        'evenly' => 'justify-evenly',
        'stretch' => 'justify-stretch',
    ];

    /** @var array<string, string> */
    private const ITEMS = [
        'start' => 'items-start',
        'center' => 'items-center',
        'end' => 'items-end',
        'baseline' => 'items-baseline',
        'stretch' => 'items-stretch',
    ];

    /**
     * @param  string  $justify  One of start, center, end, between, around, evenly, stretch.
     * @param  string  $items  One of start, center, end, baseline, stretch.
     */
    public function __construct(
        public bool $disableFlex = false,
        public bool $flexColumn = false,
        public string $flexGap = 'flex-gap-sm',
        public string $justify = 'center',
        public string $items = 'center',
    ) {
        if (! array_key_exists($justify, self::JUSTIFY)) {
            throw new InvalidArgumentException("Unknown justify value [{$justify}].");
        }

        if (! array_key_exists($items, self::ITEMS)) {
            throw new InvalidArgumentException("Unknown items value [{$items}].");
        }
    }

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
            $classes[] = self::JUSTIFY[$this->justify];
            $classes[] = self::ITEMS[$this->items];
        }

        if ($this->flexColumn) {
            $classes[] = 'flex-columns';
        }

        return implode(' ', $classes);
    }
}
