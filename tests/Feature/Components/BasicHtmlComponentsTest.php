<?php

declare(strict_types=1);

use Juaniquillo\BackendComponents\Builders\ComponentBuilder;
use Juaniquillo\BackendComponents\Enums\ComponentEnum;

it('renders a button component', function () {
    $button = ComponentBuilder::make(ComponentEnum::BUTTON)
        ->setContent('Click me');

    $html = (string) $this->blade('{{ $button }}', ['button' => $button]);

    expect($html)->toContain('<button')
        ->toContain('Click me')
        ->toContain('</button>');
});

it('renders a div component with content', function () {
    $div = ComponentBuilder::make(ComponentEnum::DIV)
        ->setContent('Hello World');

    $html = (string) $this->blade('{{ $div }}', ['div' => $div]);

    expect($html)->toContain('<div')
        ->toContain('Hello World')
        ->toContain('</div>');
});

it('renders a paragraph component', function () {
    $paragraph = ComponentBuilder::make(ComponentEnum::PARAGRAPH)
        ->setContent('This is a paragraph');

    $html = (string) $this->blade('{{ $paragraph }}', ['paragraph' => $paragraph]);

    expect($html)->toContain('<p')
        ->toContain('This is a paragraph')
        ->toContain('</p>');
});

it('renders a heading component', function () {
    $h1 = ComponentBuilder::make(ComponentEnum::H1)
        ->setContent('Main Title');

    $html = (string) $this->blade('{{ $h1 }}', ['h1' => $h1]);

    expect($html)->toContain('<h1')
        ->toContain('Main Title')
        ->toContain('</h1>');
});

it('renders a span component', function () {
    $span = ComponentBuilder::make(ComponentEnum::SPAN)
        ->setContent('Inline text');

    $html = (string) $this->blade('{{ $span }}', ['span' => $span]);

    expect($html)->toContain('<span')
        ->toContain('Inline text')
        ->toContain('</span>');
});

it('renders a link component', function () {
    $link = ComponentBuilder::make(ComponentEnum::LINK)
        ->setContent('Click here')
        ->setAttribute('href', 'https://example.com');

    $html = (string) $this->blade('{{ $link }}', ['link' => $link]);

    expect($html)->toContain('<a')
        ->toContain('href="https://example.com"')
        ->toContain('Click here')
        ->toContain('</a>');
});

it('renders an image component', function () {
    $img = ComponentBuilder::make(ComponentEnum::IMG)
        ->setAttribute('src', 'image.jpg')
        ->setAttribute('alt', 'Test image');

    $html = (string) $this->blade('{{ $img }}', ['img' => $img]);

    expect($html)->toContain('<img')
        ->toContain('src="image.jpg"')
        ->toContain('alt="Test image"')
        ->toContain('/>');
});

it('renders a bold component', function () {
    $bold = ComponentBuilder::make(ComponentEnum::BOLD)
        ->setContent('Bold text');

    $html = (string) $this->blade('{{ $bold }}', ['bold' => $bold]);

    expect($html)->toContain('<b')
        ->toContain('Bold text')
        ->toContain('</b>');
});

it('renders an italic component', function () {
    $italic = ComponentBuilder::make(ComponentEnum::ITALIC)
        ->setContent('Italic text');

    $html = (string) $this->blade('{{ $italic }}', ['italic' => $italic]);

    expect($html)->toContain('<i')
        ->toContain('Italic text')
        ->toContain('</i>');
});

it('renders a small component', function () {
    $small = ComponentBuilder::make(ComponentEnum::SMALL)
        ->setContent('Small text');

    $html = (string) $this->blade('{{ $small }}', ['small' => $small]);

    expect($html)->toContain('<small')
        ->toContain('Small text')
        ->toContain('</small>');
});

it('renders a text input component', function () {
    $input = ComponentBuilder::make(ComponentEnum::TEXT_INPUT)
        ->setAttribute('name', 'username')
        ->setAttribute('type', 'text');

    $html = (string) $this->blade('{{ $input }}', ['input' => $input]);

    expect($html)->toContain('<input')
        ->toContain('name="username"')
        ->toContain('type="text"')
        ->toContain('/>');
});

it('renders a textarea component', function () {
    $textarea = ComponentBuilder::make(ComponentEnum::TEXTAREA)
        ->setAttribute('name', 'message')
        ->setContent('Default text');

    $html = (string) $this->blade('{{ $textarea }}', ['textarea' => $textarea]);

    expect($html)->toContain('<textarea')
        ->toContain('name="message"')
        ->toContain('Default text')
        ->toContain('</textarea>');
});

it('renders a select component', function () {
    $select = ComponentBuilder::make(ComponentEnum::SELECT)
        ->setAttribute('name', 'choice');

    $html = (string) $this->blade('{{ $select }}', ['select' => $select]);

    expect($html)->toContain('<select')
        ->toContain('name="choice"')
        ->toContain('</select>');
});

it('renders a checkbox input', function () {
    $checkbox = ComponentBuilder::make(ComponentEnum::CHECKBOX_INPUT)
        ->setAttribute('name', 'agree')
        ->setAttribute('value', '1');

    $html = (string) $this->blade('{{ $checkbox }}', ['checkbox' => $checkbox]);

    expect($html)->toContain('<input')
        ->toContain('type="checkbox"')
        ->toContain('name="agree"')
        ->toContain('value="1"');
});

it('renders a radio input', function () {
    $radio = ComponentBuilder::make(ComponentEnum::RADIO_INPUT)
        ->setAttribute('name', 'option')
        ->setAttribute('value', 'yes');

    $html = (string) $this->blade('{{ $radio }}', ['radio' => $radio]);

    expect($html)->toContain('<input')
        ->toContain('type="radio"')
        ->toContain('name="option"')
        ->toContain('value="yes"');
});

it('renders a form component', function () {
    $form = ComponentBuilder::make(ComponentEnum::FORM)
        ->setAttribute('action', '/submit')
        ->setAttribute('method', 'POST');

    $html = (string) $this->blade('{{ $form }}', ['form' => $form]);

    expect($html)->toContain('<form')
        ->toContain('action="/submit"')
        ->toContain('method="POST"')
        ->toContain('</form>');
});

it('renders an unordered list', function () {
    $ul = ComponentBuilder::make(ComponentEnum::UL)
        ->setContents([
            ComponentBuilder::make(ComponentEnum::LI)->setContent('Item 1'),
            ComponentBuilder::make(ComponentEnum::LI)->setContent('Item 2'),
        ]);

    $html = (string) $this->blade('{{ $ul }}', ['ul' => $ul]);

    expect($html)->toContain('<ul')
        ->toContain('<li')
        ->toContain('Item 1')
        ->toContain('Item 2')
        ->toContain('</ul>');
});

it('renders an ordered list', function () {
    $ol = ComponentBuilder::make(ComponentEnum::OL)
        ->setContents([
            ComponentBuilder::make(ComponentEnum::LI)->setContent('First'),
            ComponentBuilder::make(ComponentEnum::LI)->setContent('Second'),
        ]);

    $html = (string) $this->blade('{{ $ol }}', ['ol' => $ol]);

    expect($html)->toContain('<ol')
        ->toContain('<li')
        ->toContain('First')
        ->toContain('Second')
        ->toContain('</ol>');
});

it('renders a table component', function () {
    $table = ComponentBuilder::make(ComponentEnum::TABLE)
        ->setContents([
            ComponentBuilder::make(ComponentEnum::THEAD)
                ->setContent(
                    ComponentBuilder::make(ComponentEnum::TR)
                        ->setContent(
                            ComponentBuilder::make(ComponentEnum::TH)->setContent('Name')
                        )
                ),
            ComponentBuilder::make(ComponentEnum::TBODY)
                ->setContent(
                    ComponentBuilder::make(ComponentEnum::TR)
                        ->setContent(
                            ComponentBuilder::make(ComponentEnum::TD)->setContent('John')
                        )
                ),
        ]);

    $html = (string) $this->blade('{{ $table }}', ['table' => $table]);

    expect($html)->toContain('<table')
        ->toContain('<thead')
        ->toContain('<tbody')
        ->toContain('<th')
        ->toContain('Name')
        ->toContain('<td')
        ->toContain('John');
});

it('renders a dialog component', function () {
    $dialog = ComponentBuilder::make(ComponentEnum::DIALOG)
        ->setContent('Dialog content');

    $html = (string) $this->blade('{{ $dialog }}', ['dialog' => $dialog]);

    expect($html)->toContain('<dialog')
        ->toContain('Dialog content')
        ->toContain('</dialog>');
});

it('renders a details component', function () {
    $details = ComponentBuilder::make(ComponentEnum::DETAILS)
        ->setContents([
            ComponentBuilder::make(ComponentEnum::SUMMARY)->setContent('Summary'),
            ComponentBuilder::make(ComponentEnum::DIV)->setContent('Details content'),
        ]);

    $html = (string) $this->blade('{{ $details }}', ['details' => $details]);

    expect($html)->toContain('<details')
        ->toContain('<summary')
        ->toContain('Summary')
        ->toContain('Details content')
        ->toContain('</details>');
});

it('renders a fieldset component', function () {
    $fieldset = ComponentBuilder::make(ComponentEnum::FIELDSET)
        ->setContents([
            ComponentBuilder::make(ComponentEnum::LEGEND)->setContent('Personal Info'),
            ComponentBuilder::make(ComponentEnum::TEXT_INPUT)->setAttribute('name', 'name'),
        ]);

    $html = (string) $this->blade('{{ $fieldset }}', ['fieldset' => $fieldset]);

    expect($html)->toContain('<fieldset')
        ->toContain('<legend')
        ->toContain('Personal Info')
        ->toContain('<input')
        ->toContain('name="name"');
});

it('renders a label component', function () {
    $label = ComponentBuilder::make(ComponentEnum::LABEL)
        ->setAttribute('for', 'email')
        ->setContent('Email Address');

    $html = (string) $this->blade('{{ $label }}', ['label' => $label]);

    expect($html)->toContain('<label')
        ->toContain('for="email"')
        ->toContain('Email Address')
        ->toContain('</label>');
});
