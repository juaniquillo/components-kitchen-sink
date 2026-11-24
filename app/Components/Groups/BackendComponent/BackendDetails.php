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
            'Named details' => self::named(),
            'Themed summary' => self::themed(),
        ];
    }

    public static function options(): ContainerOptions
    {
        return new ContainerOptions(
            flexColumn: true,
            flexGap: 'flex-gap',
            justify: 'start',
            items: 'stretch',
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
                    ->setTheme('padding', [
                        'x-sm',
                        'y-xs',
                    ])
                    ->setTheme('background', 'default')
                    ->setTheme('border-radius', 'sm')
                    ->setTheme('color', 'light'),
                ComponentFactory::make(ComponentEnum::PARAGRAPH)
                    ->setTheme('padding', 'xs')
                    ->setContent('A kitchen sink of components built with backend-component trees.'),
            ]);
    }

    public static function named(?string $content = null): BackendComponent
    {
        $fallback = 'A kitchen sink of components built with backend-component trees.';

        return ComponentFactory::make(ComponentEnum::DIV)
            ->setTheme('background', 'secondary')
            ->setTheme('color', 'light')
            ->setTheme('border-radius', 'sm')
            ->setTheme('padding', [
                'x-sm',
                'y-xs',
            ])
            ->setContents([
                ComponentFactory::make(ComponentEnum::DETAILS)
                    ->setAttribute('name', 'details')
                    ->setTheme('border', 'bottom-sm')
                    ->setTheme('padding', 'xs')
                    ->setContents([
                        ComponentFactory::make(ComponentEnum::SUMMARY)
                            ->setTheme('cursor', 'pointer')
                            ->setContent('What is this page?'),
                        ComponentFactory::make(ComponentEnum::PARAGRAPH)
                            ->setTheme('padding', 'xs')
                            ->setContent($content ?? $fallback),
                    ]),
                ComponentFactory::make(ComponentEnum::DETAILS)
                    ->setAttribute('name', 'details')
                    ->setTheme('border', 'bottom-sm')
                    ->setTheme('padding', 'xs')
                    ->setContents([
                        ComponentFactory::make(ComponentEnum::SUMMARY)
                            ->setTheme('cursor', 'pointer')
                            ->setContent('What is this page 2?'),
                        ComponentFactory::make(ComponentEnum::PARAGRAPH)
                            ->setTheme('padding', 'xs')
                            ->setContent($content ?? $fallback),
                    ]),
                ComponentFactory::make(ComponentEnum::DETAILS)
                    ->setAttribute('name', 'details')
                    // ->setTheme('border', 'bottom-sm')
                    ->setTheme('padding', 'xs')
                    ->setContents([
                        ComponentFactory::make(ComponentEnum::SUMMARY)
                            ->setTheme('cursor', 'pointer')
                            ->setContent('What is this page 3?'),
                        ComponentFactory::make(ComponentEnum::PARAGRAPH)
                            ->setTheme('padding', 'xs')
                            ->setContent($content ?? $fallback),
                    ]),
            ]);
    }

    private static function details(string $summary, ?string $content = null): BackendComponent
    {
        return ComponentFactory::make(ComponentEnum::DETAILS)
            ->setContents([
                ComponentFactory::make(ComponentEnum::SUMMARY)
                    ->setContent($summary),
                ComponentFactory::make(ComponentEnum::PARAGRAPH)
                    ->setContent($content ?? 'A kitchen sink of components built with backend-component trees.'),
            ]);
    }
}
