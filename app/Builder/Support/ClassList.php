<?php

declare(strict_types=1);

namespace App\Builder\Support;

/**
 * Decides which classes the builder accepts.
 *
 * A theme is a list of Tailwind class names the user typed, and it ends up
 * in a class attribute and in the generated PHP. The attribute is escaped on
 * the way out, so nothing here is about escaping; it is about keeping a class
 * list to what a class name can actually be.
 *
 * Tailwind class names are made of letters, digits and the separators they
 * use: a dash, a colon for a variant, a slash for an opacity, and brackets
 * for an arbitrary value. Arbitrary values bring a few more characters with
 * them, so those are allowed too, as long as they stay inside the brackets.
 */
final class ClassList
{
    /**
     * The shape a class name may take.
     */
    private const PATTERN = '/^[A-Za-z0-9_\-:.\/\[\]#%(),!&*+>~=]+$/';

    /**
     * Whether a single class name may be used.
     */
    public static function allows(string $class): bool
    {
        return $class !== '' && preg_match(self::PATTERN, $class) === 1;
    }

    /**
     * Keep only the class names that may be used.
     *
     * The names are separated by whitespace, so a list is split on it and
     * put back together with a single space.
     */
    public static function sanitize(string $classes): string
    {
        $kept = [];

        foreach (preg_split('/\s+/', trim($classes)) ?: [] as $class) {
            if (! self::allows($class)) {
                continue;
            }

            $kept[] = $class;
        }

        return implode(' ', $kept);
    }
}
