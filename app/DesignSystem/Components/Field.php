<?php

declare(strict_types=1);

namespace App\DesignSystem\Components;

use App\DesignSystem\Concerns\IsDesignComponent;
use App\DesignSystem\Contracts\DesignComponent;
use Juaniquillo\BackendComponents\Enums\ComponentEnum;
use Juaniquillo\BackendComponents\MainBackendComponent;
use Juaniquillo\BaseBackendComponent\Contracts\BackendComponent;

final class Field implements DesignComponent
{
    use IsDesignComponent;

    private string|BackendComponent|null $label = null;

    private string|BackendComponent|null $input = null;

    private string|BackendComponent|null $error = null;

    private ?string $id = null;

    private bool $required = false;

    /** @var array<string, int|string> */
    private array $attributes = [];

    public function label(string|BackendComponent $label): static
    {
        $this->label = $label;

        return $this;
    }

    public function input(string|BackendComponent $input): static
    {
        $this->input = $input;

        return $this;
    }

    public function error(string|BackendComponent|null $error): static
    {
        $this->error = $error;

        return $this;
    }

    public function id(?string $id): static
    {
        $this->id = $id;

        return $this;
    }

    public function required(bool $required = true): static
    {
        $this->required = $required;

        return $this;
    }

    protected function build(): MainBackendComponent
    {
        $label = $this->label;
        $input = $this->input;
        $error = $this->error;

        // An explicit id wins; otherwise the control lends its name, whether
        // it is a string or a passed-in component, and a component's own id
        // beats its name. The id is written onto the control either way, so
        // a label can never point at nothing.
        $id = $this->id;

        if ($id === null && is_string($input) && $input !== '') {
            $id = $input;
        }

        if ($id === null && $input instanceof BackendComponent) {
            $candidate = $input->getAttribute('id') ?? $input->getAttribute('name');

            if ($candidate !== null && (string) $candidate !== '') {
                $id = (string) $candidate;
            }
        }

        $contents = [];

        if ($label !== null) {
            // The label is wired too: a passed label that points at some
            // other control would otherwise leave this one unnamed.
            $contents[] = $label instanceof BackendComponent
                ? $this->wireLabel($label, $id)
                : $this->buildLabel($label, $id);
        }

        if ($input !== null) {
            // Association is Field's job even when the control was built
            // elsewhere: it owns the label and the error, so it can wire
            // id, required and aria to whichever input it was handed. Only
            // attributes are touched, never content or themes.
            $contents[] = $input instanceof BackendComponent
                ? $this->wireInput($input, $id, $error !== null)
                : $this->buildInput($input, $id, $error !== null);
        }

        if ($error !== null) {
            $contents[] = $error instanceof BackendComponent
                ? $error
                : $this->buildError($error, $id);
        }

        $component = $this->makeComponent(ComponentEnum::DIV)
            ->setThemeManager($this->design->themeManager)
            ->setContents($contents)
            ->setTheme('field', 'container')
            ->setAttributes($this->attributes);

        return $component;
    }

    private function buildLabel(string $text, ?string $for): BackendComponent
    {
        $label = Label::make($this->design)->text($text);

        if ($for !== null) {
            $label->for($for);
        }

        return $label->component();
    }

    private function wireLabel(BackendComponent $label, ?string $id): BackendComponent
    {
        return $id === null ? $label : $label->setAttribute('for', $id);
    }

    private function wireInput(BackendComponent $input, ?string $id, bool $invalid): BackendComponent
    {
        $attributes = [];

        if ($id !== null) {
            $attributes['id'] = $id;
        }

        if ($this->required) {
            $attributes['required'] = 'required';
        }

        if ($invalid) {
            $attributes['aria-invalid'] = 'true';

            if ($id !== null) {
                $attributes['aria-describedby'] = $id.'-error';
            }
        }

        return $attributes === [] ? $input : $input->setAttributes($attributes);
    }

    private function buildInput(string $name, ?string $id, bool $invalid): BackendComponent
    {
        $input = Input::make($this->design)
            ->name($name)
            ->required($this->required);

        $attributes = [];

        if ($id !== null) {
            $attributes['id'] = $id;

            if ($invalid) {
                $attributes['aria-invalid'] = 'true';
                $attributes['aria-describedby'] = $id.'-error';
            }
        } elseif ($invalid) {
            $attributes['aria-invalid'] = 'true';
        }

        return $input->attributes($attributes)->component();
    }

    private function buildError(string $text, ?string $id): BackendComponent
    {
        $error = $this->makeComponent(ComponentEnum::DIV)
            ->setThemeManager($this->design->themeManager)
            ->setContent($text)
            ->setTheme('field', 'error');

        if ($id !== null) {
            $error->setAttribute('id', $id.'-error');
        }

        return $error;
    }
}
