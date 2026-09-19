<?php

declare(strict_types=1);

namespace App\Cruds\Squema\Simple;

use App\Cruds\Concerns\IsCrud;
use App\Cruds\Contracts\Crud;
use App\Cruds\Squema\Simple\Inputs\AvatarFactory;
use App\Cruds\Squema\Simple\Inputs\CitiesFactory;
use App\Cruds\Squema\Simple\Inputs\ContactByFactory;
use App\Cruds\Squema\Simple\Inputs\LastNameFactory;
use App\Cruds\Squema\Simple\Inputs\NameFactory;
use App\Cruds\Themes\Inputs;
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
use Juaniquillo\InputComponentAction\Groups\InputLabelErrorGroup;
use Juaniquillo\InputComponentAction\InputComponentAction;

class SimpleCrud implements Crud
{
    use IsCrud;

    const NAME = 'Simple Themed Crud';

    const IDENTIFIER = 'simple_crud';

    public static function make(?string $group = null): InputCollection
    {
        return CrudAssistant::make([
            NameFactory::make($group),
            LastNameFactory::make($group),
            CitiesFactory::make($group),
            AvatarFactory::make($group),
            ContactByFactory::make($group),
        ]);
    }

    public static function build(?array $values = null, ?array $errors = null): BackendComponent
    {
        $crud = SimpleCrud::make();

        InputLabelErrorGroup::class;

        $output = $crud->execute(
            (new InputComponentAction(
                $values ?? [],
                $errors ?? [],
            ))
                ->setDefaultThemeBag(
                    (new DefaultThemeBag)
                        ->setWrapperTheme([
                            'margin' => 'top-sm',
                        ])
                        ->setInputTheme(Inputs::inputs())
                        ->setLabelTheme(Inputs::label())
                        ->setErrorTheme([
                            'color' => [
                                'error',
                                'error-dark',
                            ],
                            'padding' => 'top-xs',
                        ])
                )
                ->setDefaultComponentBag(
                    (new DefaultComponentBag)
                        ->setInputComponent(function (\BackedEnum|string $type, ThemeManager $themeManager) {
                            return new MainBackendComponent($type, $themeManager);
                        })
                )
        );

        /** @var InputComponentOutput $output */
        $inputs = $output->inputs;
        $meta = $output->meta;

        $form = ComponentBuilder::make(ComponentEnum::FORM)
            ->setContents($inputs->toArray())
            ->setAttribute('action', route('cruds.store', ['identifier' => self::IDENTIFIER, '#'.self::IDENTIFIER]))
            ->setAttribute('enctype', 'multipart/form-data')
            ->setContent(
                ComponentBuilder::make(ComponentEnum::BUTTON)
                    ->setContent('Send')
                    ->setTheme('action', 'default')
                    ->setTheme('color', 'light')
                    ->setTheme('padding', 'button')
                    ->setTheme('display', 'inline-block')
                    ->setTheme('margin', 'top-md')
            );

        return $form;
    }
}
