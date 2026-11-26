<?php

declare(strict_types=1);

namespace App\DesignSystem\Boxes;

use App\Components\Options\GroupOptions;
use App\DesignSystem\Boxes\Concerns\IsDesignSystemBox;
use App\DesignSystem\Boxes\Contracts\Box;
use Juaniquillo\BackendDesignSystem\Components\Stack;
use Juaniquillo\BackendDesignSystem\Components\Table;
use Juaniquillo\BackendDesignSystem\Designs\Design;
use Juaniquillo\BackendDesignSystem\Theme\Size;

final class DataTable implements Box
{
    use IsDesignSystemBox;

    public const NAME = 'Data table';

    public function options(): GroupOptions
    {
        return self::flexOffOptions();
    }

    public function components(Design $design): array
    {
        return [
            Stack::make($design)
                ->space(Size::Md)
                ->children([
                    Table::make($design)
                        ->caption('Invoices')
                        ->headers(['Invoice', 'Status', 'Amount'])
                        ->rows([
                            ['INV-001', 'Paid', '€120.00'],
                            ['INV-002', 'Open', '€80.00'],
                        ])
                        ->footer(['', '', '€200.00']),
                    Table::make($design)
                        ->headers(['Invoice', 'Status'])
                        ->empty('No invoices yet.'),
                ]),
        ];
    }
}
