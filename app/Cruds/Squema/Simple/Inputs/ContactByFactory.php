<?php

declare(strict_types=1);

namespace App\Cruds\Squema\Simple\Inputs;

use App\Cruds\Actions\Validation\LaravelValidationRulesRecipe;
use App\Cruds\Concerns\IsLivewireInput;
use Illuminate\Validation\Rule;
use Juaniquillo\BackendComponents\Enums\ComponentEnum;
use Juaniquillo\CrudAssistant\Contracts\InputInterface;
use Juaniquillo\CrudAssistant\CrudAssistant;
use Juaniquillo\CrudAssistant\Inputs\DefaultInput;
use Juaniquillo\InputComponentAction\Bags\DefaultAttributeBag;
use Juaniquillo\InputComponentAction\Bags\DefaultComponentBag;
use Juaniquillo\InputComponentAction\Bags\DefaultDisableBag;
use Juaniquillo\InputComponentAction\Bags\DefaultThemeBag;
use Juaniquillo\InputComponentAction\Groups\InputLabelErrorGroup;
use Juaniquillo\InputComponentAction\Recipes\InputComponentRecipe;

class ContactByFactory
{
    use IsLivewireInput;

    const NAME = 'contact_by';

    const LABEL = 'Contact by';

    public static function make(?string $livewireGroup = null): InputInterface
    {
        $input = new DefaultInput(self::NAME, self::LABEL);

        $input->setSubElements(
            CrudAssistant::make(self::checkboxes($livewireGroup))
        );

        self::formRecipe($input);
        self::validation($input);

        return $input;
    }

    public static function formRecipe(InputInterface $input): void
    {
        $input->setRecipe(
            new InputComponentRecipe(
                componentBag: (new DefaultComponentBag())
                    ->setWrapperType(ComponentEnum::FIELDSET)
                    ->setInputType(ComponentEnum::DIV)
                    ->setLabelType(ComponentEnum::LEGEND),
                disableBag: (new DefaultDisableBag)
                    ->setDisableDefaultNameAttribute()
                    ->setDisableDefaultForAttribute(),
                themeBag: (new DefaultThemeBag)
                    ->setInputTheme([
                        'display' => 'flex',
                        'flex' => [
                            'items-center',
                            'gap-md',
                            'wrap',
                        ],
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
                        values: array_column(self::checkboxesArray(), 
                        column_key: 'name')),
                ]
            )
        );
    }

    public static function checkboxesArray(): array
    {
        return [
            [
                'name' => 'by_text',
                'label' => 'By Text',
            ],
            [
                'name' => 'by_email',
                'label' => 'By Email',
            ],
            [
                'name' => 'by_phone',
                'label' => 'By Phone',
            ],
        ];
    }

    public static function checkboxes(?string $livewireGroup = null): array
    {
        $checkboxes = [];

        $inputAttributes = ['name' => self::NAME];
        $labelAttributes = [];

        /**
         * wire:model attribute for all inputs
         */
        if($livewireGroup) {
            $inputAttributes = [
                ...self::getLivewireDirective(group: $livewireGroup, name: self::NAME),
            ];
        }

        $attributes = $attributes ?? ['name' => self::NAME,];

        foreach (self::checkboxesArray() as $optionArray) {
            $option = new DefaultInput(name: $optionArray['name'], label: $optionArray['label']);

            if($livewireGroup) {
               
                $inputAttributes = [
                    ...$inputAttributes,
                    /**
                     * input name attribute
                     */
                    ...self::getLivewireId(group: $livewireGroup, name: $optionArray['name']),
                ];
                
                /**
                 * label for attribute
                 */
                $labelAttributes = [
                    'for' => self::getDotNotationName(group: $livewireGroup, name: $optionArray['name']),
                ];

            }

            $optionRecipe = new InputComponentRecipe(
                componentBag: (new DefaultComponentBag())
                    ->setInputType(ComponentEnum::RADIO_INPUT),
                inputValue: $optionArray['value'] ?? $optionArray['name'],
                checkable: true,
                useParentValue: true,
                disableBag: (new DefaultDisableBag)
                    ->setDisableError(),
                inputGroup: new InputLabelErrorGroup,
                attributeBag: (new DefaultAttributeBag)
                    ->setInputAttributes(inputAttributes: $inputAttributes)
                    ->setLabelAttributes(labelAttributes:$labelAttributes),
                themeBag: (new DefaultThemeBag)
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
                    ->setLabelTheme([])
                    ->setErrorTheme([
                        'color' => [
                            'error',
                            'error-dark',
                        ],
                        'padding' => 'top-xs',
                    ]),
            );

            $option->setRecipe($optionRecipe);

            $checkboxes[] = $option;
        }

        return $checkboxes;

    }

}
