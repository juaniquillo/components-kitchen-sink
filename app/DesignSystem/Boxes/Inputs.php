<?php

declare(strict_types=1);

namespace App\DesignSystem\Boxes;

use App\DesignSystem\Boxes\Concerns\IsDesignSystemBox;
use App\DesignSystem\Boxes\Contracts\Box;
use Juaniquillo\BackendDesignSystem\Components\Input;
use Juaniquillo\BackendDesignSystem\Designs\Design;

final class Inputs implements Box
{
    use IsDesignSystemBox;

    public const NAME = 'Inputs';

    public function components(Design $design): array
    {
        return [
            Input::make($design)->name('email')->type('email')->placeholder('you@example.com')->attributes(['aria-label' => 'Email']),
            Input::make($design)->name('password')->type('password')->placeholder('Password')->required()->attributes(['aria-label' => 'Password']),
            Input::make($design)->name('nickname')->placeholder('Disabled')->disabled()->attributes(['aria-label' => 'Disabled example']),
        ];
    }
}
