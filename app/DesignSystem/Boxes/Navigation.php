<?php

declare(strict_types=1);

namespace App\DesignSystem\Boxes;

use App\Components\Options\GroupOptions;
use App\DesignSystem\Boxes\Concerns\IsDesignSystemBox;
use App\DesignSystem\Boxes\Contracts\Box;
use Juaniquillo\BackendDesignSystem\Components\Breadcrumb;
use Juaniquillo\BackendDesignSystem\Designs\Design;

final class Navigation implements Box
{
    use IsDesignSystemBox;

    public const NAME = 'Navigation';

    public function options(): GroupOptions
    {
        return self::flexOffOptions();
    }

    public function components(Design $design): array
    {
        return [
            Breadcrumb::make($design)->items([
                ['label' => 'Home', 'href' => '#navigation'],
                ['label' => 'Billing', 'href' => '#navigation'],
                ['label' => 'Invoices'],
            ]),
        ];
    }
}
