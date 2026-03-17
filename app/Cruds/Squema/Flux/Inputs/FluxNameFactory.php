<?php

namespace App\Cruds\Squema\Flux\Inputs;

use App\Cruds\Actions\Validation\LaravelValidationRulesRecipe;
use Juaniquillo\CrudAssistant\Contracts\InputInterface;
use Juaniquillo\CrudAssistant\Inputs\DefaultInput;
use Juaniquillo\InputComponentAction\Bags\DefaultAttributeBag;
use Juaniquillo\InputComponentAction\Recipes\InputComponentRecipe;

class FluxNameFactory
{
    const NAME = 'flux_name';

    const LABEL = 'Name';

    public static function make() : InputInterface
    {
        $input = new DefaultInput(self::NAME, self::LABEL);

        self::validation($input);

        self::form($input);

        return $input;
    }

    public static function validation(InputInterface $input): void
    {
        $input->setRecipe(
            recipe: new LaravelValidationRulesRecipe([
                'required'
            ])
        );
    }

    public static function form(InputInterface $input): void
    {
        $input->setRecipe(
            recipe: new InputComponentRecipe(
                attributeBag: (new DefaultAttributeBag())
                    ->setInputAttributes([
                        'label' => self::LABEL,
                    ])

            )
        );
    }
}
