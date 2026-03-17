<?php

namespace App\Cruds\Squema\Flux\Inputs;

use Juaniquillo\CrudAssistant\Contracts\InputInterface;
use Juaniquillo\CrudAssistant\CrudAssistant;
use Juaniquillo\CrudAssistant\InputCollection;
use Juaniquillo\CrudAssistant\Inputs\DefaultInput;
use Juaniquillo\InputComponentAction\Bags\DefaultAttributeBag;
use Juaniquillo\InputComponentAction\Bags\DefaultComponentBag;
use Juaniquillo\InputComponentAction\Bags\DefaultDisableBag;
use Juaniquillo\InputComponentAction\Bags\DefaultThemeBag;
use Juaniquillo\InputComponentAction\Groups\SoleInputGroup;
use Juaniquillo\InputComponentAction\Recipes\InputComponentRecipe;

class FluxYearFactory
{
    const NAME = 'flux_year';

    const LABEL = 'Year';

    public static function make(): InputInterface
    {
        $input = new DefaultInput(self::NAME, self::LABEL);

        $input->setRecipe(
            new InputComponentRecipe(
                componentBag: (new DefaultComponentBag)
                    ->setInputType('select'),
                attributeBag: (new DefaultAttributeBag())
                    ->setInputAttributes([
                        'label' => self::LABEL,
                    ])
            )
        );

        $input->setSubElements(self::options());

        return $input;
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
                'name' => 1990,
                'label' => '1990',
            ],
            [
                'name' => 2000,
                'label' => '2000',
            ],
        ];
    }

    public static function options(): InputCollection
    {
        $options = [];

        foreach (self::optionsArray() as $optionArray) {
            $option = new DefaultInput($optionArray['name'], $optionArray['label']);

            $optionRecipe = new InputComponentRecipe(
                inputGroup: new SoleInputGroup,
                inputValue: $optionArray['value'] ?? $optionArray['name'],
                selectable: true,
                useParentValue: true,
                labelAsInputContent: true,
                disableBag: (new DefaultDisableBag())
                    ->setDisableWrapper()
                    ->setDisableDefaultNameAttribute(),
                themeBag: (new DefaultThemeBag)
                    // reset default theme
                    // for the options
                    ->setInputTheme([]),
                componentBag: (new DefaultComponentBag())
                    ->setInputType('select.option')
                    // ->setInputComponent(FluxBackendComponent::class)
            );

            $option->setRecipe($optionRecipe);

            $options[] = $option;
        }

        return CrudAssistant::make($options);
    }
}
