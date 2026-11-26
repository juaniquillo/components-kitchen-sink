<?php

declare(strict_types=1);

namespace App\DesignSystem\Boxes;

use App\DesignSystem\Boxes\Concerns\IsDesignSystemBox;
use App\DesignSystem\Boxes\Contracts\Box;
use Juaniquillo\BackendDesignSystem\Components\Badge;
use Juaniquillo\BackendDesignSystem\Designs\Design;
use Juaniquillo\BackendDesignSystem\Theme\BadgeVariant;

final class Badges implements Box
{
    use IsDesignSystemBox;

    public const NAME = 'Badges';

    public function components(Design $design): array
    {
        return [
            Badge::make($design)->label('Default'),
            Badge::make($design)->label('Active')->variant(BadgeVariant::Success),
            Badge::make($design)->label('New')->variant(BadgeVariant::Info),
            Badge::make($design)->label('Expiring')->variant(BadgeVariant::Warning),
            Badge::make($design)->label('Failed')->variant(BadgeVariant::Danger),
        ];
    }
}
