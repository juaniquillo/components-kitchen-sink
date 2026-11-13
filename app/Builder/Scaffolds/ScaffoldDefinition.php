<?php

declare(strict_types=1);

namespace App\Builder\Scaffolds;

use App\Livewire\Builder\NoticeSurface;
use Closure;

/**
 * One compound a node menu or the palette offers to scaffold in one go.
 *
 * A table is not assembled part by part, and neither is a details with its
 * summary: the form asks what cannot be clicked together, then builds the
 * whole compound at once. Everything a scaffold needs to be offered and
 * run lives here, so a new one is a single registry entry.
 */
final class ScaffoldDefinition
{
    /**
     * @param  array<int, ScaffoldField>  $fields
     * @param  Closure(array, string, array, callable(string): string): array  $build  ($tree, $parentId, $values, $idFactory) => $tree
     */
    public function __construct(
        public readonly string $key,
        public readonly string $rootType,
        public readonly NoticeSurface $notice,
        public readonly string $menuLabel,
        public readonly string $menuTitle,
        public readonly string $drawerTitle,
        public readonly string $submitLabel,
        public readonly string $blurb,
        public readonly string $hint,
        public readonly string $refusalMessage,
        public readonly ?string $paletteGroup,
        public readonly string $paletteLabel,
        public readonly string $paletteTitle,
        public readonly array $fields,
        public readonly Closure $build,
    ) {}

    /**
     * The values a fresh form opens with, keyed by field name.
     *
     * @return array<string, mixed>
     */
    public function defaults(): array
    {
        $defaults = [];

        foreach ($this->fields as $field) {
            $defaults[$field->name] = $field->default;
        }

        return $defaults;
    }

    /**
     * Coerce raw form values to the shape the build closure takes.
     *
     * @param  array<string, mixed>  $values
     * @return array<string, mixed>
     */
    public function normalize(array $values): array
    {
        $normalized = [];

        foreach ($this->fields as $field) {
            $normalized[$field->name] = $field->normalize($values[$field->name] ?? $field->default);
        }

        return $normalized;
    }
}
