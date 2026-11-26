<?php

declare(strict_types=1);

namespace App\DesignSystem\Boxes;

use App\DesignSystem\Boxes\Concerns\IsDesignSystemBox;
use App\DesignSystem\Boxes\Contracts\Box;
use Juaniquillo\BackendDesignSystem\Components\Button;
use Juaniquillo\BackendDesignSystem\Designs\Design;
use Juaniquillo\BackendDesignSystem\Theme\ButtonVariant;
use Juaniquillo\BackendDesignSystem\Theme\Size;

final class Buttons implements Box
{
    use IsDesignSystemBox;

    public const NAME = 'Buttons';

    public function components(Design $design): array
    {
        return [
            Button::make($design)->label('Primary')->variant(ButtonVariant::Primary),
            Button::make($design)->label('Secondary')->variant(ButtonVariant::Secondary),
            Button::make($design)->label('Danger')->variant(ButtonVariant::Danger),
            Button::make($design)->label('Ghost')->variant(ButtonVariant::Ghost),
            Button::make($design)->label('Small')->size(Size::Sm),
            Button::make($design)->label('Large')->size(Size::Lg),
            Button::make($design)->label('Save')->variant(ButtonVariant::Primary)->attributes(['wire:click' => 'save']),
        ];
    }
}
