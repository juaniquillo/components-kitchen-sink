<?php

declare(strict_types=1);

namespace App\DesignSystem\Boxes\Concerns;

use App\Components\Options\ContainerOptions;
use App\Components\Options\Enums\Items;
use App\Components\Options\Enums\Justify;
use App\Components\Options\GroupOptions;

/**
 * The container options every gallery box shares.
 *
 * The gallery is left-aligned with the roomier gap, unlike the kitchen-sink
 * groups; a box overrides only what changes (flex off, or a column).
 */
trait IsDesignSystemBox
{
    public function options(): GroupOptions
    {
        return self::galleryContainer();
    }

    protected static function flexOffOptions(): GroupOptions
    {
        return self::galleryContainer(disableFlex: true);
    }

    protected static function columnOptions(): GroupOptions
    {
        return self::galleryContainer(flexColumn: true, items: Items::Stretch);
    }

    private static function galleryContainer(
        bool $disableFlex = false,
        bool $flexColumn = false,
        Items $items = Items::Center,
    ): GroupOptions {
        return new GroupOptions(
            container: new ContainerOptions(
                disableFlex: $disableFlex,
                flexColumn: $flexColumn,
                flexGap: 'flex-gap',
                justify: Justify::Start,
                items: $items,
            ),
        );
    }
}
