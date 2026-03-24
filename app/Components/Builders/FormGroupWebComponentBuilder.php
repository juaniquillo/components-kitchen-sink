<?php

namespace  App\Components\Builders;

use Illuminate\Contracts\Support\Htmlable;
use Juaniquillo\BackendComponents\Enums\ComponentEnum;
use Juaniquillo\BackendComponents\Contracts\StaticBuilder;
use Juaniquillo\BackendComponents\Contracts\CompoundComponent;
use Juaniquillo\BackendComponents\MainBackendComponent;
use Juaniquillo\BackendComponents\Themes\LocalThemeManager;

final class FormGroupWebComponentBuilder implements StaticBuilder
{
    public static function make(string|ComponentEnum $name): Htmlable|CompoundComponent
    {
        $themeManager = new LocalThemeManager();

        return (new MainBackendComponent(name: 'form-group-web-component.'.$name, themeManager: $themeManager))
            ->useLocal();
    }
}
