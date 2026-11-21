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

final class Select implements DesignComponent
{
    private string $name = '';

    /** @var array<string, string> */
    private array $options = [];

    private ?string $value = null;

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

    /**
     * The options, keyed by value with their labels as values.
     *
     * @param  array<string, string>  $options
     */
    public function options(array $options): static
    {
        $this->options = $options;

        return $this;
    }

    public function value(?string $value): static
    {
        $this->value = $value;

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

        $select = ComponentFactory::make(ComponentEnum::SELECT)
            ->setThemeManager($this->design->themeManager)
            ->setTheme('select', $variants->all())
            ->setTheme('radius', 'sm');

        if ($this->name !== '') {
            $select->setAttribute('name', $this->name);
        }

        foreach ($this->options as $value => $label) {
            $option = ComponentFactory::make(ComponentEnum::OPTION)
                ->setThemeManager($this->design->themeManager)
                ->setAttribute('value', (string) $value)
                ->setContent($label);

            if ($this->value !== null && (string) $value === $this->value) {
                $option->setAttribute('selected', 'selected');
            }

            $select->setContent($option);
        }

        if ($this->disabled) {
            $select->setAttribute('disabled', 'disabled');
        }

        if ($this->required) {
            $select->setAttribute('required', 'required');
        }

        $component = $select->setAttributes($this->attributes);

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
