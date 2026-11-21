<?php

declare(strict_types=1);

namespace App\DesignSystem\Components;

use App\DesignSystem\Concerns\IsDesignComponent;
use App\DesignSystem\Contracts\DesignComponent;
use App\DesignSystem\Theme\BadgeVariant;
use App\DesignSystem\ThemeVariants;
use Juaniquillo\BackendComponents\Enums\ComponentEnum;
use Juaniquillo\BackendComponents\MainBackendComponent;

final class Badge implements DesignComponent
{
    use IsDesignComponent;

    private string $label = '';

    private BadgeVariant $variant = BadgeVariant::Default;

    /** @var array<string, int|string> */
    private array $attributes = [];

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

    protected function build(): MainBackendComponent
    {
        $component = $this->makeComponent(ComponentEnum::SPAN)
            ->setThemeManager($this->design->themeManager)
            ->setContent($this->label)
            ->setTheme('badge', ThemeVariants::make()->look($this->variant)->all())
            ->setTheme('radius', 'sm')
            ->setAttributes($this->attributes);

        return $component;
    }
}
