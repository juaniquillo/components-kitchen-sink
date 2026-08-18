<?php

declare(strict_types=1);

use Juaniquillo\BackendComponents\Builders\ComponentBuilder;
use Juaniquillo\BackendComponents\Components\Individual\DivComponent;
use Juaniquillo\BackendComponents\Enums\ComponentEnum;

it('composes a card component with header and body', function () {
    $card = ComponentBuilder::make(ComponentEnum::DIV)
        ->setAttribute('class', 'card')
        ->setContents([
            ComponentBuilder::make(ComponentEnum::DIV)
                ->setAttribute('class', 'card-header')
                ->setContent('Card Title'),
            ComponentBuilder::make(ComponentEnum::DIV)
                ->setAttribute('class', 'card-body')
                ->setContent('Card content goes here'),
        ]);

    $html = (string) $this->blade('{{ $card }}', ['card' => $card]);

    expect($html)->toContain('class="card"')
        ->toContain('class="card-header"')
        ->toContain('Card Title')
        ->toContain('class="card-body"')
        ->toContain('Card content goes here');
});

it('composes a form with labels and inputs', function () {
    $form = ComponentBuilder::make(ComponentEnum::FORM)
        ->setAttribute('action', '/submit')
        ->setAttribute('method', 'POST')
        ->setContents([
            ComponentBuilder::make(ComponentEnum::LABEL)
                ->setAttribute('for', 'name')
                ->setContent('Name'),
            ComponentBuilder::make(ComponentEnum::TEXT_INPUT)
                ->setAttribute('name', 'name')
                ->setAttribute('type', 'text')
                ->setAttribute('id', 'name'),
            ComponentBuilder::make(ComponentEnum::LABEL)
                ->setAttribute('for', 'email')
                ->setContent('Email'),
            ComponentBuilder::make(ComponentEnum::EMAIL_INPUT)
                ->setAttribute('name', 'email')
                ->setAttribute('id', 'email'),
            ComponentBuilder::make(ComponentEnum::BUTTON)
                ->setAttribute('type', 'submit')
                ->setContent('Submit'),
        ]);

    $html = (string) $this->blade('{{ $form }}', ['form' => $form]);

    expect($html)->toContain('action="/submit"')
        ->toContain('method="POST"')
        ->toContain('for="name"')
        ->toContain('Name')
        ->toContain('name="name"')
        ->toContain('type="text"')
        ->toContain('for="email"')
        ->toContain('Email')
        ->toContain('type="email"')
        ->toContain('type="submit"')
        ->toContain('Submit');
});

it('composes nested divs for layout', function () {
    $layout = ComponentBuilder::make(ComponentEnum::DIV)
        ->setAttribute('class', 'container')
        ->setContent(
            ComponentBuilder::make(ComponentEnum::DIV)
                ->setAttribute('class', 'row')
                ->setContents([
                    ComponentBuilder::make(ComponentEnum::DIV)
                        ->setAttribute('class', 'col-md-6')
                        ->setContent('Left column'),
                    ComponentBuilder::make(ComponentEnum::DIV)
                        ->setAttribute('class', 'col-md-6')
                        ->setContent('Right column'),
                ])
        );

    $html = (string) $this->blade('{{ $layout }}', ['layout' => $layout]);

    expect($html)->toContain('class="container"')
        ->toContain('class="row"')
        ->toContain('class="col-md-6"')
        ->toContain('Left column')
        ->toContain('Right column');
});

it('composes a navigation menu', function () {
    $nav = ComponentBuilder::make(ComponentEnum::DIV)
        ->setAttribute('class', 'navbar')
        ->setContent(
            ComponentBuilder::make(ComponentEnum::UL)
                ->setAttribute('class', 'nav-list')
                ->setContents([
                    ComponentBuilder::make(ComponentEnum::LI)
                        ->setContent(
                            ComponentBuilder::make(ComponentEnum::LINK)
                                ->setAttribute('href', '/')
                                ->setContent('Home')
                        ),
                    ComponentBuilder::make(ComponentEnum::LI)
                        ->setContent(
                            ComponentBuilder::make(ComponentEnum::LINK)
                                ->setAttribute('href', '/about')
                                ->setContent('About')
                        ),
                    ComponentBuilder::make(ComponentEnum::LI)
                        ->setContent(
                            ComponentBuilder::make(ComponentEnum::LINK)
                                ->setAttribute('href', '/contact')
                                ->setContent('Contact')
                        ),
                ])
        );

    $html = (string) $this->blade('{{ $nav }}', ['nav' => $nav]);

    expect($html)->toContain('class="navbar"')
        ->toContain('class="nav-list"')
        ->toContain('href="/"')
        ->toContain('Home')
        ->toContain('href="/about"')
        ->toContain('About')
        ->toContain('href="/contact"')
        ->toContain('Contact');
});

it('composes a table with header and body', function () {
    $table = ComponentBuilder::make(ComponentEnum::TABLE)
        ->setAttribute('class', 'data-table')
        ->setContents([
            ComponentBuilder::make(ComponentEnum::THEAD)
                ->setContent(
                    ComponentBuilder::make(ComponentEnum::TR)
                        ->setContents([
                            ComponentBuilder::make(ComponentEnum::TH)->setContent('ID'),
                            ComponentBuilder::make(ComponentEnum::TH)->setContent('Name'),
                            ComponentBuilder::make(ComponentEnum::TH)->setContent('Email'),
                        ])
                ),
            ComponentBuilder::make(ComponentEnum::TBODY)
                ->setContents([
                    ComponentBuilder::make(ComponentEnum::TR)
                        ->setContents([
                            ComponentBuilder::make(ComponentEnum::TD)->setContent('1'),
                            ComponentBuilder::make(ComponentEnum::TD)->setContent('John Doe'),
                            ComponentBuilder::make(ComponentEnum::TD)->setContent('john@example.com'),
                        ]),
                    ComponentBuilder::make(ComponentEnum::TR)
                        ->setContents([
                            ComponentBuilder::make(ComponentEnum::TD)->setContent('2'),
                            ComponentBuilder::make(ComponentEnum::TD)->setContent('Jane Smith'),
                            ComponentBuilder::make(ComponentEnum::TD)->setContent('jane@example.com'),
                        ]),
                ]),
        ]);

    $html = (string) $this->blade('{{ $table }}', ['table' => $table]);

    expect($html)->toContain('class="data-table"')
        ->toContain('<thead')
        ->toContain('<tbody')
        ->toContain('ID')
        ->toContain('Name')
        ->toContain('Email')
        ->toContain('1')
        ->toContain('John Doe')
        ->toContain('john@example.com')
        ->toContain('2')
        ->toContain('Jane Smith')
        ->toContain('jane@example.com');
});

it('composes deeply nested components', function () {
    $deep = ComponentBuilder::make(ComponentEnum::DIV)
        ->setContent(
            ComponentBuilder::make(ComponentEnum::DIV)
                ->setContent(
                    ComponentBuilder::make(ComponentEnum::DIV)
                        ->setContent(
                            ComponentBuilder::make(ComponentEnum::SPAN)
                                ->setContent('Deep content')
                        )
                )
        );

    $html = (string) $this->blade('{{ $deep }}', ['deep' => $deep]);

    expect($html)->toContain('Deep content')
        ->toContain('<div')
        ->toContain('<span');
});

it('composes component with mixed content types', function () {
    $mixed = ComponentBuilder::make(ComponentEnum::DIV)
        ->setContents([
            ComponentBuilder::make(ComponentEnum::H1)->setContent('Title'),
            ComponentBuilder::make(ComponentEnum::PARAGRAPH)->setContent('Some text'),
            ComponentBuilder::make(ComponentEnum::BUTTON)->setContent('Click me'),
        ]);

    $html = (string) $this->blade('{{ $mixed }}', ['mixed' => $mixed]);

    expect($html)->toContain('<h1')
        ->toContain('Title')
        ->toContain('<p')
        ->toContain('Some text')
        ->toContain('<button')
        ->toContain('Click me');
});

it('composes a dialog with modal content', function () {
    $dialog = ComponentBuilder::make(ComponentEnum::DIALOG)
        ->setAttribute('id', 'myModal')
        ->setContents([
            ComponentBuilder::make(ComponentEnum::DIV)
                ->setAttribute('class', 'modal-header')
                ->setContent(
                    ComponentBuilder::make(ComponentEnum::H2)
                        ->setContent('Modal Title')
                ),
            ComponentBuilder::make(ComponentEnum::DIV)
                ->setAttribute('class', 'modal-body')
                ->setContent('Modal content here'),
            ComponentBuilder::make(ComponentEnum::DIV)
                ->setAttribute('class', 'modal-footer')
                ->setContent(
                    ComponentBuilder::make(ComponentEnum::BUTTON)
                        ->setContent('Close')
                ),
        ]);

    $html = (string) $this->blade('{{ $dialog }}', ['dialog' => $dialog]);

    expect($html)->toContain('<dialog')
        ->toContain('id="myModal"')
        ->toContain('class="modal-header"')
        ->toContain('Modal Title')
        ->toContain('class="modal-body"')
        ->toContain('Modal content here')
        ->toContain('class="modal-footer"')
        ->toContain('Close');
});

it('composes using DivComponent helper', function () {
    $card = DivComponent::make()
        ->setAttribute('class', 'card')
        ->setContents([
            DivComponent::make()
                ->setContent('Header'),
            DivComponent::make()
                ->setContent('Body'),
        ]);

    $html = (string) $this->blade('{{ $card }}', ['card' => $card]);

    expect($html)->toContain('class="card"')
        ->toContain('Header')
        ->toContain('Body');
});

it('composes fieldset with legend and inputs', function () {
    $fieldset = ComponentBuilder::make(ComponentEnum::FIELDSET)
        ->setContents([
            ComponentBuilder::make(ComponentEnum::LEGEND)
                ->setContent('Personal Information'),
            ComponentBuilder::make(ComponentEnum::LABEL)
                ->setAttribute('for', 'firstname')
                ->setContent('First Name'),
            ComponentBuilder::make(ComponentEnum::TEXT_INPUT)
                ->setAttribute('name', 'firstname')
                ->setAttribute('id', 'firstname'),
            ComponentBuilder::make(ComponentEnum::LABEL)
                ->setAttribute('for', 'lastname')
                ->setContent('Last Name'),
            ComponentBuilder::make(ComponentEnum::TEXT_INPUT)
                ->setAttribute('name', 'lastname')
                ->setAttribute('id', 'lastname'),
        ]);

    $html = (string) $this->blade('{{ $fieldset }}', ['fieldset' => $fieldset]);

    expect($html)->toContain('<fieldset')
        ->toContain('<legend')
        ->toContain('Personal Information')
        ->toContain('for="firstname"')
        ->toContain('First Name')
        ->toContain('name="firstname"')
        ->toContain('for="lastname"')
        ->toContain('Last Name')
        ->toContain('name="lastname"');
});
