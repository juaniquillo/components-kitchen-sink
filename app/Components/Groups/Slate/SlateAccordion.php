<?php

declare(strict_types=1);

namespace App\Components\Groups\Slate;

use App\Components\ContainerOptions;
use App\Components\Contracts\Component;
use Juaniquillo\BackendComponents\Contracts\BackendComponent;
use Juaniquillo\SlateBackendComponents\Utils\SlateAccordionUtil;

class SlateAccordion implements Component
{
    const NAME = 'Accordion';

    public static function list(): array
    {
        return [
            self::simple(),
            self::withMultiple(),
            self::withDefaultValue(),
        ];
    }

    public static function options(): ContainerOptions
    {
        return new ContainerOptions;
    }

    public static function simple(): BackendComponent
    {
        return SlateAccordionUtil::make(
            items: [
                'getting-started' => [
                    'title' => 'Getting Started',
                    'content' => 'Install the package via composer and publish the config file.',
                ],
                'configuration' => [
                    'title' => 'Configuration',
                    'content' => 'Customize the theme, components, and assets in the config file.',
                ],
                'usage' => [
                    'title' => 'Usage',
                    'content' => 'Use the component builder to create accordions in your views.',
                ],
            ],
        )->getComponent();
    }

    public static function withMultiple(): BackendComponent
    {
        return SlateAccordionUtil::make(
            items: [
                'section-a' => [
                    'title' => 'Section A',
                    'content' => 'This section can be open at the same time as other sections.',
                ],
                'section-b' => [
                    'title' => 'Section B',
                    'content' => 'Multiple sections can be expanded simultaneously.',
                ],
                'section-c' => [
                    'title' => 'Section C',
                    'content' => 'Each section operates independently.',
                ],
            ],
            type: 'multiple',
        )->getComponent();
    }

    public static function withDefaultValue(): BackendComponent
    {
        return SlateAccordionUtil::make(
            items: [
                'faq-1' => [
                    'title' => 'What is this package?',
                    'content' => 'A component library for building UIs with Blade and PHP.',
                ],
                'faq-2' => [
                    'title' => 'Is it free?',
                    'content' => 'Yes, it is open source and free to use.',
                ],
                'faq-3' => [
                    'title' => 'How do I contribute?',
                    'content' => 'Open a pull request on GitHub.',
                ],
            ],
            defaultValue: 'faq-1',
        )->getComponent();
    }
}
