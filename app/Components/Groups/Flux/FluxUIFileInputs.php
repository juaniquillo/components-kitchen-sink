<?php

declare(strict_types=1);

namespace App\Components\Groups\Flux;

use App\Components\ContainerOptions;
use App\Components\Contracts\Component;
use Juaniquillo\BackendComponents\Contracts\BackendComponent;
use Juaniquillo\FluxBackendComponents\Builders\FluxLocalThemeComponentBuilder;
use Juaniquillo\FluxBackendComponents\FluxComponentEnum;

class FluxUIFileInputs implements Component
{
    const NAME = 'Flux UI Inputs';

    public static function list(): array
    {
        return [
            self::textInput(),
            self::emailInput(),
            self::passwordInput(),
            self::numberInput(),
            self::fileInput(),
            self::fileInputMultiple(),
            self::selectInput(),
            self::checkboxInput(),
            self::radioInput(),
            self::textareaInput(),
        ];
    }

    public static function options(): ContainerOptions
    {
        return new ContainerOptions(
            flexColumn: true,
        );
    }

    public static function textInput(): BackendComponent
    {
        return FluxLocalThemeComponentBuilder::make(FluxComponentEnum::TEXT_INPUT)
            ->setAttributes([
                'type' => 'text',
                'placeholder' => 'Enter your name',
            ]);
    }

    public static function emailInput(): BackendComponent
    {
        return FluxLocalThemeComponentBuilder::make(FluxComponentEnum::TEXT_INPUT)
            ->setAttributes([
                'type' => 'email',
                'placeholder' => 'you@example.com',
            ]);
    }

    public static function passwordInput(): BackendComponent
    {
        return FluxLocalThemeComponentBuilder::make(FluxComponentEnum::TEXT_INPUT)
            ->setAttributes([
                'type' => 'password',
                'placeholder' => 'Enter password',
            ]);
    }

    public static function numberInput(): BackendComponent
    {
        return FluxLocalThemeComponentBuilder::make(FluxComponentEnum::TEXT_INPUT)
            ->setAttributes([
                'type' => 'number',
                'placeholder' => '0',
            ]);
    }

    public static function fileInput(): BackendComponent
    {
        return FluxLocalThemeComponentBuilder::make(FluxComponentEnum::TEXT_FILE)
            ->setAttribute('name', 'avatar');
    }

    public static function fileInputMultiple(): BackendComponent
    {
        return FluxLocalThemeComponentBuilder::make(FluxComponentEnum::TEXT_FILE)
            ->setAttributes([
                'name' => 'documents',
                'multiple' => 1,
            ]);
    }

    public static function selectInput(): BackendComponent
    {
        return FluxLocalThemeComponentBuilder::make(FluxComponentEnum::SELECT)
            ->setContents([
                FluxLocalThemeComponentBuilder::make(FluxComponentEnum::OPTION)
                    ->setAttribute('value', '')
                    ->setContent('Select a role'),
                FluxLocalThemeComponentBuilder::make(FluxComponentEnum::OPTION)
                    ->setAttribute('value', 'admin')
                    ->setContent('Admin'),
                FluxLocalThemeComponentBuilder::make(FluxComponentEnum::OPTION)
                    ->setAttribute('value', 'editor')
                    ->setContent('Editor'),
                FluxLocalThemeComponentBuilder::make(FluxComponentEnum::OPTION)
                    ->setAttribute('value', 'viewer')
                    ->setContent('Viewer'),
            ]);
    }

    public static function checkboxInput(): BackendComponent
    {
        return FluxLocalThemeComponentBuilder::make(FluxComponentEnum::CHECKBOX)
            ->setAttributes([
                'label' => 'Accept terms and conditions',
            ]);
    }

    public static function radioInput(): BackendComponent
    {
        return FluxLocalThemeComponentBuilder::make(FluxComponentEnum::RADIO)
            ->setAttributes([
                'label' => 'Option A',
                'value' => 'a',
            ]);
    }

    public static function textareaInput(): BackendComponent
    {
        return FluxLocalThemeComponentBuilder::make(FluxComponentEnum::TEXTAREA)
            ->setAttributes([
                'placeholder' => 'Write your message here...',
                'rows' => 4,
            ]);
    }
}
