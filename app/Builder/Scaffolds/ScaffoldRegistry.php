<?php

declare(strict_types=1);

namespace App\Builder\Scaffolds;

use App\Builder\Registries\ComponentRegistry;
use App\Builder\Support\TreeMutator;
use App\Livewire\Builder\NoticeSurface;

/**
 * The compounds a node menu or the palette offers to scaffold in one go.
 *
 * Everything a scaffold needs lives in its definition, so a new compound
 * is a single entry here: the canvas, the drawer panel, the palette and
 * the node menu all read from this registry and need no changes.
 */
final class ScaffoldRegistry
{
    /** @var array<string, ScaffoldDefinition>|null */
    private static ?array $definitions = null;

    /**
     * @return array<string, ScaffoldDefinition>
     */
    public static function all(): array
    {
        if (self::$definitions !== null) {
            return self::$definitions;
        }

        $table = new ScaffoldDefinition(
            key: 'table',
            rootType: 'table.table',
            notice: NoticeSurface::TABLE,
            menuLabel: 'Add table…',
            menuTitle: 'Add a table with the rows and columns you choose',
            drawerTitle: 'Add table',
            submitLabel: 'Add table',
            blurb: 'A skeleton with a head row and the body rows goes under the node',
            hint: 'Every cell holds a dash until something is typed into it.',
            refusalMessage: 'This node does not take a table.',
            paletteGroup: ComponentRegistry::GROUP_TABLE,
            paletteLabel: 'Add table…',
            paletteTitle: 'Add a table with the rows and columns you choose',
            fields: [
                new ScaffoldField(name: 'columns', label: 'Columns', type: ScaffoldFieldType::NUMBER, default: 3, min: 1, max: 99),
                new ScaffoldField(name: 'rows', label: 'Rows', type: ScaffoldFieldType::NUMBER, default: 2, min: 1, max: 99),
            ],
            build: fn (array $tree, string $parentId, array $values, callable $ids): array => TreeMutator::scaffoldTable($tree, $parentId, $values['columns'], $values['rows'], $ids),
        );

        $details = new ScaffoldDefinition(
            key: 'details',
            rootType: 'details.details',
            notice: NoticeSurface::DETAILS,
            menuLabel: 'Add details…',
            menuTitle: 'Add a details with the summary you choose',
            drawerTitle: 'Add details',
            submitLabel: 'Add details',
            blurb: 'A details with its summary goes under the node',
            hint: 'The summary names what the details holds; leave it empty to name it later.',
            refusalMessage: 'This node does not take a details.',
            paletteGroup: ComponentRegistry::GROUP_LAYOUT,
            paletteLabel: 'Add details…',
            paletteTitle: 'Add a details with the summary you choose',
            fields: [
                new ScaffoldField(name: 'summary', label: 'Summary', type: ScaffoldFieldType::TEXT, default: '', placeholder: 'Summary'),
                new ScaffoldField(name: 'open', label: 'Start open', type: ScaffoldFieldType::CHECKBOX, default: false),
            ],
            build: fn (array $tree, string $parentId, array $values, callable $ids): array => TreeMutator::scaffoldDetails($tree, $parentId, $values['summary'], $values['open'], $ids),
        );

        return self::$definitions = [
            $table->key => $table,
            $details->key => $details,
        ];
    }

    public static function get(string $key): ?ScaffoldDefinition
    {
        return self::all()[$key] ?? null;
    }

    /**
     * The scaffolds the palette offers in a group, in registry order.
     *
     * @return array<int, ScaffoldDefinition>
     */
    public static function forPaletteGroup(string $group): array
    {
        return array_values(array_filter(
            self::all(),
            fn (ScaffoldDefinition $definition): bool => $definition->paletteGroup === $group,
        ));
    }

    /**
     * The scaffolds a node menu offers, in registry order.
     *
     * @param  array<int, string>  $allowed  The types the node may hold.
     * @return array<int, ScaffoldDefinition>
     */
    public static function forAllowedChildren(array $allowed): array
    {
        return array_values(array_filter(
            self::all(),
            fn (ScaffoldDefinition $definition): bool => in_array($definition->rootType, $allowed, true),
        ));
    }
}
