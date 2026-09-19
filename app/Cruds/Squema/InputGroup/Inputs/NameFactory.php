<?php

declare(strict_types=1);

namespace App\Cruds\Squema\InputGroup\Inputs;

use App\Cruds\Actions\Validation\LaravelValidationRulesRecipe;
use Juaniquillo\CrudAssistant\Contracts\InputInterface;
use Juaniquillo\CrudAssistant\Inputs\DefaultInput;
use Juaniquillo\InputComponentAction\Bags\DefaultAttributeBag;
use Juaniquillo\InputComponentAction\Recipes\InputComponentRecipe;

class NameFactory
{
    const NAME = 'name_wc';

    const LABEL = 'Name';

    public static function make(): InputInterface
    {
        $input = new DefaultInput(self::NAME, self::LABEL);

        /**
         * Laravel validation recipe
         */
        $input->setRecipe(
            new LaravelValidationRulesRecipe(
                rules: [
                    'required',
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
                attributeBag: (new DefaultAttributeBag)
                    ->setInputAttributes([
                        'required' => 'required',
                        'placeholder' => 'Enter your name',
                    ]
                    ),
            )
        );

        return $input;
    }
}
