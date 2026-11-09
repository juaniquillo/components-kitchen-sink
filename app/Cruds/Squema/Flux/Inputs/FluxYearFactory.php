<?php

declare(strict_types=1);

namespace App\Cruds\Squema\Flux\Inputs;

use Juaniquillo\CrudAssistant\Contracts\InputCollectionInterface;
use Juaniquillo\CrudAssistant\Contracts\InputInterface;
use Juaniquillo\CrudAssistant\CrudAssistant;
use Juaniquillo\CrudAssistant\Inputs\DefaultInput;
use Juaniquillo\InputComponentAction\Bags\DefaultAttributeBag;
use Juaniquillo\InputComponentAction\Bags\DefaultComponentBag;
use Juaniquillo\InputComponentAction\Bags\DefaultThemeBag;
use Juaniquillo\InputComponentAction\Recipes\InputComponentRecipe;
use Juaniquillo\InputComponentAction\Recipes\SelectOptionComponentRecipe;

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
                attributeBag: (new DefaultAttributeBag)
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
                'name' => '1990',
                'label' => '1990',
            ],
            [
                'name' => '2000',
                'label' => '2000',
            ],
        ];
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
                componentBag: (new DefaultComponentBag)
                    ->setInputType('select.option')
                // ->setInputComponent(FluxBackendComponent::class)
            );

            $option->setRecipe($optionRecipe);

            $options[] = $option;
        }

        return CrudAssistant::make($options);
    }
}
