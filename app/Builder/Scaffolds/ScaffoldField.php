<?php

declare(strict_types=1);

namespace App\Builder\Scaffolds;

/**
 * One question a scaffold form asks before building.
 *
 * The type decides the input the drawer renders, the shape the value is
 * normalized to, and the default a fresh form opens with.
 */
final class ScaffoldField
{
    public function __construct(
        public readonly string $name,
        public readonly string $label,
        public readonly ScaffoldFieldType $type,
        public readonly mixed $default = null,
        public readonly ?int $min = null,
        public readonly ?int $max = null,
        public readonly ?string $placeholder = null,
    ) {}

    /**
     * Coerce a raw form value to the shape the scaffold builder takes.
     */
    public function normalize(mixed $value): mixed
    {
        return match ($this->type) {
            ScaffoldFieldType::NUMBER => $this->clamp((int) $value),
            ScaffoldFieldType::TEXT => trim((string) $value),
            ScaffoldFieldType::CHECKBOX => (bool) $value,
        };
    }

    private function clamp(int $value): int
    {
        if ($this->min !== null) {
            $value = max($this->min, $value);
        }

        if ($this->max !== null) {
            $value = min($this->max, $value);
        }

        return $value;
    }
}
