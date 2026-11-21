<?php

declare(strict_types=1);

namespace App\DesignSystem\Components;

use App\DesignSystem\Contracts\DesignComponent;
use App\DesignSystem\Design;
use App\DesignSystem\DesignRegistry;
use App\DesignSystem\Theme\Look;
use App\DesignSystem\Theme\State;
use App\DesignSystem\ThemeVariants;
use Juaniquillo\BackendComponents\Enums\ComponentEnum;
use Juaniquillo\BackendComponents\Factories\ComponentFactory;
use Juaniquillo\BackendComponents\MainBackendComponent;
use RuntimeException;

final class Textarea implements DesignComponent
{
    private string $name = '';

    private ?int $rows = null;

    private ?string $value = null;

    private ?string $placeholder = null;

    private bool $disabled = false;

    private bool $required = false;

    /** @var array<string, int|string> */
    private array $attributes = [];

    private function __construct(private readonly Design $design) {}

    public static function make(?Design $design = null): static
    {
        return new self($design ?? DesignRegistry::current());
    }

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

    public function attributes(array $attributes): static
    {
        $this->attributes = [...$this->attributes, ...$attributes];

        return $this;
    }

    public function component(): MainBackendComponent
    {
        $variants = ThemeVariants::make()
            ->look(Look::Default)
            ->state($this->disabled ? State::Disabled : null);

        $textarea = ComponentFactory::make(ComponentEnum::TEXTAREA)
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
