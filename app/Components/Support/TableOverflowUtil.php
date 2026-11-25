<?php

declare(strict_types=1);

namespace App\Components\Support;

use Juaniquillo\BackendComponents\Factories\ComponentFactory;
use Juaniquillo\BaseBackendComponent\Contracts\BackendComponent;
use Juaniquillo\BaseBackendComponent\Contracts\ThemeComponent;

class TableOverflowUtil
{
    public static function wrapContainer(BackendComponent|ThemeComponent $component): BackendComponent
    {
        return ComponentFactory::make('div')
            ->setTheme('table', 'container')
            ->setContent($component);
    }

    public static function wrapOverflowHidden(BackendComponent|ThemeComponent $component): BackendComponent
    {
        return ComponentFactory::make('div')
            ->setTheme('overflow', 'hidden')
            ->setContent($component);
    }
}
