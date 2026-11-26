<?php

declare(strict_types=1);

namespace App\DesignSystem\Boxes;

use App\Components\Options\GroupOptions;
use App\DesignSystem\Boxes\Concerns\IsDesignSystemBox;
use App\DesignSystem\Boxes\Contracts\Box;
use Juaniquillo\BackendDesignSystem\Components\Button;
use Juaniquillo\BackendDesignSystem\Components\Dialog as DialogComponent;
use Juaniquillo\BackendDesignSystem\Components\Inline;
use Juaniquillo\BackendDesignSystem\Designs\Design;
use Juaniquillo\BackendDesignSystem\Theme\ButtonVariant;
use Juaniquillo\BackendDesignSystem\Theme\Size;

final class Dialog implements Box
{
    use IsDesignSystemBox;

    public const NAME = 'Dialog';

    public function options(): GroupOptions
    {
        return self::flexOffOptions();
    }

    public function components(Design $design): array
    {
        return [
            Button::make($design)->label('Open dialog')->variant(ButtonVariant::Primary)->attributes([
                'command' => 'show-modal',
                'commandfor' => 'billing-dialog',
            ]),
            DialogComponent::make($design)
                ->id('billing-dialog')
                ->title('Cancel subscription')
                ->body('You keep access until the end of the billing period.')
                ->footer(Inline::make($design)->space(Size::Sm)->children([
                    Button::make($design)->label('Keep plan')
                        ->attributes([
                            'command' => 'close', 'commandfor' => 'billing-dialog',
                        ]),
                    Button::make($design)->label('Cancel plan')->variant(ButtonVariant::Danger)
                        ->attributes([
                            'command' => 'close', 'commandfor' => 'billing-dialog',
                        ]),
                ])),
        ];
    }
}
