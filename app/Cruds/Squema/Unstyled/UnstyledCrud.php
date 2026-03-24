<?php

namespace App\Cruds\Squema\Unstyled;


use App\Cruds\Concerns\IsCrud;
use App\Cruds\Contracts\Crud;
use App\Cruds\Squema\Unstyled\Inputs\EmailFactory;
use App\Cruds\Squema\Unstyled\Inputs\NameFactory;
use Juaniquillo\BackendComponents\Builders\ComponentBuilder;
use Juaniquillo\BackendComponents\Contracts\BackendComponent;
use Juaniquillo\BackendComponents\Enums\ComponentEnum;
use Juaniquillo\CrudAssistant\CrudAssistant;
use Juaniquillo\CrudAssistant\InputCollection;
use Juaniquillo\InputComponentAction\Bags\DefaultThemeBag;
use Juaniquillo\InputComponentAction\Containers\InputComponentOutput;
use Juaniquillo\InputComponentAction\InputComponentAction;

class UnstyledCrud implements Crud
{
    use IsCrud;

    public const IDENTIFIER = 'unstyled';
    public const NAME = 'Unstyled Crud';

    public static function make(?string $group = null): InputCollection
    {
        return CrudAssistant::make([
            NameFactory::make(),
            EmailFactory::make(),
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
            ->setDefaultThemeBag(
                (new DefaultThemeBag())
                    ->setInputTheme([
                        'color' => 'default',
                    ])
            )
        );

        /** @var InputComponentOutput $output */
        $inputs = $output->inputs;
       
        $form = ComponentBuilder::make(ComponentEnum::FORM)
            ->setContents($inputs->toArray())
            ->setAttribute('action', route('cruds.store', ['identifier' => self::IDENTIFIER, '#'.self::IDENTIFIER]))
            ->setAttribute('enctype', 'multipart/form-data')
            ->setContent(
                ComponentBuilder::make(ComponentEnum::BUTTON)
                    ->setContent('Send')
            );

        return $form;
    }
}
