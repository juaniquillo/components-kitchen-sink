<?php

declare(strict_types=1);

namespace App\DesignSystem\Boxes;

use App\Components\Options\GroupOptions;
use App\DesignSystem\Boxes\Concerns\IsDesignSystemBox;
use App\DesignSystem\Boxes\Contracts\Box;
use Juaniquillo\BackendDesignSystem\Components\Select;
use Juaniquillo\BackendDesignSystem\Components\Textarea;
use Juaniquillo\BackendDesignSystem\Designs\Design;

final class SelectAndTextarea implements Box
{
    use IsDesignSystemBox;

    public const NAME = 'Select and Textarea';

    public function options(): GroupOptions
    {
        return self::columnOptions();
    }

    public function components(Design $design): array
    {
        return [
            Select::make($design)->name('country')->options([
                '' => 'Choose a country',
                'es' => 'Spain',
                'fr' => 'France',
                'de' => 'Germany',
            ])->value('fr')->attributes(['aria-label' => 'Country']),
            Select::make($design)->name('disabled-select')->options(['' => 'Not available'])->disabled()->attributes(['aria-label' => 'Unavailable country']),
            Textarea::make($design)->name('notes')->rows(3)->placeholder('Anything we should know?')->attributes(['aria-label' => 'Notes']),
        ];
    }
}
