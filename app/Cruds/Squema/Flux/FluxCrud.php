<?php

namespace App\Cruds\Squema\Flux;

use App\Components\Builders\FluxComponentBuilder;
use App\Components\ThirdParty\Flux\FluxBackendComponent;
use App\Cruds\Contracts\Crud;
use App\Cruds\Squema\Flux\Inputs\FluxEmailFactory;
use App\Cruds\Squema\Flux\Inputs\FluxNameFactory;
use App\Cruds\Squema\Flux\Inputs\FluxYearFactory;
use BackedEnum;
use Juaniquillo\BackendComponents\Builders\ComponentBuilder;
use Juaniquillo\BackendComponents\Contracts\BackendComponent;
use Juaniquillo\BackendComponents\Contracts\ThemeManager;
use Juaniquillo\BackendComponents\Enums\ComponentEnum;
use Juaniquillo\CrudAssistant\CrudAssistant;
use Juaniquillo\CrudAssistant\InputCollection;
use Juaniquillo\InputComponentAction\Bags\DefaultComponentBag;
use Juaniquillo\InputComponentAction\Containers\InputComponentOutput;
use Juaniquillo\InputComponentAction\Groups\NoWrapSoleInputGroup;
use Juaniquillo\InputComponentAction\InputComponentAction;

class FluxCrud implements Crud
{
    public const IDENTIFIER = 'flux';
    public const NAME = 'Flux Crud';

    public static function make(?array $values = null, ?array $errors = null): InputCollection
    {
        return CrudAssistant::make([
            FluxNameFactory::make(),
            FluxEmailFactory::make(),
            FluxYearFactory::make(),
        ]);
    }

    public static function build(?array $values = null, ?array $errors = null): BackendComponent
    {
        $crud = self::make();

        $output = $crud->execute(
            (new InputComponentAction(
                $values ?? [],
                $errors ?? [],
            ))
            ->setDefaultInputGroup(new NoWrapSoleInputGroup)
            ->setDefaultComponentBag(
                (new DefaultComponentBag())
                    // Input
                    ->setInputType('input')
                    ->setInputComponent(
                        function(string|BackedEnum $type, ThemeManager $manager) {
                            return new FluxBackendComponent($type, $manager);
                        }
                    )
                    
            )
        );

        /** @var InputComponentOutput $output */
        $inputs = $output->inputs;
        $meta = $output->meta;

        return ComponentBuilder::make(ComponentEnum::FORM)
            ->setContents($inputs->toArray())
            ->setAttribute('action', route('cruds.store', ['identifier' => self::IDENTIFIER, '#'.self::IDENTIFIER]))
            ->setAttribute('enctype', 'multipart/form-data')
            ->setThemes([
                'display' => 'grid',
                'grid' => [
                    'gap-md'
                ]
            ])
            ->setContent(
                ComponentBuilder::make(ComponentEnum::DIV)
                    ->setContent(
                        FluxComponentBuilder::make('button')
                        ->setContent('Send')
                        ->setAttribute('type', 'submit')
                        ->setAttribute('variant', 'primary')
                        ->setAttribute('color', 'blue')
                    )
            );
    }
}
