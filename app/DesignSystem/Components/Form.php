<?php

declare(strict_types=1);

namespace App\DesignSystem\Components;

use App\DesignSystem\Contracts\DesignComponent;
use App\DesignSystem\Design;
use App\DesignSystem\DesignRegistry;
use Illuminate\Contracts\Support\Htmlable;
use Juaniquillo\BackendComponents\Enums\ComponentEnum;
use Juaniquillo\BackendComponents\Factories\ComponentFactory;
use Juaniquillo\BackendComponents\MainBackendComponent;
use RuntimeException;

final class Form implements DesignComponent
{
    private string $action = '';

    private string $method = 'post';

    /** @var array<int, DesignComponent|Htmlable> */
    private array $fields = [];

    private DesignComponent|Htmlable|null $actions = null;

    private bool $csrf = false;

    private bool $methodInput = false;

    /** @var array<string, int|string> */
    private array $attributes = [];

    private function __construct(private readonly Design $design) {}

    public static function make(?Design $design = null): static
    {
        return new self($design ?? DesignRegistry::current());
    }

    public function action(string $action): static
    {
        $this->action = $action;

        return $this;
    }

    public function method(string $method): static
    {
        $this->method = $method;

        return $this;
    }

    /**
     * @param  array<int, DesignComponent|Htmlable>  $fields
     */
    public function fields(array $fields): static
    {
        $this->fields = $fields;

        return $this;
    }

    public function actions(DesignComponent|Htmlable $actions): static
    {
        $this->actions = $actions;

        return $this;
    }

    /**
     * Emit Laravel's CSRF field. Off by default: the component builds
     * plain HTML unless the app asks for its framework's extras.
     */
    public function csrf(bool $csrf = true): static
    {
        $this->csrf = $csrf;

        return $this;
    }

    /**
     * Emit the hidden _method input for verbs HTML forms cannot send.
     */
    public function methodInput(bool $methodInput = true): static
    {
        $this->methodInput = $methodInput;

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

        foreach ($this->fields as $field) {
            // A field is a design component: the form takes what it builds,
            // so the tree only ever holds backend components.
            $contents[] = $field instanceof DesignComponent ? $field->component() : $field;
        }

        if ($this->actions !== null) {
            $contents[] = ComponentFactory::make(ComponentEnum::DIV)
                ->setThemeManager($this->design->themeManager)
                ->setTheme('form', 'actions')
                ->setTheme('space', 'sm')
                ->setContent(
                    $this->actions instanceof DesignComponent
                        ? $this->actions->component()
                        : $this->actions
                );
        }

        $form = ComponentFactory::make(ComponentEnum::FORM)
            ->setThemeManager($this->design->themeManager)
            ->setTheme('form', 'container')
            ->setTheme('space', 'md')
            ->setSetting('disable_csrf', ! $this->csrf)
            ->setSetting('disable_method_input', ! $this->methodInput)
            ->setAttribute('method', strtoupper($this->method))
            ->setContents($contents);

        if ($this->action !== '') {
            $form->setAttribute('action', $this->action);
        }

        $component = $form->setAttributes($this->attributes);

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
