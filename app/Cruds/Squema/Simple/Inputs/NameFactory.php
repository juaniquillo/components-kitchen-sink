<?php

declare(strict_types=1);

namespace App\Cruds\Squema\Simple\Inputs;

use App\Cruds\Concerns\IsLivewireInput;
use Juaniquillo\CrudAssistant\Inputs\DefaultInput;
use Juaniquillo\CrudAssistant\Contracts\InputInterface;
use App\Cruds\Actions\Validation\LaravelValidationRulesRecipe;

class NameFactory
{
    use IsLivewireInput;

    const NAME = 'name';

    const LABEL = 'Name';

    public static function make(?string $livewireGroup = null): InputInterface
    {
        $input = new DefaultInput(self::NAME, self::LABEL);

        if($livewireGroup) {
            $input->setRecipe(
                recipe: self::manageLivewireRecipe($livewireGroup, self::NAME)
            );
        }

        $input->setRecipe(
            new LaravelValidationRulesRecipe(
                rules: [
                    'required',
                    'min:3',
                    'max:255',
                ]
            )
        );

        return $input;
    }
    
}
