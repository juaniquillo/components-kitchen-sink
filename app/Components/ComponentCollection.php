<?php

namespace App\Components;

use App\Components\Groups\FluxModals;
use App\Components\Groups\Modals;

class ComponentCollection
{
    public static function list() : array
    {
        return [
            Modals::NAME => [
                'group' => Modals::make(),
            ],
            FluxModals::NAME => [
                'group' => FluxModals::make(),
            ],
        ];
    }

}
