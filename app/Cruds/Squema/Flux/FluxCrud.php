<?php

namespace App\Cruds\Squema\Flux;

use App\Components\Builders\FluxComponentBuilder;
use App\Cruds\Contracts\Crud;
use Juaniquillo\BackendComponents\Builders\ComponentBuilder;
use Juaniquillo\BackendComponents\Contracts\BackendComponent;
use Juaniquillo\BackendComponents\Enums\ComponentEnum;
use Juaniquillo\CrudAssistant\CrudAssistant;
use Juaniquillo\CrudAssistant\InputCollection;
use Juaniquillo\InputComponentAction\Containers\InputComponentOutput;
use Juaniquillo\InputComponentAction\InputComponentAction;

class FluxCrud implements Crud
{
    public const IDENTIFIER = 'flux';
    public const NAME = 'Flux Crud';

    public static function make(?array $values = null, ?array $errors = null): InputCollection
    {
        return CrudAssistant::make([
            
        ]);
    }

    public static function build(?array $values = null, ?array $errors = null): BackendComponent
    {
        $crud  = CrudAssistant::make();

        $output = $crud->execute(
            (new InputComponentAction(
                $values ?? [],
                $errors ?? [],
            ))
        );

        /** @var InputComponentOutput $output */
        $inputs = $output->inputs;
        $meta = $output->meta;

        return ComponentBuilder::make(ComponentEnum::FORM)
            ->setContents($inputs->toArray())
            ->setAttribute('action', route('cruds.store', ['identifier' => self::IDENTIFIER, '#'.self::IDENTIFIER]))
            ->setAttribute('enctype', 'multipart/form-data')
            ->setContent(
                FluxComponentBuilder::make('button')
                    ->setContent('Send')
                    ->setAttribute('type', 'submit')
            );
    }
}
