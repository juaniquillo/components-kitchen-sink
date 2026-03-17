<?php

namespace App\Cruds\Squema\Flux\Inputs;

use Juaniquillo\CrudAssistant\Contracts\InputInterface;
use Juaniquillo\CrudAssistant\Inputs\DefaultInput;
use Juaniquillo\InputComponentAction\Bags\DefaultAttributeBag;
use Juaniquillo\InputComponentAction\Recipes\InputComponentRecipe;

class FluxEmailFactory
{
    const NAME = 'flux_email';

    const LABEL = 'Email';

    public static function make() : InputInterface
    {
        $input = new DefaultInput(self::NAME, self::LABEL);

        self::form($input);

        return $input;
    }

    public static function form(InputInterface $input): void
    {
        $input->setRecipe(
            new InputComponentRecipe(
                attributeBag: (new DefaultAttributeBag)
                    ->setInputAttributes([
                        'type' => 'email',
                        'label' => self::LABEL,
                    ])
            )
        );
    }
}
