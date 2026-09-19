<?php

declare(strict_types=1);

namespace App\Cruds\Squema\Unstyled\Inputs;

use App\Cruds\Actions\Validation\LaravelValidationRulesRecipe;
use Juaniquillo\CrudAssistant\Contracts\InputInterface;
use Juaniquillo\CrudAssistant\Inputs\DefaultInput;
use Juaniquillo\InputComponentAction\Bags\DefaultAttributeBag;
use Juaniquillo\InputComponentAction\Recipes\InputComponentRecipe;

class EmailFactory
{
    const NAME = 'email_unstyled';

    const LABEL = 'Email';

    public static function make(): InputInterface
    {
        $input = new DefaultInput(
            name: self::NAME,
            label: self::LABEL,
        );

        self::form($input);

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

    public static function form(InputInterface $input): void
    {
        $input->setRecipe(
            new InputComponentRecipe(
                attributeBag: (new DefaultAttributeBag)
                    ->setInputAttributes([
                        'type' => 'email',
                    ])
            )
        );
    }
}
