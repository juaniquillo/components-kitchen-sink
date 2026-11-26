<?php

declare(strict_types=1);

namespace App\DesignSystem\Boxes;

use App\Components\Options\GroupOptions;
use App\DesignSystem\Boxes\Concerns\IsDesignSystemBox;
use App\DesignSystem\Boxes\Contracts\Box;
use Juaniquillo\BackendDesignSystem\Components\Accordion;
use Juaniquillo\BackendDesignSystem\Designs\Design;

final class Disclosure implements Box
{
    use IsDesignSystemBox;

    public const NAME = 'Disclosure';

    public function options(): GroupOptions
    {
        return self::columnOptions();
    }

    public function components(Design $design): array
    {
        return [
            Accordion::make($design)
                ->summary('Shipping details')
                ->content('Ships in two business days.'),
            Accordion::make($design)
                ->summary('Returns')
                ->content('Thirty days, unused and in the original box.')
                ->open(),
        ];
    }
}
