<?php

declare(strict_types=1);

namespace App\Cruds\Squema\Simple\Inputs;

use App\Cruds\Concerns\IsLivewireInput;
use Juaniquillo\CrudAssistant\Contracts\InputInterface;
use Juaniquillo\CrudAssistant\Inputs\DefaultInput;

class LastNameFactory
{
    use IsLivewireInput;

    const NAME = 'last_name';

    const LABEL = 'Last Name';

    public static function make(?string $livewireGroup = null): InputInterface
    {
        $input = new DefaultInput(self::NAME, self::LABEL);

        if ($livewireGroup) {
            $input->setRecipe(
                recipe: self::manageLivewireRecipe($livewireGroup, self::NAME)
            );
        }

        return $input;
    }
}
