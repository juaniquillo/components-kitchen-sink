<?php

declare(strict_types=1);

namespace App\DesignSystem\Concerns;

use App\DesignSystem\Design;
use App\DesignSystem\DesignRegistry;
use Juaniquillo\BackendComponents\Enums\ComponentEnum;
use Juaniquillo\BackendComponents\Factories\ComponentFactory;
use Juaniquillo\BackendComponents\MainBackendComponent;
use RuntimeException;

/**
 * The boilerplate every design component shares.
 *
 * A component declares its slots and what it builds; the trait supplies
 * make(), the design it renders with, the attribute escape hatch and the
 * Htmlable bridge. build() is abstract on purpose: a component that forgets
 * it fails when the class is declared, not when a page renders.
 */
trait IsDesignComponent
{
    private readonly Design $design;

    /** @var array<string, int|string> */
    private array $attributes = [];

    public static function make(?Design $design = null): static
    {
        return new static($design ?? DesignRegistry::current());
    }

    private function __construct(Design $design)
    {
        $this->design = $design;
    }

    /**
     * @param  array<string, int|string>  $attributes
     */
    public function attributes(array $attributes): static
    {
        $this->attributes = [...$this->attributes, ...$attributes];

        return $this;
    }

    public function component(): MainBackendComponent
    {
        return $this->build();
    }

    abstract protected function build(): MainBackendComponent;

    public function toHtml(): string
    {
        return $this->component()->toHtml();
    }

    /**
     * The factory returns a union; a design component always needs the
     * concrete class, and the trait narrows it once for every component.
     */
    protected function makeComponent(ComponentEnum|string $name): MainBackendComponent
    {
        $component = ComponentFactory::make($name);

        if (! $component instanceof MainBackendComponent) {
            throw new RuntimeException('Design components build MainBackendComponent instances.');
        }

        return $component;
    }
}
