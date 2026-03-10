<?php

namespace App\Cruds\Squema\Unstyled\Inputs;

use App\Cruds\Actions\Validation\LaravelValidationRulesRecipe;
use Juaniquillo\CrudAssistant\Contracts\InputInterface;
use Juaniquillo\CrudAssistant\Inputs\DefaultInput;

class EmailFactory
{
    const NAME = 'email_unstyled';

    const LABEL = 'Email';

    public static function make() : InputInterface
    {        
        $input = new DefaultInput(
            name: self::NAME,
            label: self::LABEL,
        );

        $input->setRecipe(
            (new LaravelValidationRulesRecipe(
                rules: [
                    'required',
                    'email',
                ]
            ))
        );

        return $input;
    }
}
