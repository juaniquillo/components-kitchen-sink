<?php

namespace App\Cruds\Squema\Flux\Inputs;

use App\Cruds\Actions\Validation\LaravelValidationRulesRecipe;
use Juaniquillo\CrudAssistant\Contracts\InputInterface;
use Juaniquillo\CrudAssistant\Inputs\DefaultInput;

class FluxNameFactory
{
    const NAME = 'flux_name';

    const LABEL = 'Name';

    public static function make() : InputInterface
    {
        $input = new DefaultInput(self::NAME, self::LABEL);

        self::validation($input);

        return $input;
    }

    public static function validation(InputInterface $input) : void
    {
        $input->setRecipe(
            new LaravelValidationRulesRecipe([
                'required'
            ])
        );
    }
}
