<?php

namespace App\Components\Builders;

use App\Components\ThirdParty\Flux\FluxBackendComponent;
use BackedEnum;
use Illuminate\Contracts\Support\Htmlable;
use Juaniquillo\BackendComponents\Contracts\CompoundComponent;
use Juaniquillo\BackendComponents\Contracts\StaticBuilder;

class FluxComponentBuilder implements StaticBuilder
{
    public static function make(string|BackedEnum $name): Htmlable|CompoundComponent
    {
        $builder = new FluxBackendComponent($name);

        return $builder;
    }
}
