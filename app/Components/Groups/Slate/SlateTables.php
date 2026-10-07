<?php

declare(strict_types=1);

namespace App\Components\Groups\Slate;

use App\Components\ContainerOptions;
use App\Components\Contracts\Component;
use Juaniquillo\BackendComponents\Contracts\BackendComponent;
use Juaniquillo\BackendComponents\Contracts\ContentComponent;
use Juaniquillo\SlateBackendComponents\SlateBackendComponent;
use Juaniquillo\SlateBackendComponents\SlateComponentEnum;
use Juaniquillo\SlateBackendComponents\Utils\SlateUITableUtil;

class SlateTables implements Component
{
    const NAME = 'Tables';

    public static function list(): array
    {
        return [
            self::simple(),
        ];
    }

    public static function options(): ContainerOptions
    {
        return new ContainerOptions(
            disableFlex: true,
        );
    }

    public static function simple(): BackendComponent
    {
        $table = SlateUITableUtil::make(
            head: ['Invoice', 'Status', 'Method', 'Amount'],
            body: [
                ['INV001', 'Paid', 'Credit card', '$250.00'],
                ['INV002', 'Pending', 'PayPal', '$150.00'],
                ['INV003', 'Unpaid', 'Bank transfer', '$350.00'],
            ],
        )->getComponent();

        if ($table instanceof ContentComponent) {
            $table->setContent(
                (new SlateBackendComponent(SlateComponentEnum::TABLE_FOOTER))
                    ->setContents([
                        (new SlateBackendComponent(SlateComponentEnum::TABLE_ROW))
                            ->setContents([
                                (new SlateBackendComponent(SlateComponentEnum::TABLE_CELL))
                                    ->setAttribute('colspan', 3)
                                    ->setContent('Total'),
                                (new SlateBackendComponent(SlateComponentEnum::TABLE_CELL))
                                    ->setContent('$750.00'),
                            ]),
                    ])
            );
        }

        return $table;
    }
}
