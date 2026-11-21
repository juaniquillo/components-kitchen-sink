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

final class Input implements DesignComponent
{
    private string $type = 'text';

    private string $name = '';

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

    public function type(string $type): static
    {
        $this->type = $type;

        return $this;
    }

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
        // One input element, and the type as an attribute. The package has
        // dedicated enums for a few types, but they all render the same
        // <input> template, so naming them here would only mean a mapping
        // that silently degrades for every type left out of it. Handing the
        // type to the element keeps date, time, color, range and the rest
        // working without a code change.
        // Same rule as the button: the look is one variant, the state is
        // another, and both are complete on their own.
        $variants = ThemeVariants::make()
            ->look(Look::Default)
            ->state($this->disabled ? State::Disabled : null);

        $input = ComponentFactory::make(ComponentEnum::TEXT_INPUT)
            ->setThemeManager($this->design->themeManager)
            ->setTheme('input', $variants->all())
            ->setTheme('radius', 'sm')
            ->setAttribute('type', $this->type);

        if ($this->name !== '') {
            $input->setAttribute('name', $this->name);
        }

        if ($this->value !== null) {
            $input->setAttribute('value', $this->value);
        }

        if ($this->placeholder !== null) {
            $input->setAttribute('placeholder', $this->placeholder);
        }

        if ($this->disabled) {
            $input->setAttribute('disabled', 'disabled');
        }

        if ($this->required) {
            $input->setAttribute('required', 'required');
        }

        $component = $input->setAttributes($this->attributes);

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
