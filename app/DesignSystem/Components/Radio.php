<?php

declare(strict_types=1);

namespace App\DesignSystem\Components;

use App\DesignSystem\Concerns\IsDesignComponent;
use App\DesignSystem\Contracts\DesignComponent;
use Juaniquillo\BackendComponents\Enums\ComponentEnum;
use Juaniquillo\BackendComponents\MainBackendComponent;

final class Radio implements DesignComponent
{
    use IsDesignComponent;

    private string $name = '';

    private ?string $value = null;

    private ?string $label = null;

    private bool $checked = false;

    private bool $disabled = false;

    private bool $required = false;

    /** @var array<string, int|string> */
    private array $attributes = [];

    public function name(string $name): static
    {
        $this->name = $name;

        return $this;
    }

    public function value(?string $value): static
    {
        $this->value = $value;

        return $this;
    }

    public function label(?string $label): static
    {
        $this->label = $label;

        return $this;
    }

    public function checked(bool $checked = true): static
    {
        $this->checked = $checked;

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
        $box = $this->makeComponent(ComponentEnum::RADIO_INPUT)
            ->setThemeManager($this->design->themeManager)
            ->setTheme('radio', 'box');

        if ($this->name !== '') {
            $box->setAttribute('name', $this->name);
        }

        if ($this->value !== null) {
            $box->setAttribute('value', $this->value);
        }

        if ($this->checked) {
            $box->setAttribute('checked', 'checked');
        }

        if ($this->disabled) {
            $box->setAttribute('disabled', 'disabled');
        }

        if ($this->required) {
            $box->setAttribute('required', 'required');
        }

        $label = $this->makeComponent(ComponentEnum::LABEL)
            ->setThemeManager($this->design->themeManager)
            ->setTheme('radio', 'container')
            ->setTheme('space', 'sm')
            ->setContents([
                $box,
                $this->makeComponent(ComponentEnum::SPAN)
                    ->setThemeManager($this->design->themeManager)
                    ->setTheme('radio', 'label')
                    ->setTheme('text', 'sm')
                    ->setContent($this->label ?? ''),
            ]);

        $component = $label->setAttributes($this->attributes);

        return $component;
    }
}
