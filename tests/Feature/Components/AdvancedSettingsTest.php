<?php

declare(strict_types=1);

use Juaniquillo\BackendComponents\Builders\ComponentBuilder;
use Juaniquillo\BackendComponents\Enums\ComponentEnum;

it('renders a button with data attributes', function () {
    $button = ComponentBuilder::make(ComponentEnum::BUTTON)
        ->setContent('Click me')
        ->setAttribute('data-action', 'submit')
        ->setAttribute('data-target', 'form1');

    $html = (string) $this->blade('{{ $button }}', ['button' => $button]);

    expect($html)->toContain('data-action="submit"')
        ->toContain('data-target="form1"')
        ->toContain('Click me');
});

it('renders a div with aria attributes', function () {
    $div = ComponentBuilder::make(ComponentEnum::DIV)
        ->setContent('Accessible content')
        ->setAttribute('role', 'alert')
        ->setAttribute('aria-live', 'polite');

    $html = (string) $this->blade('{{ $div }}', ['div' => $div]);

    expect($html)->toContain('role="alert"')
        ->toContain('aria-live="polite"')
        ->toContain('Accessible content');
});

it('renders a button with alpine.js click handler', function () {
    $button = ComponentBuilder::make(ComponentEnum::BUTTON)
        ->setContent('Toggle')
        ->setAttribute('@click', 'open = !open');

    $html = (string) $this->blade('{{ $button }}', ['button' => $button]);

    expect($html)->toContain('@click="open = !open"')
        ->toContain('Toggle');
});

it('renders a div with alpine.js conditional display', function () {
    $div = ComponentBuilder::make(ComponentEnum::DIV)
        ->setContent('Conditional content')
        ->setAttribute('x-show', 'isOpen')
        ->setAttribute('x-transition', '');

    $html = (string) $this->blade('{{ $div }}', ['div' => $div]);

    expect($html)->toContain('x-show="isOpen"')
        ->toContain('x-transition')
        ->toContain('Conditional content');
});

it('renders a form with wire:submit', function () {
    $form = ComponentBuilder::make(ComponentEnum::FORM)
        ->setAttribute('wire:submit', 'save')
        ->setContents([
            ComponentBuilder::make(ComponentEnum::TEXT_INPUT)
                ->setAttribute('wire:model', 'name'),
            ComponentBuilder::make(ComponentEnum::BUTTON)
                ->setAttribute('type', 'submit')
                ->setContent('Save'),
        ]);

    $html = (string) $this->blade('{{ $form }}', ['form' => $form]);

    expect($html)->toContain('wire:submit="save"')
        ->toContain('wire:model="name"')
        ->toContain('Save');
});

it('renders a button with x-on:click', function () {
    $button = ComponentBuilder::make(ComponentEnum::BUTTON)
        ->setContent('Alert')
        ->setAttribute('x-on:click', "alert('clicked')");

    $html = (string) $this->blade('{{ $button }}', ['button' => $button]);

    expect($html)->toContain('x-on:click=')
        ->toContain('Alert');
});

it('renders a div with x-data', function () {
    $div = ComponentBuilder::make(ComponentEnum::DIV)
        ->setAttribute('x-data', '{ open: false }')
        ->setContents([
            ComponentBuilder::make(ComponentEnum::BUTTON)
                ->setContent('Toggle')
                ->setAttribute('@click', 'open = !open'),
            ComponentBuilder::make(ComponentEnum::DIV)
                ->setContent('Dropdown content')
                ->setAttribute('x-show', 'open'),
        ]);

    $html = (string) $this->blade('{{ $div }}', ['div' => $div]);

    expect($html)->toContain('x-data="{ open: false }"')
        ->toContain('@click="open = !open"')
        ->toContain('x-show="open"')
        ->toContain('Toggle')
        ->toContain('Dropdown content');
});

it('renders a button with multiple alpine attributes', function () {
    $button = ComponentBuilder::make(ComponentEnum::BUTTON)
        ->setContent('Multi Alpine')
        ->setAttribute('x-data', '{ loading: false }')
        ->setAttribute('@click', 'loading = true')
        ->setAttribute(':disabled', 'loading');

    $html = (string) $this->blade('{{ $button }}', ['button' => $button]);

    expect($html)->toContain('x-data="{ loading: false }"')
        ->toContain('@click="loading = true"')
        ->toContain(':disabled="loading"')
        ->toContain('Multi Alpine');
});

it('renders a div with wire:loading', function () {
    $div = ComponentBuilder::make(ComponentEnum::DIV)
        ->setContent('Loading...')
        ->setAttribute('wire:loading', '')
        ->setAttribute('wire:target', 'submit');

    $html = (string) $this->blade('{{ $div }}', ['div' => $div]);

    expect($html)->toContain('wire:loading')
        ->toContain('wire:target="submit"')
        ->toContain('Loading...');
});

it('renders a button with wire:click', function () {
    $button = ComponentBuilder::make(ComponentEnum::BUTTON)
        ->setContent('Click me')
        ->setAttribute('wire:click', 'increment');

    $html = (string) $this->blade('{{ $button }}', ['button' => $button]);

    expect($html)->toContain('wire:click="increment"')
        ->toContain('Click me');
});

it('renders a div with x-init', function () {
    $div = ComponentBuilder::make(ComponentEnum::DIV)
        ->setAttribute('x-data', '{ count: 0 }')
        ->setAttribute('x-init', 'count = 1')
        ->setContent('Count: ');

    $html = (string) $this->blade('{{ $div }}', ['div' => $div]);

    expect($html)->toContain('x-data="{ count: 0 }"')
        ->toContain('x-init="count = 1"')
        ->toContain('Count:');
});

it('renders a button with x-bind:class', function () {
    $button = ComponentBuilder::make(ComponentEnum::BUTTON)
        ->setContent('Styled')
        ->setAttribute('x-bind:class', "active ? 'bg-blue-500' : 'bg-gray-500'");

    $html = (string) $this->blade('{{ $button }}', ['button' => $button]);

    expect($html)->toContain('x-bind:class=')
        ->toContain('Styled');
});

it('renders a div with x-on:keydown', function () {
    $div = ComponentBuilder::make(ComponentEnum::DIV)
        ->setAttribute('x-data', '{}')
        ->setAttribute('x-on:keydown.escape', 'open = false')
        ->setContent('Press Escape to close');

    $html = (string) $this->blade('{{ $div }}', ['div' => $div]);

    expect($html)->toContain('x-on:keydown.escape="open = false"')
        ->toContain('Press Escape to close');
});

it('renders a form with wire:model.live', function () {
    $form = ComponentBuilder::make(ComponentEnum::FORM)
        ->setContents([
            ComponentBuilder::make(ComponentEnum::TEXT_INPUT)
                ->setAttribute('wire:model.live', 'search'),
            ComponentBuilder::make(ComponentEnum::DIV)
                ->setAttribute('wire:loading', '')
                ->setContent('Searching...'),
        ]);

    $html = (string) $this->blade('{{ $form }}', ['form' => $form]);

    expect($html)->toContain('wire:model.live="search"')
        ->toContain('wire:loading')
        ->toContain('Searching...');
});

it('renders a button with x-cloak', function () {
    $button = ComponentBuilder::make(ComponentEnum::BUTTON)
        ->setContent('Hidden until ready')
        ->setAttribute('x-cloak', '');

    $html = (string) $this->blade('{{ $button }}', ['button' => $button]);

    expect($html)->toContain('x-cloak')
        ->toContain('Hidden until ready');
});

it('renders a div with x-transition:enter', function () {
    $div = ComponentBuilder::make(ComponentEnum::DIV)
        ->setContent('Transitioning content')
        ->setAttribute('x-show', 'open')
        ->setAttribute('x-transition:enter', 'transition ease-out duration-300')
        ->setAttribute('x-transition:enter-start', 'opacity-0 scale-95')
        ->setAttribute('x-transition:enter-end', 'opacity-100 scale-100');

    $html = (string) $this->blade('{{ $div }}', ['div' => $div]);

    expect($html)->toContain('x-transition:enter="transition ease-out duration-300"')
        ->toContain('x-transition:enter-start="opacity-0 scale-95"')
        ->toContain('x-transition:enter-end="opacity-100 scale-100"')
        ->toContain('Transitioning content');
});

it('renders a button with wire:poll', function () {
    $button = ComponentBuilder::make(ComponentEnum::BUTTON)
        ->setContent('Refresh')
        ->setAttribute('wire:poll', '5s');

    $html = (string) $this->blade('{{ $button }}', ['button' => $button]);

    expect($html)->toContain('wire:poll="5s"')
        ->toContain('Refresh');
});

it('renders a div with x-effect', function () {
    $div = ComponentBuilder::make(ComponentEnum::DIV)
        ->setAttribute('x-data', '{ count: 0 }')
        ->setAttribute('x-effect', 'console.log(count)')
        ->setContent('Count: ');

    $html = (string) $this->blade('{{ $div }}', ['div' => $div]);

    expect($html)->toContain('x-effect="console.log(count)"')
        ->toContain('Count:');
});

it('renders a button with wire:confirm', function () {
    $button = ComponentBuilder::make(ComponentEnum::BUTTON)
        ->setContent('Delete')
        ->setAttribute('wire:confirm', 'Are you sure?')
        ->setAttribute('wire:click', 'delete');

    $html = (string) $this->blade('{{ $button }}', ['button' => $button]);

    expect($html)->toContain('wire:confirm="Are you sure?"')
        ->toContain('wire:click="delete"')
        ->toContain('Delete');
});

it('renders a div with x-intersect', function () {
    $div = ComponentBuilder::make(ComponentEnum::DIV)
        ->setAttribute('x-data', '{ visible: false }')
        ->setAttribute('x-intersect:enter', 'visible = true')
        ->setContent('Infinite scroll item');

    $html = (string) $this->blade('{{ $div }}', ['div' => $div]);

    expect($html)->toContain('x-intersect:enter="visible = true"')
        ->toContain('Infinite scroll item');
});

it('renders a button with wire:navigate', function () {
    $button = ComponentBuilder::make(ComponentEnum::BUTTON)
        ->setContent('Go to page')
        ->setAttribute('wire:navigate', '/dashboard');

    $html = (string) $this->blade('{{ $button }}', ['button' => $button]);

    expect($html)->toContain('wire:navigate="/dashboard"')
        ->toContain('Go to page');
});
