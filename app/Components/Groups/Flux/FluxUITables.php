<?php

declare(strict_types=1);

namespace App\Components\Groups\Flux;

use App\Components\ContainerOptions;
use App\Components\Contracts\Component;
use Juaniquillo\BackendComponents\Builders\LocalThemeComponentBuilder;
use Juaniquillo\BackendComponents\Contracts\BackendComponent;
use Juaniquillo\BackendComponents\Enums\ComponentEnum;
use Juaniquillo\BackendComponents\Utils\CellBag;
use Juaniquillo\FluxBackendComponents\Builders\FluxComponentBuilder;
use Juaniquillo\FluxBackendComponents\Builders\FluxLocalThemeComponentBuilder;
use Juaniquillo\FluxBackendComponents\FluxComponentEnum;
use Juaniquillo\FluxBackendComponents\Utils\FluxUITableUtil;

class FluxUITables implements Component
{
    const NAME = 'Flux UI Tables';

    public static function list(): array
    {
        return [
            self::container([
                self::basic(),
            ]),
            self::container([
                self::striped(),
            ]),
            self::container([
                self::compact(),
            ]),
            self::container([
                self::withActions(),
            ]),
            self::container([
                self::strongCells(),
            ]),
            self::container([
                self::aligned(),
            ]),
            self::container([
                self::hover(),
            ]),
        ];
    }

    public static function options(): ContainerOptions
    {
        return new ContainerOptions(
            disableFlex: true,
        );
    }

    /** @param array<int|string, BackendComponent> $contents */
    public static function container(array $contents): BackendComponent
    {
        return LocalThemeComponentBuilder::make(ComponentEnum::DIV)
            ->setContents($contents)
            ->setTheme('table', 'container');
    }

    public  static function badge(string $content, string $color = 'lime'): BackendComponent
    {
        return FluxComponentBuilder::make(FluxComponentEnum::BADGE)
            ->setContent($content)
            ->setAttribute('color', $color)
            ->setAttribute('size', 'sm');
    }

    public static function basic(): BackendComponent
    {
        return FluxUITableUtil::make(
            head: ['Name', 'Email', 'Role'],
            body: [
                ['Alice', 'alice@example.com', 'Admin'],
                ['Bob', 'bob@example.com', 'Editor'],
                ['Charlie', 'charlie@example.com', 'Viewer'],
            ],
        )->getComponent();
    }

    public static function striped(): BackendComponent
    {
        return FluxUITableUtil::make(
            head: ['Name', 'Email', 'Role', 'City', 'Approved'],
            body: [
                ['Alice', 'alice@example.com', 'Admin', 'New York', self::badge('Yes')],
                ['Bob', 'bob@example.com', 'Editor', 'New Jersey', self::badge('Yes')],
                ['Charlie', 'charlie@example.com', 'Viewer', 'Miami', self::badge('No', 'rose')],
                ['Diana', 'diana@example.com', 'Admin', 'California', self::badge('Yes')],
                ['Eve', 'eve@example.com', 'Viewer', 'New York', self::badge('No', 'rose')],
            ],
        )
            ->setTrThemes(['table' => ['striped']])
            ->setTableAttributes(['bleed' => 'bleed'])
            ->getComponent();
    }

    public static function compact(): BackendComponent
    {
        return FluxUITableUtil::make(
            head: ['Name', 'Email', 'Role'],
            body: [
                ['Alice', 'alice@example.com', 'Admin'],
                ['Bob', 'bob@example.com', 'Editor'],
                ['Charlie', 'charlie@example.com', 'Viewer'],
            ],
        )
            ->setThThemes(['table' => ['compact-th', 'th', 'th-dark']])
            ->setTdThemes(['table' => ['compact', 'td', 'td-dark']])
            ->getComponent();
    }

    public static function withActions(): BackendComponent
    {
        $editButton = FluxLocalThemeComponentBuilder::make(FluxComponentEnum::BUTTON)
            ->setAttribute('variant', 'ghost')
            ->setAttribute('size', 'sm')
            ->setAttribute('type', 'button')
            ->setContent('Edit');

        $deleteButton = FluxLocalThemeComponentBuilder::make(FluxComponentEnum::BUTTON)
            ->setAttribute('variant', 'danger')
            ->setAttribute('size', 'sm')
            ->setAttribute('type', 'button')
            ->setContent('Delete');

        return FluxUITableUtil::make(
            head: ['Name', 'Email', 'Actions'],
            body: [
                [
                    'Alice',
                    'alice@example.com',
                    new CellBag(
                        content: LocalThemeComponentBuilder::make(ComponentEnum::COLLECTION)
                            ->setContents([$editButton, $deleteButton]),
                    ),
                ],
                [
                    'Bob',
                    'bob@example.com',
                    new CellBag(
                        content: LocalThemeComponentBuilder::make(ComponentEnum::COLLECTION)
                            ->setContents([$editButton, $deleteButton])
                    ),
                ],
            ],
        )->getComponent();
    }

    public static function strongCells(): BackendComponent
    {
        return FluxUITableUtil::make(
            head: ['Name', 'Email', 'Role'],
            body: [
                [
                    new CellBag(content: 'Alice', theme: ['table' => ['td-strong', 'td', 'td-dark']]),
                    'alice@example.com',
                    new CellBag(content: 'Admin', theme: ['table' => ['td-strong', 'td', 'td-dark']]),
                ],
                ['Bob', 'bob@example.com', 'Editor'],
                ['Charlie', 'charlie@example.com', 'Viewer'],
            ],
        )->getComponent();
    }

    public static function aligned(): BackendComponent
    {
        return FluxUITableUtil::make(
            head: [
                new CellBag(content: 'Name', attributes: ['class' => 'text-start']),
                new CellBag(content: 'Email', attributes: ['class' => 'text-center']),
                new CellBag(content: 'Role', attributes: ['class' => 'text-end']),
            ],
            body: [
                [
                    new CellBag(content: 'Alice', attributes: ['class' => 'text-start']),
                    new CellBag(content: 'alice@example.com', attributes: ['class' => 'text-center']),
                    new CellBag(content: 'Admin', attributes: ['class' => 'text-end']),
                ],
                [
                    new CellBag(content: 'Bob', attributes: ['class' => 'text-start']),
                    new CellBag(content: 'bob@example.com', attributes: ['class' => 'text-center']),
                    new CellBag(content: 'Editor', attributes: ['class' => 'text-end']),
                ],
            ],
        )->getComponent();
    }

    public static function hover(): BackendComponent
    {
        return FluxUITableUtil::make(
            head: ['Name', 'Email', 'Role'],
            body: [
                ['Alice', 'alice@example.com', 'Admin'],
                ['Bob', 'bob@example.com', 'Editor'],
                ['Charlie', 'charlie@example.com', 'Viewer'],
            ],
        )
            ->setTrThemes(['table' => ['hover']])
            ->getComponent();
    }
}
