<?php

namespace App\Components\Groups\Slate;

use App\Components\ContainerOptions;
use App\Components\Contracts\Component;
use App\Components\ThirdParty\Slate\SlateBackendComponent;
use Juaniquillo\BackendComponents\Contracts\BackendComponent;

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

    public  static function simple(): BackendComponent
    {
        $table = SlateBackendComponent::make('table');

        $table->setContent(
            SlateBackendComponent::make('table-header')
                ->setContent(
                    SlateBackendComponent::make('table-row')
                        ->setContents([
                            SlateBackendComponent::make('table-head')
                                ->setContent('Invoice'),
                            SlateBackendComponent::make('table-head')
                                ->setContent('Status'),
                            SlateBackendComponent::make('table-head')
                                ->setContent('Method'),
                            SlateBackendComponent::make('table-head')
                                ->setContent('Amount'),
                        ])
                )
        );

        $table->setContent(
            SlateBackendComponent::make('table-body')
                ->setContents([
                    SlateBackendComponent::make('table-row')
                        ->setContents([
                            SlateBackendComponent::make('table-cell')
                                ->setContent('INV001'),
                            SlateBackendComponent::make('table-cell')
                                ->setContent('Paid'),
                            SlateBackendComponent::make('table-cell')
                                ->setContent('Credit card'),
                            SlateBackendComponent::make('table-cell')
                                ->setContent('$250.00'),
                        ]),
                    
                ])
        );

        $table->setContent(
            SlateBackendComponent::make('table-body')
                ->setContents([
                    SlateBackendComponent::make('table-row')
                        ->setContents([
                            SlateBackendComponent::make('table-cell')
                                ->setContent('INV002'),
                            SlateBackendComponent::make('table-cell')
                                ->setContent('Pending'),
                            SlateBackendComponent::make('table-cell')
                                ->setContent('PayPal'),
                            SlateBackendComponent::make('table-cell')
                                ->setContent('$150.00'),
                        ]),
                    
                ])
        );

        $table->setContent(
            SlateBackendComponent::make('table-body')
                ->setContents([
                    SlateBackendComponent::make('table-row')
                        ->setContents([
                            SlateBackendComponent::make('table-cell')
                                ->setContent('INV003'),
                            SlateBackendComponent::make('table-cell')
                                ->setContent('Unpaid'),
                            SlateBackendComponent::make('table-cell')
                                ->setContent('Bank transfer'),
                            SlateBackendComponent::make('table-cell')
                                ->setContent('$350.00'),
                        ]),
                    
                ])
        );
        $table->setContent(
            SlateBackendComponent::make('table-footer')
                ->setContents([
                    SlateBackendComponent::make('table-row')
                        ->setContents([
                            SlateBackendComponent::make('table-cell')
                                ->setAttribute('colspan', 3)
                                ->setContent('Total'),
                            SlateBackendComponent::make('table-cell')
                                ->setContent('$750.00'),
                        ]),
                    
                ])
        );

        return $table;
    }
}

// <x-slate::table>
//    
//     <x-slate::table-body>
//         
//     <x-slate::table-footer>
//         <x-slate::table-row>
//             <x-slate::table-cell colspan="3">Total</x-slate::table-cell>
//             <x-slate::table-cell class="text-end">$750.00</x-slate::table-cell>
//         </x-slate::table-row>
//     </x-slate::table-footer>
// </x-slate::table>