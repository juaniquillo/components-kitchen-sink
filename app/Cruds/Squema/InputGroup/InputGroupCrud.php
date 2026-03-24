<?php

namespace App\Cruds\Squema\InputGroup;

use App\Components\InputGroups\LabelInputGroup;
use App\Cruds\Concerns\IsCrud;
use App\Cruds\Contracts\Crud;
use App\Cruds\Squema\InputGroup\Inputs\EmailFactory;
use App\Cruds\Squema\InputGroup\Inputs\FavoriteAnimalFactory;
use App\Cruds\Squema\InputGroup\Inputs\LastNameFactory;
use App\Cruds\Squema\InputGroup\Inputs\NameFactory;
use BackedEnum;
use Juaniquillo\BackendComponents\Builders\ComponentBuilder;
use Juaniquillo\BackendComponents\Contracts\BackendComponent;
use Juaniquillo\BackendComponents\Contracts\ThemeManager;
use Juaniquillo\BackendComponents\Enums\ComponentEnum;
use Juaniquillo\BackendComponents\MainBackendComponent;
use Juaniquillo\CrudAssistant\CrudAssistant;
use Juaniquillo\CrudAssistant\InputCollection;
use Juaniquillo\InputComponentAction\Bags\DefaultComponentBag;
use Juaniquillo\InputComponentAction\Bags\DefaultThemeBag;
use Juaniquillo\InputComponentAction\Containers\InputComponentOutput;
use Juaniquillo\InputComponentAction\InputComponentAction;

class InputGroupCrud implements Crud
{
    use IsCrud;
    
    public const IDENTIFIER = 'input_group';
    public const NAME = 'Input Group WC';

	public static function make(): InputCollection
	{
		return CrudAssistant::make([
            NameFactory::make(),
            LastNameFactory::make(),
            EmailFactory::make(),
            FavoriteAnimalFactory::make(),
        ]);
	}

	public static function build(?array $values = null, ?array $errors = null): BackendComponent
	{
        $crud = self::make();

        $output = $crud->execute(
            action: (new InputComponentAction(
                $values ?? [],
                $errors ?? [],
            ))
            ->setDefaultInputGroup(LabelInputGroup::class)
            ->setDefaultComponentBag(
                (new DefaultComponentBag())
                    ->setWrapperComponent(function(string|BackedEnum $name, ThemeManager $themeManager){
                        return (new MainBackendComponent(name: 'form-group-web-component.main', themeManager: $themeManager))
                            ->useLocal();
                    })
            )
            ->setDefaultThemeBag(
                (new DefaultThemeBag())
                    ->setWrapperTheme([
                        'display' => 'block'
                    ])
                    ->setInputTheme(self::inputTheme())
                    ->setLabelTheme([
                        'display' => 'block'
                    ])
            )
        );

        /** @var InputComponentOutput $output */
        $inputs = $output->inputs;
        
		$form = ComponentBuilder::make(ComponentEnum::FORM)
            ->setContents($inputs->toArray())
            ->setAttribute('action', route('cruds.store', ['identifier' => self::IDENTIFIER, '#'.self::IDENTIFIER]))
            ->setAttribute('enctype', 'multipart/form-data')
            ->setThemes([
                'display' => 'grid',
                'grid' => 'gap-md'
            ])
            ->setContent(
                ComponentBuilder::make(ComponentEnum::BUTTON)
                    ->setContent('Send')
                    ->setAttribute('id', 'form_group_wc_button')
                    ->setTheme('action', 'default')
                    ->setTheme('color', 'light')
                    ->setTheme('padding', 'button')
                    ->setTheme('display', 'inline-block')
                    ->setTheme('margin', 'top-md')
            );

        return $form;
	}

    public  static function inputTheme(): array
    {
        return [
            'inputs' => [
                'ig_text',
                'ig_text_dark',
            ],
            'size' => 'w-full',
        ];
        
    }
}
