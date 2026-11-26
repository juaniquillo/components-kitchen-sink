<?php

declare(strict_types=1);

namespace App\DesignSystem\Boxes;

use App\Components\Options\GroupOptions;
use App\DesignSystem\Boxes\Concerns\IsDesignSystemBox;
use App\DesignSystem\Boxes\Contracts\Box;
use Juaniquillo\BackendDesignSystem\Components\Divider;
use Juaniquillo\BackendDesignSystem\Components\Heading;
use Juaniquillo\BackendDesignSystem\Components\Inline;
use Juaniquillo\BackendDesignSystem\Components\Link;
use Juaniquillo\BackendDesignSystem\Components\Stack;
use Juaniquillo\BackendDesignSystem\Components\Text;
use Juaniquillo\BackendDesignSystem\Designs\Design;
use Juaniquillo\BackendDesignSystem\Theme\HeadingLevel;
use Juaniquillo\BackendDesignSystem\Theme\Size;

final class Typography implements Box
{
    use IsDesignSystemBox;

    public const NAME = 'Typography';

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
                    Heading::make($design)->text('Billing overview')->level(HeadingLevel::H3),
                    Text::make($design)->text('Your plan renews on the first of every month.'),
                    Text::make($design)->text('Prices exclude taxes.')->size(Size::Sm),
                    Inline::make($design)
                        ->space(Size::Sm)
                        ->children([
                            Link::make($design)->label('Manage billing')->href('#typography'),
                            Link::make($design)->label('Current page')->href('#typography')->current(),
                            Link::make($design)->label('Documentation')->href('https://example.com/docs')->external(),
                        ]),
                    Divider::make($design),
                ]),
        ];
    }
}
