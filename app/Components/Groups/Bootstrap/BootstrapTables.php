<?php

declare(strict_types=1);

namespace App\Components\Groups\Bootstrap;

use App\Components\ContainerOptions;
use App\Components\Contracts\Component;
use Juaniquillo\BackendComponents\Enums\ComponentEnum;
use Juaniquillo\BackendComponents\Factories\ComponentFactory;
use Juaniquillo\BackendComponents\Utils\CellBag;
use Juaniquillo\BaseBackendComponent\Contracts\BackendComponent;
use Juaniquillo\BaseBackendComponent\Contracts\CompoundComponent;

class BootstrapTables implements Component
{
    const NAME = 'Bootstrap Tables';

    public static function list(): array
    {
        return [
            self::container([self::basic()]),
            self::container([self::striped()]),
            self::container([self::hover()]),
            self::container([self::compact()]),
            self::container([self::withCaption()]),
            self::container([self::withActions()]),
            self::container([self::darkHeader()]),
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
        return ComponentFactory::make(ComponentEnum::DIV)
            ->setContents($contents);
    }

    public static function basic(): CompoundComponent
    {
        return self::buildTable(
            class: 'table',
            head: ['Name', 'Email', 'Role'],
            body: [
                ['Alice', 'alice@example.com', 'Admin'],
                ['Bob', 'bob@example.com', 'Editor'],
                ['Charlie', 'charlie@example.com', 'Viewer'],
            ],
        );
    }

    public static function striped(): CompoundComponent
    {
        return self::buildTable(
            class: 'table table-striped',
            head: ['Name', 'Email', 'Role'],
            body: [
                ['Alice', 'alice@example.com', 'Admin'],
                ['Bob', 'bob@example.com', 'Editor'],
                ['Charlie', 'charlie@example.com', 'Viewer'],
                ['Diana', 'diana@example.com', 'Admin'],
            ],
        );
    }

    public static function hover(): CompoundComponent
    {
        return self::buildTable(
            class: 'table table-hover',
            head: ['Name', 'Email', 'Role'],
            body: [
                ['Alice', 'alice@example.com', 'Admin'],
                ['Bob', 'bob@example.com', 'Editor'],
                ['Charlie', 'charlie@example.com', 'Viewer'],
            ],
        );
    }

    public static function compact(): CompoundComponent
    {
        return self::buildTable(
            class: 'table table-sm',
            head: ['Name', 'Email', 'Role'],
            body: [
                ['Alice', 'alice@example.com', 'Admin'],
                ['Bob', 'bob@example.com', 'Editor'],
                ['Charlie', 'charlie@example.com', 'Viewer'],
            ],
        );
    }

    public static function withCaption(): CompoundComponent
    {
        return self::buildTable(
            class: 'table',
            head: ['Name', 'Email', 'Role'],
            body: [
                ['Alice', 'alice@example.com', 'Admin'],
                ['Bob', 'bob@example.com', 'Editor'],
                ['Charlie', 'charlie@example.com', 'Viewer'],
            ],
            caption: new CellBag(
                content: 'List of team members',
                attributes: [
                    'class' => 'text-secondary bg-light px-2 fw-bold',
                ]
            ),
        );
    }

    public static function withActions(): CompoundComponent
    {
        $editButton = ComponentFactory::make(ComponentEnum::BUTTON)
            ->setContent('Edit')
            ->setAttributes([
                'class' => 'btn btn-sm btn-outline-primary',
                'type' => 'button',
            ]);

        $deleteButton = ComponentFactory::make(ComponentEnum::BUTTON)
            ->setContent('Delete')
            ->setAttributes([
                'class' => 'btn btn-sm btn-outline-danger',
                'type' => 'button',
            ]);

        return self::buildTable(
            class: 'table',
            head: ['Name', 'Email', 'Actions'],
            body: [
                [
                    'Alice',
                    'alice@example.com',
                    ComponentFactory::make(ComponentEnum::DIV)
                        ->setAttribute('class', 'd-flex gap-1')
                        ->setContents([$editButton, $deleteButton]),
                ],
                [
                    'Bob',
                    'bob@example.com',
                    ComponentFactory::make(ComponentEnum::DIV)
                        ->setAttribute('class', 'd-flex gap-1')
                        ->setContents([$editButton, $deleteButton]),
                ],
            ],
        );
    }

    public static function darkHeader(): CompoundComponent
    {
        return self::buildTable(
            class: 'table table-dark',
            head: ['Name', 'Email', 'Role'],
            body: [
                ['Alice', 'alice@example.com', 'Admin'],
                ['Bob', 'bob@example.com', 'Editor'],
                ['Charlie', 'charlie@example.com', 'Viewer'],
            ],
        );
    }

    /**
     * @param  array<string|int, string|CompoundComponent|CellBag|array{...}>  $head
     * @param  array<string|int, array<string|int, string|CompoundComponent|CellBag|array{...}>>  $body
     */
    private static function buildTable(
        string $class,
        array $head,
        array $body,
        string|CompoundComponent|CellBag|null $caption = null,
    ): CompoundComponent {
        $tableContents = [];

        if ($caption !== null) {
            $captionContent = self::resolveContent($caption);
            $captionAttrs = self::resolveAttributes($caption);
            $tableContents[] = ComponentFactory::make(ComponentEnum::CAPTION)
                ->setContent($captionContent)
                ->setAttributes($captionAttrs);
        }

        $thCells = [];
        foreach ($head as $value) {
            $content = is_array($value) ? ($value['content'] ?? '') : $value;
            $attrs = is_array($value) ? ($value['attributes'] ?? []) : [];
            $thCells[] = ComponentFactory::make(ComponentEnum::TH)
                ->setContent($content)
                ->setAttributes($attrs);
        }

        $tableContents[] = ComponentFactory::make(ComponentEnum::THEAD)
            ->setContents([
                ComponentFactory::make(ComponentEnum::TR)
                    ->setContents($thCells),
            ]);

        $rows = [];
        foreach ($body as $row) {
            $tdCells = [];
            foreach ($row as $value) {
                $content = self::resolveContent($value);
                $attrs = self::resolveAttributes($value);
                $tdCells[] = ComponentFactory::make(ComponentEnum::TD)
                    ->setContent($content)
                    ->setAttributes($attrs);
            }
            $rows[] = ComponentFactory::make(ComponentEnum::TR)
                ->setContents($tdCells);
        }

        $tableContents[] = ComponentFactory::make(ComponentEnum::TBODY)
            ->setContents($rows);

        return ComponentFactory::make(ComponentEnum::TABLE)
            ->setAttribute('class', $class)
            ->setContents($tableContents);
    }

    private static function resolveContent(string|CellBag|CompoundComponent $value): string|CompoundComponent
    {
        if ($value instanceof CellBag) {
            return $value->content;
        }

        return $value;
    }

    private static function resolveAttributes(string|CellBag|CompoundComponent $value): array
    {
        if ($value instanceof CellBag) {
            return $value->attributes ?? [];
        }

        return [];
    }
}
