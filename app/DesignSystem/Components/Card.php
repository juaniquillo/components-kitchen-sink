<?php

declare(strict_types=1);

namespace App\DesignSystem\Components;

use App\DesignSystem\Contracts\DesignComponent;
use App\DesignSystem\Design;
use App\DesignSystem\DesignRegistry;
use Juaniquillo\BackendComponents\Enums\ComponentEnum;
use Juaniquillo\BackendComponents\Factories\ComponentFactory;
use Juaniquillo\BackendComponents\MainBackendComponent;
use Juaniquillo\BaseBackendComponent\Contracts\BackendComponent;
use RuntimeException;

final class Card implements DesignComponent
{
    private string|BackendComponent|null $title = null;

    private string|BackendComponent|null $body = null;

    private string|BackendComponent|null $footer = null;

    private int $titleLevel = 3;

    /** @var array<string, int|string> */
    private array $attributes = [];

    private function __construct(private readonly Design $design) {}

    public static function make(?Design $design = null): static
    {
        return new self($design ?? DesignRegistry::current());
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

    public function footer(string|BackendComponent $footer): static
    {
        $this->footer = $footer;

        return $this;
    }

    /**
     * The heading level of a string title, clamped to the six levels HTML
     * has. A card usually sits under a section heading, so it starts at 3.
     */
    public function titleLevel(int $level): static
    {
        $this->titleLevel = max(1, min(6, $level));

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
            // A string title is a heading, so cards take part in the
            // document outline. A component title is whatever its author
            // built.
            $contents[] = $this->title instanceof BackendComponent
                ? $this->title
                : $this->heading($this->title);
        }

        if ($this->body !== null) {
            $contents[] = $this->section($this->body, 'body');
        }

        if ($this->footer !== null) {
            $contents[] = $this->section($this->footer, 'foot');
        }

        $component = ComponentFactory::make(ComponentEnum::DIV)
            ->setThemeManager($this->design->themeManager)
            ->setContents($contents)
            ->setTheme('card', 'container')
            ->setTheme('radius', 'md')
            ->setAttributes($this->attributes);

        if (! $component instanceof MainBackendComponent) {
            throw new RuntimeException('Design components build MainBackendComponent instances.');
        }

        return $component;
    }

    private function heading(string $text): BackendComponent
    {
        $enum = match ($this->titleLevel) {
            1 => ComponentEnum::H1,
            2 => ComponentEnum::H2,
            4 => ComponentEnum::H4,
            5 => ComponentEnum::H5,
            6 => ComponentEnum::H6,
            default => ComponentEnum::H3,
        };

        return ComponentFactory::make($enum)
            ->setThemeManager($this->design->themeManager)
            ->setContent($text)
            ->setTheme('card', 'title')
            ->setTheme('text', 'lg');
    }

    private function section(string|BackendComponent $content, string $variant): BackendComponent
    {
        if (! is_string($content)) {
            return $content;
        }

        return ComponentFactory::make(ComponentEnum::DIV)
            ->setThemeManager($this->design->themeManager)
            ->setContent($content)
            ->setTheme('card', $variant);
    }

    public function toHtml(): string
    {
        return $this->component()->toHtml();
    }
}
