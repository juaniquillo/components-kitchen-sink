<?php

declare(strict_types=1);

namespace App\Builder\Registries;

use App\Builder\Support\ClassList;

/**
 * The themes the user has defined.
 *
 * A theme is a group of named variants, and a variant is a list of Tailwind
 * classes. The group is what the theme is called in the generated code and
 * in the theme file it belongs to, so two components can share a theme
 * without either of them carrying the classes.
 *
 * The registry never changes: every method that changes something returns a
 * new registry, the same way the tree mutator works.
 *
 * @phpstan-type Themes array<string, array<string, string>>
 */
final class ThemeRegistry
{
    /**
     * The shape a group or a variant name may take.
     *
     * The group becomes a file name, so it cannot hold a dot, a slash or a
     * space. The variant ends up inside a PHP array, so it is held to the
     * same rule.
     */
    private const NAME_PATTERN = '/^[A-Za-z0-9_-]+$/';

    /**
     * @param  array<string, array<string, string>>  $themes
     */
    public function __construct(private readonly array $themes = []) {}

    /**
     * The groups and the variants in them.
     *
     * @return array<string, array<string, string>>
     */
    public function groups(): array
    {
        return $this->themes;
    }

    /**
     * Every variant, flattened, for a picker.
     *
     * @return array<int, array{group: string, variant: string, classes: string}>
     */
    public function variants(): array
    {
        $variants = [];

        foreach ($this->themes as $group => $groupVariants) {
            foreach ($groupVariants as $variant => $classes) {
                $variants[] = [
                    'group' => $group,
                    'variant' => $variant,
                    'classes' => $classes,
                ];
            }
        }

        return $variants;
    }

    /**
     * The classes of a variant.
     */
    public function classes(string $group, string $variant): string
    {
        return $this->themes[$group][$variant] ?? '';
    }

    /**
     * Whether a variant exists.
     */
    public function has(string $group, string $variant): bool
    {
        return isset($this->themes[$group][$variant]);
    }

    /**
     * A registry with a variant added or changed.
     *
     * The classes are cleaned up on the way in, so a registry only ever holds
     * class names that may be used.
     */
    public function with(string $group, string $variant, string $classes): self
    {
        if (! self::allowsName($group) || ! self::allowsName($variant)) {
            return $this;
        }

        $themes = $this->themes;

        $themes[$group][$variant] = ClassList::sanitize($classes);

        return new self($themes);
    }

    /**
     * A registry without a variant.
     *
     * A group left without any variants is dropped, so the registry never
     * holds an empty group.
     */
    public function without(string $group, string $variant): self
    {
        if (! isset($this->themes[$group][$variant])) {
            return $this;
        }

        $themes = $this->themes;

        unset($themes[$group][$variant]);

        if ($themes[$group] === []) {
            unset($themes[$group]);
        }

        return new self($themes);
    }

    /**
     * Whether a group or a variant name may be used.
     */
    public static function allowsName(string $name): bool
    {
        return $name !== '' && preg_match(self::NAME_PATTERN, $name) === 1;
    }

    /**
     * Keep only the themes that may be used.
     *
     * A saved file is data, so it could have been written by hand. The names
     * and the classes are checked before anything reaches the builder.
     *
     * @return array<string, array<string, string>>
     */
    public static function sanitize(mixed $themes): array
    {
        if (! is_array($themes)) {
            return [];
        }

        $result = [];

        foreach ($themes as $group => $variants) {
            if (! is_string($group) || ! self::allowsName($group) || ! is_array($variants)) {
                continue;
            }

            foreach ($variants as $variant => $classes) {
                if (! is_string($variant) || ! self::allowsName($variant) || ! is_string($classes)) {
                    continue;
                }

                $classes = ClassList::sanitize($classes);

                if ($classes === '') {
                    continue;
                }

                $result[$group][$variant] = $classes;
            }
        }

        return $result;
    }
}
