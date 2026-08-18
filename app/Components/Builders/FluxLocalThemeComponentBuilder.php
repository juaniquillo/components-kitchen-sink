<?php

declare(strict_types=1);

namespace App\Components\Builders;

use App\Components\ThirdParty\Flux\FluxBackendComponent;
use BackedEnum;
use Illuminate\Contracts\Support\Htmlable;
use Juaniquillo\BackendComponents\Contracts\CompoundComponent;
use Juaniquillo\BackendComponents\Contracts\StaticBuilder;
use Juaniquillo\BackendComponents\Themes\LocalThemeManager;

class FluxLocalThemeComponentBuilder implements StaticBuilder
{
    public static function make(string|BackedEnum $name): Htmlable|CompoundComponent
    {
        $builder = new FluxBackendComponent($name, new LocalThemeManager);

        return $builder;
    }
}
