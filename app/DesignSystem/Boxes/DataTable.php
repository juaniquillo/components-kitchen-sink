<?php

declare(strict_types=1);

namespace App\DesignSystem\Boxes;

use App\Components\Options\GroupOptions;
use App\DesignSystem\Boxes\Concerns\IsDesignSystemBox;
use App\DesignSystem\Boxes\Contracts\Box;
use App\DesignSystem\Examples\Example;
use Juaniquillo\BackendComponents\Utils\CellBag;
use Juaniquillo\BackendDesignSystem\Components\Badge;
use Juaniquillo\BackendDesignSystem\Components\Stack;
use Juaniquillo\BackendDesignSystem\Components\Table;
use Juaniquillo\BackendDesignSystem\Designs\Design;
use Juaniquillo\BackendDesignSystem\Theme\BadgeVariant;
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
                            ['cells' => [
                                'INV-001',
                                new CellBag(
                                    content: Badge::make($design)->label('Paid')->variant(BadgeVariant::Success)->component(),
                                    attributes: ['data-status' => 'paid'],
                                ),
                                '€120.00',
                            ], 'attributes' => ['data-invoice' => 'INV-001']],
                            ['INV-002', 'Open', '€80.00'],
                        ])
                        ->footer(['', '', '€200.00']),
                    Table::make($design)
                        ->headers(['Invoice', 'Status'])
                        ->empty('No invoices yet.'),
                ]),
        ];
    }

    /**
     * @return array<int, Example>
     */
    public function examples(): array
    {
        return [
            new Example(
                'Invoice list',
                'Badges mark the status per row; the footer totals the column.',
                fn (Design $design): Table => Table::make($design)
                    ->caption('Invoices')
                    ->headers(['Invoice', 'Status', 'Amount'])
                    ->rows([
                        ['INV-1042', Badge::make($design)->label('Paid')->variant(BadgeVariant::Success), '€1,240.00'],
                        ['INV-1043', Badge::make($design)->label('Overdue')->variant(BadgeVariant::Warning), '€860.00'],
                    ])
                    ->footer(['', 'Total due', '€2,100.00']),
            ),
        ];
    }
}
