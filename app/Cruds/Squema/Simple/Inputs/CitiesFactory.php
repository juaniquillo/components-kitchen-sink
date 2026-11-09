<?php

declare(strict_types=1);

namespace App\Cruds\Squema\Simple\Inputs;

use App\Cruds\Concerns\IsLivewireInput;
use Juaniquillo\BackendComponents\Enums\ComponentEnum;
use Juaniquillo\CrudAssistant\Contracts\InputCollectionInterface;
use Juaniquillo\CrudAssistant\Contracts\InputInterface;
use Juaniquillo\CrudAssistant\CrudAssistant;
use Juaniquillo\CrudAssistant\Inputs\DefaultInput;
use Juaniquillo\InputComponentAction\Bags\DefaultAttributeBag;
use Juaniquillo\InputComponentAction\Bags\DefaultComponentBag;
use Juaniquillo\InputComponentAction\Bags\DefaultDisableBag;
use Juaniquillo\InputComponentAction\Bags\DefaultThemeBag;
use Juaniquillo\InputComponentAction\Recipes\InputComponentRecipe;
use Juaniquillo\InputComponentAction\Recipes\SelectOptionComponentRecipe;

class CitiesFactory
{
    use IsLivewireInput;

    const NAME = 'cities';

    const LABEL = 'Choose City';

    public static function make(?string $livewireGroup = null, ?int $id = null): InputInterface
    {
        $input = new DefaultInput(self::NAME, self::LABEL);

        $input->setSubElements(self::options());

        $attributeBag = new DefaultAttributeBag;

        if ($livewireGroup) {
            self::getLivewireAttributeBag(
                $livewireGroup,
                self::NAME,
                $attributeBag
            );
        }

        $input->setRecipe(
            new InputComponentRecipe(
                attributeBag: $attributeBag,
                componentBag: (new DefaultComponentBag)
                    ->setInputType(ComponentEnum::SELECT),
                disableBag: (new DefaultDisableBag)
                    ->setDisableInputValue()
            )
        );

        return $input;
    }

    public static function options(): InputCollectionInterface
    {
        $options = [];

        foreach (self::optionsArray() as $optionArray) {

            $option = new DefaultInput($optionArray['name'], $optionArray['label']);

            $optionRecipe = new SelectOptionComponentRecipe(
                inputValue: $optionArray['value'] ?? $optionArray['name'],
                themeBag: (new DefaultThemeBag)
                    // reset default theme
                    // for the options
                    ->setInputTheme([]),
            );

            $option->setRecipe($optionRecipe);

            $options[] = $option;
        }

        return CrudAssistant::make($options);

    }

    public static function optionsArray(): array
    {
        return [
            [
                'name' => 'choose',
                'label' => 'Choose...',
                'value' => '',
            ],
            [
                'name' => 'new_york',
                'label' => 'New york',
            ],
            [
                'name' => 'new_york',
                'label' => 'New york',
            ],
            [
                'name' => 'san_francisco',
                'label' => 'San Francisco',
            ],
            [
                'name' => 'chicago',
                'label' => 'Chicago',
            ],
            [
                'name' => 'miami',
                'label' => 'Miami',
            ],
        ];
    }
}
