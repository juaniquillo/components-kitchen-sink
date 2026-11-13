<?php

declare(strict_types=1);

namespace App\Components\Groups\BackendComponent;

use App\Components\ContainerOptions;
use App\Components\Contracts\Component;
use Juaniquillo\BackendComponents\Enums\ComponentEnum;
use Juaniquillo\BackendComponents\Factories\ComponentFactory;
use Juaniquillo\BackendComponents\Themes\LocalThemeManager;
use Juaniquillo\BackendComponents\Utils\CellBag;
use Juaniquillo\BackendComponents\Utils\TableUtil;
use Juaniquillo\BaseBackendComponent\Contracts\BackendComponent;

class BackendTables implements Component
{
    const NAME = 'Backend Tables';

    public static function list(): array
    {
        return [
            'Plain table' => self::plain(),
            'Themed cells' => self::cells(),
        ];
    }

    public static function options(): ContainerOptions
    {
        return new ContainerOptions;
    }

    public static function plain(): BackendComponent
    {
        return TableUtil::make(
            head: ['Name', 'Email', 'Role'],
            body: [
                ['Alice', 'alice@example.com', 'Admin'],
                ['Bob', 'bob@example.com', 'Editor'],
            ],
            themeManager: new LocalThemeManager,
        )->getComponent();
    }

    public static function cells(): BackendComponent
    {
        return TableUtil::make(
            head: ['Name', 'Email', 'Role'],
            body: [
                ['Alice', 'alice@example.com', 'Admin'],
                [
                    new CellBag(content: 'Bob', theme: ['color' => ['success', 'success-dark']]),
                    'bob@example.com',
                    new CellBag(
                        content: ComponentFactory::make(ComponentEnum::BOLD)
                            ->setContent('Editor')
                            ->setTheme('color', ['info', 'info-dark']),
                    ),
                ],
                [
                    new CellBag(
                        content: 'Two users share the editor role.',
                        attributes: ['colspan' => 3],
                    ),
                ],
            ],
            themeManager: new LocalThemeManager,
        )
            ->setCaption('Team members and their roles.')
            ->getComponent();
    }
}
