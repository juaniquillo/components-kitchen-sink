<?php

declare(strict_types=1);

namespace App\DesignSystem\Components;

use App\DesignSystem\Contracts\DesignComponent;
use App\DesignSystem\Design;
use App\DesignSystem\DesignRegistry;
use App\DesignSystem\Theme\AlertVariant;
use App\DesignSystem\ThemeVariants;
use Juaniquillo\BackendComponents\Enums\ComponentEnum;
use Juaniquillo\BackendComponents\Factories\ComponentFactory;
use Juaniquillo\BackendComponents\MainBackendComponent;
use Juaniquillo\BaseBackendComponent\Contracts\BackendComponent;
use RuntimeException;

final class Alert implements DesignComponent
{
    private AlertVariant $variant = AlertVariant::Info;

    private string|BackendComponent|null $title = null;

    private string|BackendComponent|null $body = null;

    private bool $live = false;

    /** @var array<string, int|string> */
    private array $attributes = [];

    private function __construct(private readonly Design $design) {}

    public static function make(?Design $design = null): static
    {
        return new self($design ?? DesignRegistry::current());
    }

    public function variant(AlertVariant $variant): static
    {
        $this->variant = $variant;

        return $this;
    }

    public function title(string|BackendComponent $title): static
    {
        $this->title = $title;

        return $this;
    }

    public function body(string|BackendComponent $body): static
    {
        $this->body = $body;

        return $this;
    }

    /**
     * Announce the alert when it appears.
     *
     * Off by default: a static alert on a page should not be read out on
     * load. When on, failures interrupt (role=alert) and the rest wait for
     * a pause (role=status).
     */
    public function live(bool $live = true): static
    {
        $this->live = $live;

        return $this;
    }

    public function attributes(array $attributes): static
    {
        $this->attributes = [...$this->attributes, ...$attributes];

        return $this;
    }

    public function component(): MainBackendComponent
    {
        $contents = [];

        if ($this->title !== null) {
            $contents[] = $this->title instanceof BackendComponent
                ? $this->title
                : ComponentFactory::make(ComponentEnum::BOLD)
                    ->setThemeManager($this->design->themeManager)
                    ->setContent($this->title);
        }

        if ($this->body !== null) {
            $contents[] = $this->body instanceof BackendComponent
                ? $this->body
                : ComponentFactory::make(ComponentEnum::PARAGRAPH)
                    ->setThemeManager($this->design->themeManager)
                    ->setContent($this->body);
        }

        $attributes = [];

        if ($this->live) {
            $attributes['role'] = in_array($this->variant, [AlertVariant::Warning, AlertVariant::Danger], true) ? 'alert' : 'status';
        }

        $component = ComponentFactory::make(ComponentEnum::DIV)
            ->setThemeManager($this->design->themeManager)
            ->setContents($contents)
            ->setTheme('alert', ThemeVariants::make()->look($this->variant)->all())
            ->setTheme('radius', 'sm')
            ->setAttributes([...$attributes, ...$this->attributes]);

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
