<?php

declare(strict_types=1);

namespace App\DesignSystem\Boxes;

use App\Components\Options\GroupOptions;
use App\DesignSystem\Boxes\Concerns\IsDesignSystemBox;
use App\DesignSystem\Boxes\Contracts\Box;
use Juaniquillo\BackendDesignSystem\Components\Button;
use Juaniquillo\BackendDesignSystem\Components\Inline;
use Juaniquillo\BackendDesignSystem\Components\Stack;
use Juaniquillo\BackendDesignSystem\Components\Text;
use Juaniquillo\BackendDesignSystem\Designs\Design;
use Juaniquillo\BackendDesignSystem\Theme\ButtonVariant;
use Juaniquillo\BackendDesignSystem\Theme\Size;

final class Layout implements Box
{
    use IsDesignSystemBox;

    public const NAME = 'Layout';

    public function options(): GroupOptions
    {
        return self::flexOffOptions();
    }

    public function components(Design $design): array
    {
        return [
            Stack::make($design)
                ->space(Size::Lg)
                ->children([
                    Stack::make($design)
                        ->space(Size::Sm)
                        ->children([
                            Text::make($design)->text('Stacked heading'),
                            Text::make($design)->text('Stacked body copy.')->size(Size::Sm),
                        ]),
                    Inline::make($design)
                        ->space(Size::Sm)
                        ->children([
                            Button::make($design)->label('Cancel'),
                            Button::make($design)->label('Save')->variant(ButtonVariant::Primary),
                        ]),
                ]),
        ];
    }
}
