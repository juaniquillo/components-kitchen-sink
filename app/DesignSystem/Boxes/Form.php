<?php

declare(strict_types=1);

namespace App\DesignSystem\Boxes;

use App\Components\Options\GroupOptions;
use App\DesignSystem\Boxes\Concerns\IsDesignSystemBox;
use App\DesignSystem\Boxes\Contracts\Box;
use Juaniquillo\BackendDesignSystem\Components\Button;
use Juaniquillo\BackendDesignSystem\Components\Checkbox;
use Juaniquillo\BackendDesignSystem\Components\Field;
use Juaniquillo\BackendDesignSystem\Components\Form as FormComponent;
use Juaniquillo\BackendDesignSystem\Components\Select;
use Juaniquillo\BackendDesignSystem\Designs\Design;
use Juaniquillo\BackendDesignSystem\Theme\ButtonVariant;

final class Form implements Box
{
    use IsDesignSystemBox;

    public const NAME = 'Form';

    public function options(): GroupOptions
    {
        return self::flexOffOptions();
    }

    public function components(Design $design): array
    {
        return [
            FormComponent::make($design)
                ->action('/subscribe')
                ->method('post')
                ->fields([
                    Field::make($design)->label('Email')->input('form-email')->required(),
                    Field::make($design)->label('Country')->input(
                        Select::make($design)->name('form-country')->options(['' => 'Choose', 'es' => 'Spain', 'fr' => 'France'])
                    ),
                    Checkbox::make($design)->name('form-terms')->label('I accept the terms'),
                ])
                ->actions(Button::make($design)->label('Subscribe')->variant(ButtonVariant::Primary)->type('submit')),
        ];
    }
}
