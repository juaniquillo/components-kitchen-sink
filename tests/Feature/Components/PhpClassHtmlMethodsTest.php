<?php

declare(strict_types=1);

use Juaniquillo\BackendComponents\Components\Individual\DivComponent;

it('creates a div component with static make method', function () {
    $div = DivComponent::make();

    expect($div)->toBeInstanceOf(DivComponent::class);
});

it('sets content on div component', function () {
    $div = DivComponent::make()->setContent('Hello');

    $html = (string) $this->blade('{{ $div }}', ['div' => $div]);

    expect($html)->toContain('Hello')
        ->toContain('<div');
});

it('sets attributes on div component', function () {
    $div = DivComponent::make()
        ->setAttribute('id', 'my-div')
        ->setAttribute('class', 'container');

    $html = (string) $this->blade('{{ $div }}', ['div' => $div]);

    expect($html)->toContain('id="my-div"')
        ->toContain('class="container"');
});

it('sets multiple contents on div component', function () {
    $div = DivComponent::make()
        ->setContents([
            'header' => DivComponent::make()->setContent('Header'),
            'body' => DivComponent::make()->setContent('Body'),
        ]);

    $html = (string) $this->blade('{{ $div }}', ['div' => $div]);

    expect($html)->toContain('Header')
        ->toContain('Body');
});

it('renders nested div components', function () {
    $inner = DivComponent::make()->setContent('Inner content');
    $outer = DivComponent::make()
        ->setAttribute('class', 'outer')
        ->setContent($inner);

    $html = (string) $this->blade('{{ $outer }}', ['outer' => $outer]);

    expect($html)->toContain('class="outer"')
        ->toContain('Inner content')
        ->toContain('<div');
});

it('converts div component to html string', function () {
    $div = DivComponent::make()->setContent('Test');

    $html = $div->toHtml();

    expect($html)->toContain('<div')
        ->toContain('Test')
        ->toContain('</div>');
});

it('converts div component to array', function () {
    $div = DivComponent::make()
        ->setContent('Hello')
        ->setAttribute('id', 'test');

    $array = $div->toArray();

    expect($array)->toBeArray()
        ->toHaveKey('name')
        ->toHaveKey('attributes')
        ->toHaveKey('contents');
});

it('gets component name', function () {
    $div = DivComponent::make();

    $name = $div->getName();

    expect($name)->toBe('div');
});

it('sets theme on div component', function () {
    $div = DivComponent::make()
        ->setTheme('action', 'success');

    $html = (string) $this->blade('{{ $div }}', ['div' => $div]);

    expect($html)->toContain('<div');
});

it('sets multiple themes on div component', function () {
    $div = DivComponent::make()
        ->setThemes([
            'action' => 'success',
            'size' => 'lg',
        ]);

    $html = (string) $this->blade('{{ $div }}', ['div' => $div]);

    expect($html)->toContain('<div');
});

it('creates div with attribute bag', function () {
    $div = DivComponent::make()
        ->setAttributes([
            'data-testid' => 'content',
            'aria-label' => 'Main content',
        ]);

    $html = (string) $this->blade('{{ $div }}', ['div' => $div]);

    expect($html)->toContain('data-testid="content"')
        ->toContain('aria-label="Main content"');
});

it('renders empty div when no content set', function () {
    $div = DivComponent::make();

    $html = (string) $this->blade('{{ $div }}', ['div' => $div]);

    expect($html)->toContain('<div')
        ->toContain('</div>');
});

it('handles special characters in content', function () {
    $div = DivComponent::make()
        ->setContent('Hello & "World" <test>');

    $html = (string) $this->blade('{{ $div }}', ['div' => $div]);

    expect($html)->toContain('Hello &amp; &quot;World&quot; &lt;test&gt;');
});

it('handles numeric content', function () {
    $div = DivComponent::make()
        ->setContent('42');

    $html = (string) $this->blade('{{ $div }}', ['div' => $div]);

    expect($html)->toContain('42');
});

it('handles boolean content', function () {
    $div = DivComponent::make()
        ->setContent('true');

    $html = (string) $this->blade('{{ $div }}', ['div' => $div]);

    expect($html)->toContain('true');
});

it('overrides attribute when set twice', function () {
    $div = DivComponent::make()
        ->setAttribute('class', 'first')
        ->setAttribute('class', 'second');

    $html = (string) $this->blade('{{ $div }}', ['div' => $div]);

    expect($html)->toContain('class="second"')
        ->not->toContain('class="first"');
});

it('merges multiple setAttributes calls', function () {
    $div = DivComponent::make()
        ->setAttributes(['id' => 'test', 'class' => 'base'])
        ->setAttributes(['data-value' => '123']);

    $html = (string) $this->blade('{{ $div }}', ['div' => $div]);

    expect($html)->toContain('id="test"')
        ->toContain('class="base"')
        ->toContain('data-value="123"');
});
