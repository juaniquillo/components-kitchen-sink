<?php

namespace App\Cruds\Squema\InputGroup\Inputs;

use App\Cruds\Actions\Validation\LaravelValidationRulesRecipe;
use Juaniquillo\BackendComponents\Enums\ComponentEnum;
use Juaniquillo\CrudAssistant\Contracts\InputInterface;
use Juaniquillo\CrudAssistant\Inputs\DefaultInput;
use Juaniquillo\InputComponentAction\Bags\DefaultAttributeBag;
use Juaniquillo\InputComponentAction\Bags\DefaultComponentBag;
use Juaniquillo\InputComponentAction\Recipes\InputComponentRecipe;

class EmailFactory
{
    const NAME = 'email_wc';

    const LABEL = 'Email';


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
                    'nullable',
                    'email',
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
                        'required' => 'required',
                        'placeholder' => 'Enter your Email',
                    ]),
                componentBag: (new DefaultComponentBag())
                    ->setInputType(ComponentEnum::EMAIL_INPUT)
            )
        );

        return $input;
    }
}
