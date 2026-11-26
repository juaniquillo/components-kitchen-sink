<?php

declare(strict_types=1);

namespace App\DesignSystem\Boxes;

use App\Components\Options\GroupOptions;
use App\DesignSystem\Boxes\Concerns\IsDesignSystemBox;
use App\DesignSystem\Boxes\Contracts\Box;
use Juaniquillo\BackendDesignSystem\Components\Tabs as TabsComponent;
use Juaniquillo\BackendDesignSystem\Designs\Design;

final class Tabs implements Box
{
    use IsDesignSystemBox;

    public const NAME = 'Tabs';

    public function options(): GroupOptions
    {
        return self::flexOffOptions();
    }

    public function components(Design $design): array
    {
        return [
            TabsComponent::make($design)
                ->label('Billing sections')
                ->items([
                    ['label' => 'Overview', 'href' => '#tabs', 'current' => true],
                    ['label' => 'Invoices', 'href' => '#tabs'],
                    ['label' => 'Settings'],
                ]),
        ];
    }
}
