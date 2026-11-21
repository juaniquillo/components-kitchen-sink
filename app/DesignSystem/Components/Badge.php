<?php

declare(strict_types=1);

namespace App\DesignSystem\Components;

use App\DesignSystem\Contracts\DesignComponent;
use App\DesignSystem\Design;
use App\DesignSystem\DesignRegistry;
use App\DesignSystem\Theme\BadgeVariant;
use App\DesignSystem\ThemeVariants;
use Juaniquillo\BackendComponents\Enums\ComponentEnum;
use Juaniquillo\BackendComponents\Factories\ComponentFactory;
use Juaniquillo\BackendComponents\MainBackendComponent;
use RuntimeException;

final class Badge implements DesignComponent
{
    private string $label = '';

    private BadgeVariant $variant = BadgeVariant::Default;

    /** @var array<string, int|string> */
    private array $attributes = [];

    private function __construct(private readonly Design $design) {}

    public static function make(?Design $design = null): static
    {
        return new self($design ?? DesignRegistry::current());
    }

    public function label(string $label): static
    {
        $this->label = $label;

        return $this;
    }

    public function variant(BadgeVariant $variant): static
    {
        $this->variant = $variant;

        return $this;
    }

    public function attributes(array $attributes): static
    {
        $this->attributes = [...$this->attributes, ...$attributes];

        return $this;
    }

    public function component(): MainBackendComponent
    {
        $component = ComponentFactory::make(ComponentEnum::SPAN)
            ->setThemeManager($this->design->themeManager)
            ->setContent($this->label)
            ->setTheme('badge', ThemeVariants::make()->look($this->variant)->all())
            ->setTheme('radius', 'sm')
            ->setAttributes($this->attributes);

        if (! $component instanceof MainBackendComponent) {
            throw new RuntimeException('Design components build MainBackendComponent instances.');
        }

        return $component;
    }

    public function toHtml(): string
    {
        return $this->component()->toHtml();
    }
}
