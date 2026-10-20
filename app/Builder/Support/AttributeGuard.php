<?php

declare(strict_types=1);

namespace App\Builder\Support;

/**
 * Decides which attribute names the builder accepts.
 *
 * The builder lets the user name attributes freely, but the name ends up
 * inside a Livewire expression and inside the generated HTML, so it cannot be
 * taken on trust. Three rules apply:
 *
 *  - the name has to look like an HTML attribute name, which keeps quotes and
 *    brackets out of the expressions the name is interpolated into;
 *  - a handful of names are refused outright;
 *  - a few prefixes are refused, because they would turn a saved component
 *    into something that runs code.
 */
final class AttributeGuard
{
    /**
     * Names that are never allowed.
     *
     * @var array<int, string>
     */
    private const FORBIDDEN = [
        'style',
        'data-node-id',
        'data-selected',
    ];

    /**
     * Prefixes that are never allowed.
     *
     * `on` covers the event handlers, which would be a live cross site
     * scripting hole. `wire:` would let a saved component invoke any public
     * method on the builder. `x-` is Alpine's directive syntax.
     *
     * @var array<int, string>
     */
    private const FORBIDDEN_PREFIXES = [
        'on',
        'wire:',
        'x-',
        '@',
        ':',
    ];

    /**
     * The shape an HTML attribute name may take.
     */
    private const PATTERN = '/^[a-zA-Z_:][a-zA-Z0-9_:.-]*$/';

    /**
     * Whether an attribute name may be used.
     */
    public static function allows(string $name): bool
    {
        if ($name === '' || strlen($name) > 100) {
            return false;
        }

        if (! preg_match(self::PATTERN, $name)) {
            return false;
        }

        $lower = strtolower($name);

        if (in_array($lower, self::FORBIDDEN, true)) {
            return false;
        }

        foreach (self::FORBIDDEN_PREFIXES as $prefix) {
            if (str_starts_with($lower, $prefix)) {
                return false;
            }
        }

        return true;
    }

    /**
     * The reason an attribute name was refused, for the user.
     */
    public static function reason(string $name): ?string
    {
        if ($name === '') {
            return 'An attribute needs a name.';
        }

        if (! preg_match(self::PATTERN, $name)) {
            return 'That is not a usable attribute name.';
        }

        $lower = strtolower($name);

        if (in_array($lower, self::FORBIDDEN, true)) {
            return 'That attribute is not allowed.';
        }

        foreach (self::FORBIDDEN_PREFIXES as $prefix) {
            if (str_starts_with($lower, $prefix)) {
                return 'That attribute is not allowed.';
            }
        }

        return null;
    }
}
