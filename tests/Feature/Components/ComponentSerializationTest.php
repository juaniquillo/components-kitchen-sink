<?php

declare(strict_types=1);

use Juaniquillo\BackendComponents\Components\Individual\DivComponent;
use Juaniquillo\BackendComponents\Contracts\BackendComponent;
use Juaniquillo\BackendComponents\Deserializers\ComponentDeserializer;
use Juaniquillo\BackendComponents\Enums\ComponentEnum;
use Juaniquillo\BackendComponents\Factories\ComponentFactory;

it('serializes a simple button to array', function () {
    $button = ComponentFactory::make(ComponentEnum::BUTTON)
        ->setContent('Click me')
        ->setAttribute('class', 'btn');

    $array = $button->toArray();

    expect($array)->toBeArray()
        ->toHaveKey('name')
        ->toHaveKey('attributes')
        ->toHaveKey('contents')
        ->and($array['name'])->toBe('inline.button')
        ->and($array['attributes'])->toHaveKey('class')
        ->and($array['attributes']['class'])->toBe('btn');
});

it('restores a button from array', function () {
    $original = ComponentFactory::make(ComponentEnum::BUTTON)
        ->setContent('Click me')
        ->setAttribute('class', 'btn');

    $array = $original->toArray();
    $restored = ComponentDeserializer::fromArray($array);

    expect($restored)->toBeInstanceOf(BackendComponent::class);

    $html = $restored->toHtml();

    expect($html)->toContain('Click me')
        ->toContain('class="btn"');
});

it('serializes a div with content to array', function () {
    $div = ComponentFactory::make(ComponentEnum::DIV)
        ->setContent('Hello World')
        ->setAttribute('id', 'greeting');

    $array = $div->toArray();

    expect($array)->toBeArray()
        ->toHaveKey('name')
        ->toHaveKey('attributes')
        ->toHaveKey('contents')
        ->and($array['name'])->toBe('div')
        ->and($array['attributes']['id'])->toBe('greeting');
});

it('restores a div from array', function () {
    $original = ComponentFactory::make(ComponentEnum::DIV)
        ->setContent('Hello World')
        ->setAttribute('id', 'greeting');

    $array = $original->toArray();
    $restored = ComponentDeserializer::fromArray($array);

    $html = $restored->toHtml();

    expect($html)->toContain('Hello World')
        ->toContain('id="greeting"');
});

it('serializes nested components to array', function () {
    $card = ComponentFactory::make(ComponentEnum::DIV)
        ->setAttribute('class', 'card')
        ->setContents([
            ComponentFactory::make(ComponentEnum::H2)
                ->setContent('Title'),
            ComponentFactory::make(ComponentEnum::PARAGRAPH)
                ->setContent('Body text'),
        ]);

    $array = $card->toArray();

    expect($array)->toBeArray()
        ->toHaveKey('contents')
        ->and($array['contents'])->toBeArray();
});

it('restores nested components from array', function () {
    $original = ComponentFactory::make(ComponentEnum::DIV)
        ->setAttribute('class', 'card')
        ->setContents([
            ComponentFactory::make(ComponentEnum::H2)
                ->setContent('Title'),
            ComponentFactory::make(ComponentEnum::PARAGRAPH)
                ->setContent('Body text'),
        ]);

    $array = $original->toArray();
    $restored = ComponentDeserializer::fromArray($array);

    $html = $restored->toHtml();

    expect($html)->toContain('Title')
        ->toContain('Body text')
        ->toContain('class="card"');
});

it('serializes themed component to array', function () {
    $button = ComponentFactory::make(ComponentEnum::BUTTON)
        ->setContent('Styled')
        ->setTheme('action', 'success');

    $array = $button->toArray();

    expect($array)->toBeArray()
        ->toHaveKey('theme')
        ->and($array['theme'])->toHaveKey('themes')
        ->and($array['theme']['themes'])->toBeArray();
});

it('restores themed component from array', function () {
    $original = ComponentFactory::make(ComponentEnum::BUTTON)
        ->setContent('Styled')
        ->setTheme('action', 'success');

    $array = $original->toArray();
    $restored = ComponentDeserializer::fromArray($array);

    $html = $restored->toHtml();

    expect($html)->toContain('Styled');
});

it('serializes component with multiple attributes', function () {
    $div = ComponentFactory::make(ComponentEnum::DIV)
        ->setAttributes([
            'id' => 'test',
            'class' => 'container',
            'data-value' => '123',
            'aria-label' => 'Test div',
        ]);

    $array = $div->toArray();

    expect($array['attributes'])->toHaveKey('id')
        ->toHaveKey('class')
        ->toHaveKey('data-value')
        ->toHaveKey('aria-label');
});

it('restores component with multiple attributes from array', function () {
    $original = ComponentFactory::make(ComponentEnum::DIV)
        ->setAttributes([
            'id' => 'test',
            'class' => 'container',
            'data-value' => '123',
            'aria-label' => 'Test div',
        ]);

    $array = $original->toArray();
    $restored = ComponentDeserializer::fromArray($array);

    $html = $restored->toHtml();

    expect($html)->toContain('id="test"')
        ->toContain('class="container"')
        ->toContain('data-value="123"')
        ->toContain('aria-label="Test div"');
});

it('serializes deeply nested components', function () {
    $deep = ComponentFactory::make(ComponentEnum::DIV)
        ->setContent(
            ComponentFactory::make(ComponentEnum::DIV)
                ->setContent(
                    ComponentFactory::make(ComponentEnum::SPAN)
                        ->setContent('Deep')
                )
        );

    $array = $deep->toArray();

    expect($array)->toBeArray()
        ->toHaveKey('contents');
});

it('restores deeply nested components', function () {
    $original = ComponentFactory::make(ComponentEnum::DIV)
        ->setContent(
            ComponentFactory::make(ComponentEnum::DIV)
                ->setContent(
                    ComponentFactory::make(ComponentEnum::SPAN)
                        ->setContent('Deep')
                )
        );

    $array = $original->toArray();
    $restored = ComponentDeserializer::fromArray($array);

    $html = $restored->toHtml();

    expect($html)->toContain('Deep');
});

it('serializes component with empty content', function () {
    $div = ComponentFactory::make(ComponentEnum::DIV);

    $array = $div->toArray();

    expect($array)->toBeArray()
        ->toHaveKey('name')
        ->toHaveKey('attributes')
        ->toHaveKey('contents');
});

it('restores component with empty content', function () {
    $original = ComponentFactory::make(ComponentEnum::DIV);

    $array = $original->toArray();
    $restored = ComponentDeserializer::fromArray($array);

    $html = $restored->toHtml();

    expect($html)->toContain('<div')
        ->toContain('</div>');
});

it('serializes component with multiple themes', function () {
    $button = ComponentFactory::make(ComponentEnum::BUTTON)
        ->setContent('Multi-theme')
        ->setThemes([
            'action' => 'success',
            'size' => 'lg',
        ]);

    $array = $button->toArray();

    expect($array)->toHaveKey('theme')
        ->and($array['theme'])->toHaveKey('themes')
        ->and($array['theme']['themes'])->toBeArray();
});

it('restores component with multiple themes', function () {
    $original = ComponentFactory::make(ComponentEnum::BUTTON)
        ->setContent('Multi-theme')
        ->setThemes([
            'action' => 'success',
            'size' => 'lg',
        ]);

    $array = $original->toArray();
    $restored = ComponentDeserializer::fromArray($array);

    $html = $restored->toHtml();

    expect($html)->toContain('Multi-theme');
});

it('serializes table component', function () {
    $table = ComponentFactory::make(ComponentEnum::TABLE)
        ->setContents([
            ComponentFactory::make(ComponentEnum::THEAD)
                ->setContent(
                    ComponentFactory::make(ComponentEnum::TR)
                        ->setContent(
                            ComponentFactory::make(ComponentEnum::TH)->setContent('Name')
                        )
                ),
        ]);

    $array = $table->toArray();

    expect($array)->toBeArray()
        ->toHaveKey('contents');
});

it('restores table component', function () {
    $original = ComponentFactory::make(ComponentEnum::TABLE)
        ->setContents([
            ComponentFactory::make(ComponentEnum::THEAD)
                ->setContent(
                    ComponentFactory::make(ComponentEnum::TR)
                        ->setContent(
                            ComponentFactory::make(ComponentEnum::TH)->setContent('Name')
                        )
                ),
        ]);

    $array = $original->toArray();
    $restored = ComponentDeserializer::fromArray($array);

    $html = $restored->toHtml();

    expect($html)->toContain('Name');
});

it('serializes form component', function () {
    $form = ComponentFactory::make(ComponentEnum::FORM)
        ->setAttribute('action', '/submit')
        ->setAttribute('method', 'POST')
        ->setContents([
            ComponentFactory::make(ComponentEnum::TEXT_INPUT)
                ->setAttribute('name', 'email'),
        ]);

    $array = $form->toArray();

    expect($array)->toBeArray()
        ->toHaveKey('attributes')
        ->toHaveKey('contents')
        ->and($array['attributes']['action'])->toBe('/submit')
        ->and($array['attributes']['method'])->toBe('POST');
});

it('restores form component', function () {
    $original = ComponentFactory::make(ComponentEnum::FORM)
        ->setAttribute('action', '/submit')
        ->setAttribute('method', 'POST')
        ->setContents([
            ComponentFactory::make(ComponentEnum::TEXT_INPUT)
                ->setAttribute('name', 'email'),
        ]);

    $array = $original->toArray();
    $restored = ComponentDeserializer::fromArray($array);

    $html = $restored->toHtml();

    expect($html)->toContain('action="/submit"')
        ->toContain('method="POST"')
        ->toContain('name="email"');
});

it('serializes DivComponent to array', function () {
    $div = DivComponent::make()
        ->setContent('Hello')
        ->setAttribute('id', 'test');

    $array = $div->toArray();

    expect($array)->toBeArray()
        ->toHaveKey('name')
        ->toHaveKey('attributes')
        ->toHaveKey('contents')
        ->and($array['name'])->toBe('div')
        ->and($array['attributes']['id'])->toBe('test');
});

it('restores DivComponent from array', function () {
    $original = DivComponent::make()
        ->setContent('Hello')
        ->setAttribute('id', 'test');

    $html = $original->toHtml();

    expect($html)->toContain('Hello')
        ->toContain('id="test"');
});
