<?php

declare(strict_types=1);

namespace App\DesignSystem\Boxes;

use App\DesignSystem\Boxes\Concerns\IsDesignSystemBox;
use App\DesignSystem\Boxes\Contracts\Box;
use Juaniquillo\BackendDesignSystem\Components\Field;
use Juaniquillo\BackendDesignSystem\Components\Input;
use Juaniquillo\BackendDesignSystem\Designs\Design;

final class Fields implements Box
{
    use IsDesignSystemBox;

    public const NAME = 'Fields';

    public function components(Design $design): array
    {
        return [
            Field::make($design)
                ->label('Email')
                ->input(Input::make($design)->name('email')->type('email')->attributes(['id' => 'signup-email'])),
            Field::make($design)
                ->label('Confirm email')
                ->input('confirm-email')
                ->error('The email addresses do not match.')
                ->required(),
        ];
    }
}
