<?php

namespace App\Cruds\Squema\Unstyled\Inputs;

use Juaniquillo\CrudAssistant\Contracts\InputInterface;
use Juaniquillo\CrudAssistant\Inputs\DefaultInput;

class NameFactory
{
    const NAME = 'name_unstyled';

    const LABEL = 'Name';

    public static function make() : InputInterface
    {
        $input = new DefaultInput(
            name: self::NAME,
            label: self::LABEL,
        );

        return $input;
    }
}
