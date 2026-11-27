<?php

declare(strict_types=1);

namespace App\DesignSystem\Boxes;

use App\DesignSystem\Boxes\Concerns\IsDesignSystemBox;
use App\DesignSystem\Boxes\Contracts\Box;
use App\DesignSystem\Examples\Example;
use Juaniquillo\BackendComponents\Enums\ComponentEnum;
use Juaniquillo\BackendComponents\Factories\LocalThemeComponentFactory;
use Juaniquillo\BackendComponents\MainBackendComponent;
use Juaniquillo\BackendDesignSystem\Components\Button;
use Juaniquillo\BackendDesignSystem\Components\Inline;
use Juaniquillo\BackendDesignSystem\Designs\Design;
use Juaniquillo\BackendDesignSystem\Theme\ButtonVariant;
use Juaniquillo\BackendDesignSystem\Theme\Size;
use RuntimeException;

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
            Button::make($design)->label(self::icon('sun'))->variant(ButtonVariant::Secondary)->attributes(['aria-label' => 'Light mode']),
            Button::make($design)->label('Save')->variant(ButtonVariant::Primary)->attributes(['wire:click' => 'save']),
        ];
    }

    private static function icon(string $name): MainBackendComponent
    {
        $glyph = LocalThemeComponentFactory::make(ComponentEnum::SPAN);

        if (! $glyph instanceof MainBackendComponent) {
            throw new RuntimeException('Buttons builds MainBackendComponent instances.');
        }

        return $glyph
            ->setAttributes(['aria-hidden' => 'true'])
            ->setThemes(['nav' => 'icon-'.$name]);
    }

    /**
     * @return array<int, Example>
     */
    public function examples(): array
    {
        return [
            new Example(
                'Page toolbar',
                'Secondary actions stay quiet so the primary one wins.',
                fn (Design $design): Inline => Inline::make($design)->children([
                    Button::make($design)->label('Filter'),
                    Button::make($design)->label('Export')->variant(ButtonVariant::Ghost),
                    Button::make($design)->label('New invoice')->variant(ButtonVariant::Primary),
                ]),
            ),
        ];
    }
}
