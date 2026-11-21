<?php

declare(strict_types=1);

namespace App\DesignSystem\Components;

use App\DesignSystem\Contracts\DesignComponent;
use App\DesignSystem\Design;
use App\DesignSystem\DesignRegistry;
use App\DesignSystem\Theme\ButtonVariant;
use App\DesignSystem\Theme\Size;
use App\DesignSystem\Theme\State;
use App\DesignSystem\ThemeVariants;
use Juaniquillo\BackendComponents\Enums\ComponentEnum;
use Juaniquillo\BackendComponents\Factories\ComponentFactory;
use Juaniquillo\BackendComponents\MainBackendComponent;
use RuntimeException;

final class Button implements DesignComponent
{
    private string $label = '';

    private ButtonVariant $variant = ButtonVariant::Default;

    private Size $size = Size::Md;

    private string $type = 'button';

    private bool $disabled = false;

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

    public function variant(ButtonVariant $variant): static
    {
        $this->variant = $variant;

        return $this;
    }

    public function size(Size $size): static
    {
        $this->size = $size;

        return $this;
    }

    public function type(string $type): static
    {
        $this->type = $type;

        return $this;
    }

    public function disabled(bool $disabled = true): static
    {
        $this->disabled = $disabled;

        return $this;
    }

    public function attributes(array $attributes): static
    {
        $this->attributes = [...$this->attributes, ...$attributes];

        return $this;
    }

    public function component(): MainBackendComponent
    {
        // One list of variant keys: the look, then the state. State
        // variants are affordances, so a theme never needs a
        // "primary-disabled" for every combination.
        // One list of variant keys: the look, its size, and the state.
        $variants = ThemeVariants::make()
            ->look($this->variant)
            ->size($this->size)
            ->state($this->disabled ? State::Disabled : null);

        $button = ComponentFactory::make(ComponentEnum::BUTTON)
            ->setThemeManager($this->design->themeManager)
            ->setContent($this->label)
            ->setTheme('button', $variants->all())
            ->setTheme('radius', 'md')
            ->setAttribute('type', $this->type);

        if ($this->disabled) {
            $button->setAttribute('disabled', 'disabled');
        }

        $component = $button->setAttributes($this->attributes);

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
