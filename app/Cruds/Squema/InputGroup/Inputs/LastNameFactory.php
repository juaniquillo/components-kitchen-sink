<?php

namespace App\Cruds\Squema\InputGroup\Inputs;

use App\Cruds\Actions\Validation\LaravelValidationRulesRecipe;
use Juaniquillo\CrudAssistant\Contracts\InputInterface;
use Juaniquillo\CrudAssistant\Inputs\DefaultInput;
use Juaniquillo\InputComponentAction\Bags\DefaultAttributeBag;
use Juaniquillo\InputComponentAction\Recipes\InputComponentRecipe;

class LastNameFactory
{
    
    const NAME = 'last_name_wc';

    const LABEL = 'Last Name';


    public static function make(): InputInterface
    {
        $input = new DefaultInput(self::NAME, self::LABEL);

        /**
         * Laravel validation recipe
         */
        $input->setRecipe(
            new LaravelValidationRulesRecipe(
                rules: [
                    'nullable',
                    'min:3',
                    'max:255',
                ]
            )
        );

        /**
         * Input recipe
         */
        $input->setRecipe(
            new InputComponentRecipe(
                attributeBag: (new DefaultAttributeBag())
                    ->setInputAttributes([
                        'placeholder' => 'Enter your last name'
                    ])
            )
        );

        return $input;
    }
}
