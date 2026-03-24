<?php

namespace App\Cruds\Squema\InputGroup\Inputs;

use App\Cruds\Actions\Validation\LaravelValidationRulesRecipe;
use BackedEnum;
use Illuminate\Validation\Rule;
use Juaniquillo\BackendComponents\Contracts\ThemeManager;
use Juaniquillo\BackendComponents\Enums\ComponentEnum;
use Juaniquillo\BackendComponents\MainBackendComponent;
use Juaniquillo\CrudAssistant\Contracts\InputInterface;
use Juaniquillo\CrudAssistant\CrudAssistant;
use Juaniquillo\CrudAssistant\Inputs\DefaultInput;
use Juaniquillo\InputComponentAction\Bags\DefaultAttributeBag;
use Juaniquillo\InputComponentAction\Bags\DefaultComponentBag;
use Juaniquillo\InputComponentAction\Bags\DefaultDisableBag;
use Juaniquillo\InputComponentAction\Bags\DefaultThemeBag;
use Juaniquillo\InputComponentAction\Contracts\ThemeBag;
use Juaniquillo\InputComponentAction\Groups\DefaultInputGroup;
use Juaniquillo\InputComponentAction\Groups\InputLabelErrorGroup;
use Juaniquillo\InputComponentAction\Recipes\InputComponentRecipe;

class FavoriteAnimalFactory
{
    const NAME = 'favorite_animal_wc';

    const LABEL = 'Favorite animal';


    public static function make(): InputInterface
    {
        $input = new DefaultInput(self::NAME, self::LABEL);

        self::formRecipe($input);
        self::validation($input);

        $input->setSubElements(
            CrudAssistant::make(self::radioBoxes())
        );

        return $input;
    }

    public static function formRecipe(InputInterface $input): void
    {
        $input->setRecipe(
            new InputComponentRecipe(
                inputGroup: new DefaultInputGroup(),
                componentBag: (new DefaultComponentBag())
                    ->setLabelType(ComponentEnum::LEGEND)
                    ->setInputType(ComponentEnum::DIV),
                disableBag: (new DefaultDisableBag)
                    ->setDisableDefaultNameAttribute()
                    ->setDisableDefaultForAttribute(),
                themeBag: (new DefaultThemeBag)
                    ->setInputTheme([
                        'display' => 'flex',
                        'padding' => 'xs',
                        'flex' => [
                            'items-center',
                            'gap-lg',
                            'wrap',
                        ],
                    ])
                    ->setErrorTheme([
                        'color' => [
                            'error',
                            'error-dark',
                        ],
                        'padding' => 'top-xs',
                    ]),
            )
        );
    }

    
    public static function validation(InputInterface $input): void
    {
        $input->setRecipe(
            new LaravelValidationRulesRecipe(
                rules: [
                    'required',
                    Rule::in(
                        values: array_column(
                            self::radioArray(), 
                            column_key: 'name')
                        ),
                ]
            )
        );
    }

    
    public static function radioArray(): array
    {
        return [
            [
                'name' => 'horse_wc',
                'label' => 'Horse',
            ],
            [
                'name' => 'snake_wc',
                'label' => 'Snake',
            ],
            [
                'name' => 'dog_wc',
                'label' => 'Dog',
            ],
        ];
    }

    public static function radioBoxes(): array
    {
        $radioBoxes = [];
        $inputAttributes = [
            'name' => self::NAME, 
            'required' => 'required'
        ];
        
        foreach(self::radioArray() as $radio) {

            $input = new DefaultInput($radio['name'], $radio['label']);

            $input->setRecipe(
                new InputComponentRecipe(
                    // Set input value
                    inputValue: $radio['value'] ?? $radio['name'],
                    // Set input name with the same name and the required attribute
                    attributeBag: (new DefaultAttributeBag())
                        ->setInputAttributes($inputAttributes),
                    // Set a div as the wrapper 
                    componentBag: (new DefaultComponentBag())
                        ->setWrapperComponent(function(string|BackedEnum $name, ThemeManager $themeManager){
                            return new MainBackendComponent(ComponentEnum::DIV, $themeManager);
                        })
                        ->setInputType(ComponentEnum::RADIO_INPUT),
                    inputGroup: new InputLabelErrorGroup,
                    // Disable individual input error. Not needed with
                    // An input group without error component
                    disableBag: (new DefaultDisableBag())
                        ->setDisableError(),
                    // Set group tailwind classes
                    themeBag: self::radioGroupThemeBag(),
                )
            );

            $radioBoxes[] = $input;

        }

        return $radioBoxes;

    }

    public static function radioGroupThemeBag(): ThemeBag
    {
        return (new DefaultThemeBag)
            ->setWrapperTheme([
                'display' => 'flex',
                'flex' => [
                    'items-center',
                    'gap-sm',
                ],
            ])
            ->setInputTheme([
                'inputs' => 'radio',
                'size' => 'md',
            ])
            ->setLabelTheme([]);
    }

}
