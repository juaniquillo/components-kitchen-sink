<?php

declare(strict_types=1);

namespace App\DesignSystem;

use App\DesignSystem\Theme\Size;
use App\DesignSystem\Theme\State;
use BackedEnum;
use InvalidArgumentException;

/**
 * The variant keys a component hands to setTheme().
 *
 * Components name a look, a size and a state; this is the one place that
 * knows how the theme files spell them, so no component builds those keys
 * by hand. The enums carry their prefix in the value ("size.md"); a plain
 * string is still accepted and gets the prefix here, so the convention has
 * a single implementation.
 */
final class ThemeVariants
{
    public const SIZE_PREFIX = 'size.';

    public const STATE_PREFIX = 'state.';

    /** @var array<int, string> */
    private array $variants = [];

    public static function make(): self
    {
        return new self;
    }

    public function look(BackedEnum|string $look): self
    {
        $key = $look instanceof BackedEnum ? (string) $look->value : trim($look);

        $this->assertBare($key, 'look');

        $this->variants[] = $key;

        return $this;
    }

    public function size(Size|string|null $size): self
    {
        if ($size === null) {
            return $this;
        }

        return $size instanceof Size
            ? $this->push((string) $size->value)
            : $this->dimension(self::SIZE_PREFIX, $size, 'size');
    }

    public function state(State|string|null $state): self
    {
        if ($state === null) {
            return $this;
        }

        return $state instanceof State
            ? $this->push((string) $state->value)
            : $this->dimension(self::STATE_PREFIX, $state, 'state');
    }

    /**
     * @return array<int, string>
     */
    public function all(): array
    {
        return $this->variants;
    }

    private function push(string $variant): self
    {
        $this->variants[] = $variant;

        return $this;
    }

    private function dimension(string $prefix, string $name, string $dimension): self
    {
        $this->assertBare($name, $dimension);

        return $this->push($prefix.trim($name));
    }

    private function assertBare(string $name, string $dimension): void
    {
        $name = trim($name);

        if ($name === '') {
            throw new InvalidArgumentException("A theme {$dimension} cannot be empty.");
        }

        if (str_contains($name, '.')) {
            throw new InvalidArgumentException(
                "Theme {$dimension} '{$name}' must not contain a dot; pass the bare name or the enum case."
            );
        }
    }
}
