<?php

declare(strict_types=1);

namespace App\DesignSystem\Components;

use App\DesignSystem\Concerns\IsDesignComponent;
use App\DesignSystem\Contracts\DesignComponent;
use App\DesignSystem\Theme\Look;
use App\DesignSystem\Theme\State;
use App\DesignSystem\ThemeVariants;
use Juaniquillo\BackendComponents\Enums\ComponentEnum;
use Juaniquillo\BackendComponents\MainBackendComponent;

final class Textarea implements DesignComponent
{
    use IsDesignComponent;

    private string $name = '';

    private ?int $rows = null;

    private ?string $value = null;

    private ?string $placeholder = null;

    private bool $disabled = false;

    private bool $required = false;

    /** @var array<string, int|string> */
    private array $attributes = [];

    public function name(string $name): static
    {
        $this->name = $name;

        return $this;
    }

    public function rows(int $rows): static
    {
        $this->rows = $rows;

        return $this;
    }

    public function value(?string $value): static
    {
        $this->value = $value;

        return $this;
    }

    public function placeholder(?string $placeholder): static
    {
        $this->placeholder = $placeholder;

        return $this;
    }

    public function disabled(bool $disabled = true): static
    {
        $this->disabled = $disabled;

        return $this;
    }

    public function required(bool $required = true): static
    {
        $this->required = $required;

        return $this;
    }

    protected function build(): MainBackendComponent
    {
        $variants = ThemeVariants::make()
            ->look(Look::Default)
            ->state($this->disabled ? State::Disabled : null);

        $textarea = $this->makeComponent(ComponentEnum::TEXTAREA)
            ->setThemeManager($this->design->themeManager)
            ->setTheme('textarea', $variants->all())
            ->setTheme('radius', 'sm');

        if ($this->name !== '') {
            $textarea->setAttribute('name', $this->name);
        }

        if ($this->rows !== null) {
            $textarea->setAttribute('rows', (string) $this->rows);
        }

        if ($this->placeholder !== null) {
            $textarea->setAttribute('placeholder', $this->placeholder);
        }

        if ($this->value !== null) {
            $textarea->setContent($this->value);
        }

        if ($this->disabled) {
            $textarea->setAttribute('disabled', 'disabled');
        }

        if ($this->required) {
            $textarea->setAttribute('required', 'required');
        }

        $component = $textarea->setAttributes($this->attributes);

        return $component;
    }
}
