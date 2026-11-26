<?php

declare(strict_types=1);

namespace App\DesignSystem\Boxes;

use App\DesignSystem\Boxes\Concerns\IsDesignSystemBox;
use App\DesignSystem\Boxes\Contracts\Box;
use App\DesignSystem\Examples\Example;
use Juaniquillo\BackendDesignSystem\Components\Badge;
use Juaniquillo\BackendDesignSystem\Components\Button;
use Juaniquillo\BackendDesignSystem\Components\Card;
use Juaniquillo\BackendDesignSystem\Components\Heading;
use Juaniquillo\BackendDesignSystem\Designs\Design;
use Juaniquillo\BackendDesignSystem\Theme\BadgeVariant;
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

    /**
     * @return array<int, Example>
     */
    public function examples(): array
    {
        return [
            new Example(
                'KPI card',
                'A metric with its context: the label on top, the value in the body, the trend in the footer.',
                fn (Design $design): Card => Card::make($design)
                    ->title('Total revenue')
                    ->body(Heading::make($design)->text('$48,290')->level(2))
                    ->footer(Badge::make($design)->label('+12.4% vs last month')->variant(BadgeVariant::Success)),
            ),
        ];
    }
}
