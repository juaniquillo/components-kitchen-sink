<?php

namespace  App\Components\Builders;

use Illuminate\Contracts\Support\Htmlable;
use Juaniquillo\BackendComponents\Enums\ComponentEnum;
use App\Components\ThirdParty\Flux\FluxBackendComponent;
use Juaniquillo\BackendComponents\Contracts\StaticBuilder;
use Juaniquillo\BackendComponents\Contracts\CompoundComponent;

class FluxComponentBuilder implements StaticBuilder
{
     public static function make(string|ComponentEnum $name): Htmlable|CompoundComponent
     {
        $builder = new FluxBackendComponent($name);

        return $builder;
     }
}
