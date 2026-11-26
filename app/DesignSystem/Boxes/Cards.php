<?php

declare(strict_types=1);

namespace App\DesignSystem\Boxes;

use App\DesignSystem\Boxes\Concerns\IsDesignSystemBox;
use App\DesignSystem\Boxes\Contracts\Box;
use Juaniquillo\BackendDesignSystem\Components\Button;
use Juaniquillo\BackendDesignSystem\Components\Card;
use Juaniquillo\BackendDesignSystem\Designs\Design;
use Juaniquillo\BackendDesignSystem\Theme\ButtonVariant;

final class Cards implements Box
{
    use IsDesignSystemBox;

    public const NAME = 'Cards';

    public function components(Design $design): array
    {
        return [
            Card::make($design)
                ->title('Billing')
                ->body('Your plan renews on the first of every month.')
                ->footer(Button::make($design)->label('Manage')->variant(ButtonVariant::Primary)),
            Card::make($design)
                ->title('About')
                ->body('A kitchen sink of components built with backend-component trees.'),
        ];
    }
}
