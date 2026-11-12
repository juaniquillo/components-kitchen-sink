<?php

declare(strict_types=1);

namespace App\Components\Builders;

use Illuminate\Contracts\Support\Htmlable;
use Juaniquillo\BackendComponents\Contracts\StaticFactory;
use Juaniquillo\BackendComponents\Enums\ComponentEnum;
use Juaniquillo\BackendComponents\MainBackendComponent;
use Juaniquillo\BackendComponents\Themes\LocalThemeManager;
use Juaniquillo\BaseBackendComponent\Contracts\CompoundComponent;

final class FormGroupWebComponentBuilder implements StaticFactory
{
    public static function make(string|ComponentEnum $name): Htmlable|CompoundComponent
    {
        $themeManager = new LocalThemeManager;

        return (new MainBackendComponent(name: 'form-group-web-component.'.$name, themeManager: $themeManager))
            ->useLocal();
    }
}
