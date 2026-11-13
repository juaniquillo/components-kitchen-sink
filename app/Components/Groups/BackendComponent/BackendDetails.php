<?php

declare(strict_types=1);

namespace App\Components\Groups\BackendComponent;

use App\Components\ContainerOptions;
use App\Components\Contracts\Component;
use Juaniquillo\BackendComponents\Enums\ComponentEnum;
use Juaniquillo\BackendComponents\Factories\ComponentFactory;
use Juaniquillo\BaseBackendComponent\Contracts\BackendComponent;

class BackendDetails implements Component
{
    const NAME = 'Backend Details';

    public static function list(): array
    {
        return [
            'Closed details' => self::closed(),
            'Open details' => self::open(),
            'Themed summary' => self::themed(),
        ];
    }

    public static function options(): ContainerOptions
    {
        return new ContainerOptions(
            flexColumn: true,
            flexGap: 'flex-gap',
            justifyCenter: false
        );
    }

    public static function closed(): BackendComponent
    {
        return self::details('What is this page?');
    }

    public static function open(): BackendComponent
    {
        return self::details('What is this page?')
            ->setAttribute('open', 'open');
    }

    public static function themed(): BackendComponent
    {
        return ComponentFactory::make(ComponentEnum::DETAILS)
            ->setContents([
                ComponentFactory::make(ComponentEnum::SUMMARY)
                    ->setContent('What is this page?')
                    ->setTheme('color', 'info'),
                ComponentFactory::make(ComponentEnum::PARAGRAPH)
                    ->setContent('A kitchen sink of components built with backend-component trees.'),
            ]);
    }

    private static function details(string $summary): BackendComponent
    {
        return ComponentFactory::make(ComponentEnum::DETAILS)
            ->setContents([
                ComponentFactory::make(ComponentEnum::SUMMARY)
                    ->setContent($summary),
                ComponentFactory::make(ComponentEnum::PARAGRAPH)
                    ->setContent('A kitchen sink of components built with backend-component trees.'),
            ]);
    }
}
