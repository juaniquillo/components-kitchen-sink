<?php

declare(strict_types=1);

namespace App\DesignSystem\Boxes;

use App\Components\Options\GroupOptions;
use App\DesignSystem\Boxes\Concerns\IsDesignSystemBox;
use App\DesignSystem\Boxes\Contracts\Box;
use Juaniquillo\BackendDesignSystem\Components\Checkbox;
use Juaniquillo\BackendDesignSystem\Components\ChoiceGroup;
use Juaniquillo\BackendDesignSystem\Components\Inline;
use Juaniquillo\BackendDesignSystem\Components\Radio;
use Juaniquillo\BackendDesignSystem\Components\Stack;
use Juaniquillo\BackendDesignSystem\Designs\Design;
use Juaniquillo\BackendDesignSystem\Theme\Size;

final class Choices implements Box
{
    use IsDesignSystemBox;

    public const NAME = 'Choices';

    public function options(): GroupOptions
    {
        return self::flexOffOptions();
    }

    public function components(Design $design): array
    {
        return [
            Stack::make($design)
                ->space(Size::Sm)
                ->children([
                    Inline::make($design)
                        ->space(Size::Sm)
                        ->children([
                            Checkbox::make($design)->name('terms')->label('I accept the terms')->checked(),
                            Checkbox::make($design)->name('newsletter')->label('Send me the newsletter'),
                            Checkbox::make($design)->name('locked')->label('Locked choice')->disabled(),
                        ]),
                    Inline::make($design)
                        ->space(Size::Sm)
                        ->children([
                            Radio::make($design)->name('plan')->value('free')->label('Free')->checked(),
                            Radio::make($design)->name('plan')->value('pro')->label('Pro'),
                            Radio::make($design)->name('plan')->value('team')->label('Team'),
                        ]),
                    ChoiceGroup::make($design)
                        ->legend('Billing cycle')
                        ->id('billing-cycle')
                        ->choices([
                            Radio::make($design)->name('cycle')->value('monthly')->label('Monthly'),
                            Radio::make($design)->name('cycle')->value('yearly')->label('Yearly'),
                        ])
                        ->error('Choose a billing cycle.'),
                ]),
        ];
    }
}
