<?php

declare(strict_types=1);

use Juaniquillo\BackendComponents\Deserializers\ComponentDeserializer;
use Juaniquillo\BackendComponents\Enums\ComponentEnum;
use Juaniquillo\BackendComponents\Factories\ComponentFactory;

it('keeps livewire state runtime-only', function () {
    // Since v0.12 toArray() no longer serializes Livewire state: the
    // wrapper is runtime-only, so the array carries no livewire keys.
    $div = ComponentFactory::make(ComponentEnum::DIV)
        ->setLivewire()
        ->setLivewireKey('unique-key');

    expect($div->isLivewire())->toBeTrue()
        ->and($div->getLivewireKey())->toBe('unique-key');

    $array = $div->toArray();

    expect($array)->toBeArray()
        ->not->toHaveKey('isLivewire')
        ->not->toHaveKey('livewireKey')
        ->not->toHaveKey('livewireParams')
        ->and($array)->toHaveKey('name');
});

it('restores a livewire component without livewire state', function () {
    $div = ComponentFactory::make(ComponentEnum::DIV)
        ->setLivewire()
        ->setLivewireKey('user-component')
        ->setLivewireParams(['userId' => 1, 'team' => 'engineering']);

    expect($div->getLivewireParams())->toBe(['userId' => 1, 'team' => 'engineering']);

    $restored = ComponentDeserializer::fromArray($div->toArray());

    expect($restored->isLivewire())->toBeFalse();

    $restored->setLivewire()->setLivewireKey('user-component');

    expect($restored->isLivewire())->toBeTrue()
        ->and($restored->getLivewireKey())->toBe('user-component');
});

it('creates a button with livewire click handler', function () {
    $button = ComponentFactory::make(ComponentEnum::BUTTON)
        ->setContent('Click me')
        ->setAttribute('wire:click', 'handleClick');

    $html = (string) $this->blade('{{ $button }}', ['button' => $button]);

    expect($html)->toContain('wire:click="handleClick"')
        ->toContain('Click me');
});

it('creates a form with livewire submit', function () {
    $form = ComponentFactory::make(ComponentEnum::FORM)
        ->setAttribute('wire:submit', 'save')
        ->setContents([
            ComponentFactory::make(ComponentEnum::TEXT_INPUT)
                ->setAttribute('wire:model', 'name'),
            ComponentFactory::make(ComponentEnum::BUTTON)
                ->setAttribute('type', 'submit')
                ->setContent('Save'),
        ]);

    $html = (string) $this->blade('{{ $form }}', ['form' => $form]);

    expect($html)->toContain('wire:submit="save"')
        ->toContain('wire:model="name"')
        ->toContain('Save');
});

it('creates a div with livewire loading state', function () {
    $div = ComponentFactory::make(ComponentEnum::DIV)
        ->setContent('Loading...')
        ->setAttribute('wire:loading', '')
        ->setAttribute('wire:target', 'submit');

    $html = (string) $this->blade('{{ $div }}', ['div' => $div]);

    expect($html)->toContain('wire:loading')
        ->toContain('wire:target="submit"')
        ->toContain('Loading...');
});

it('creates a button with livewire poll', function () {
    $button = ComponentFactory::make(ComponentEnum::BUTTON)
        ->setContent('Refresh')
        ->setAttribute('wire:poll', '5s');

    $html = (string) $this->blade('{{ $button }}', ['button' => $button]);

    expect($html)->toContain('wire:poll="5s"')
        ->toContain('Refresh');
});

it('creates a div with livewire navigate', function () {
    $div = ComponentFactory::make(ComponentEnum::DIV)
        ->setContent('Go to dashboard')
        ->setAttribute('wire:navigate', '/dashboard');

    $html = (string) $this->blade('{{ $div }}', ['div' => $div]);

    expect($html)->toContain('wire:navigate="/dashboard"')
        ->toContain('Go to dashboard');
});

it('creates a button with livewire confirm', function () {
    $button = ComponentFactory::make(ComponentEnum::BUTTON)
        ->setContent('Delete')
        ->setAttribute('wire:confirm', 'Are you sure?')
        ->setAttribute('wire:click', 'delete');

    $html = (string) $this->blade('{{ $button }}', ['button' => $button]);

    expect($html)->toContain('wire:confirm="Are you sure?"')
        ->toContain('wire:click="delete"')
        ->toContain('Delete');
});

it('creates a div with livewire model live', function () {
    $div = ComponentFactory::make(ComponentEnum::DIV)
        ->setContents([
            ComponentFactory::make(ComponentEnum::TEXT_INPUT)
                ->setAttribute('wire:model.live', 'search'),
            ComponentFactory::make(ComponentEnum::DIV)
                ->setAttribute('wire:loading', '')
                ->setContent('Searching...'),
        ]);

    $html = (string) $this->blade('{{ $div }}', ['div' => $div]);

    expect($html)->toContain('wire:model.live="search"')
        ->toContain('wire:loading')
        ->toContain('Searching...');
});

it('creates a button with livewire click and key', function () {
    $button = ComponentFactory::make(ComponentEnum::BUTTON)
        ->setContent('Submit')
        ->setAttribute('wire:click', 'submit')
        ->setAttribute('wire:key', 'submit-button');

    $html = (string) $this->blade('{{ $button }}', ['button' => $button]);

    expect($html)->toContain('wire:click="submit"')
        ->toContain('wire:key="submit-button"')
        ->toContain('Submit');
});

it('creates a div with livewire ignore', function () {
    $div = ComponentFactory::make(ComponentEnum::DIV)
        ->setContent('Static content')
        ->setAttribute('wire:ignore', '');

    $html = (string) $this->blade('{{ $div }}', ['div' => $div]);

    expect($html)->toContain('wire:ignore')
        ->toContain('Static content');
});

it('creates a form with livewire validate', function () {
    $form = ComponentFactory::make(ComponentEnum::FORM)
        ->setAttribute('wire:submit.prevent', 'save')
        ->setContents([
            ComponentFactory::make(ComponentEnum::TEXT_INPUT)
                ->setAttribute('wire:model', 'email')
                ->setAttribute('type', 'email'),
            ComponentFactory::make(ComponentEnum::DIV)
                ->setAttribute('wire:loading', '')
                ->setContent('Saving...'),
        ]);

    $html = (string) $this->blade('{{ $form }}', ['form' => $form]);

    expect($html)->toContain('wire:submit.prevent="save"')
        ->toContain('wire:model="email"')
        ->toContain('type="email"')
        ->toContain('wire:loading')
        ->toContain('Saving...');
});

it('creates a button with livewire off', function () {
    $button = ComponentFactory::make(ComponentEnum::BUTTON)
        ->setContent('Disabled when offline')
        ->setAttribute('wire:offline', 'disabled');

    $html = (string) $this->blade('{{ $button }}', ['button' => $button]);

    expect($html)->toContain('wire:offline="disabled"')
        ->toContain('Disabled when offline');
});

it('creates a div with livewire snapshot', function () {
    $div = ComponentFactory::make(ComponentEnum::DIV)
        ->setContent('Snapshot content')
        ->setAttribute('wire:snapshot', '{}');

    $html = (string) $this->blade('{{ $div }}', ['div' => $div]);

    expect($html)->toContain('wire:snapshot')
        ->toContain('Snapshot content');
});

it('creates a button with livewire loading delay', function () {
    $button = ComponentFactory::make(ComponentEnum::BUTTON)
        ->setContent('Delayed loading')
        ->setAttribute('wire:click', 'process')
        ->setAttribute('wire:loading.delay', '');

    $html = (string) $this->blade('{{ $button }}', ['button' => $button]);

    expect($html)->toContain('wire:click="process"')
        ->toContain('wire:loading.delay')
        ->toContain('Delayed loading');
});

it('creates a div with livewire partial', function () {
    $div = ComponentFactory::make(ComponentEnum::DIV)
        ->setContent('Partial content')
        ->setAttribute('wire:partial', 'components.card');

    $html = (string) $this->blade('{{ $div }}', ['div' => $div]);

    expect($html)->toContain('wire:partial="components.card"')
        ->toContain('Partial content');
});

it('creates a button with livewire loading class', function () {
    $button = ComponentFactory::make(ComponentEnum::BUTTON)
        ->setContent('Submit')
        ->setAttribute('wire:click', 'submit')
        ->setAttribute('wire:loading.class', 'opacity-50');

    $html = (string) $this->blade('{{ $button }}', ['button' => $button]);

    expect($html)->toContain('wire:click="submit"')
        ->toContain('wire:loading.class="opacity-50"')
        ->toContain('Submit');
});

it('creates a div with livewire scroll', function () {
    $div = ComponentFactory::make(ComponentEnum::DIV)
        ->setContent('Scrollable content')
        ->setAttribute('wire:scroll', 'loadMore');

    $html = (string) $this->blade('{{ $div }}', ['div' => $div]);

    expect($html)->toContain('wire:scroll="loadMore"')
        ->toContain('Scrollable content');
});

it('creates a button with livewire click prevent', function () {
    $button = ComponentFactory::make(ComponentEnum::BUTTON)
        ->setContent('Prevent default')
        ->setAttribute('wire:click.prevent', 'handleClick');

    $html = (string) $this->blade('{{ $button }}', ['button' => $button]);

    expect($html)->toContain('wire:click.prevent="handleClick"')
        ->toContain('Prevent default');
});

it('creates a div with livewire init', function () {
    $div = ComponentFactory::make(ComponentEnum::DIV)
        ->setContent('Initialized content')
        ->setAttribute('wire:init', 'loadData');

    $html = (string) $this->blade('{{ $div }}', ['div' => $div]);

    expect($html)->toContain('wire:init="loadData"')
        ->toContain('Initialized content');
});

it('creates a button with livewire click self', function () {
    $button = ComponentFactory::make(ComponentEnum::BUTTON)
        ->setContent('Click self')
        ->setAttribute('wire:click.self', 'handleClick');

    $html = (string) $this->blade('{{ $button }}', ['button' => $button]);

    expect($html)->toContain('wire:click.self="handleClick"')
        ->toContain('Click self');
});
