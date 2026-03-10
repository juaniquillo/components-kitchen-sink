<?php

declare(strict_types=1);

namespace App\Cruds\Squema\Simple\Inputs;

use App\Cruds\Concerns\IsLivewireInput;
use Juaniquillo\BackendComponents\Enums\ComponentEnum;
use Juaniquillo\CrudAssistant\Contracts\InputInterface;
use Juaniquillo\CrudAssistant\Inputs\DefaultInput;
use Juaniquillo\InputComponentAction\Bags\DefaultAttributeBag;
use Juaniquillo\InputComponentAction\Bags\DefaultComponentBag;
use Juaniquillo\InputComponentAction\Bags\DefaultThemeBag;
use Juaniquillo\InputComponentAction\Recipes\InputComponentRecipe;
use Psy\Output\Theme;

class AvatarFactory
{
    use IsLivewireInput;

    const NAME = 'avatar';

    const LABEL = 'Choose your avatar';

    public static function make(?string $livewireGroup = null): InputInterface
    {
        $input = new DefaultInput(self::NAME, self::LABEL);

        $attributeBag = new DefaultAttributeBag;

        if($livewireGroup) {
            self::getLivewireAttributeBag(
                $livewireGroup,
                self::NAME,
                $attributeBag
            );
        }

        $input->setRecipe(
            new InputComponentRecipe(
                componentBag: (new DefaultComponentBag())
                    ->setInputType(ComponentEnum::FILE_INPUT),
                attributeBag: $attributeBag,
                themeBag: (new DefaultThemeBag())
                    ->setInputTheme([
                        'inputs' => 'file-primary',
                        'display' => 'block',

                    ])
            )
        );

        return $input;
    }
}
