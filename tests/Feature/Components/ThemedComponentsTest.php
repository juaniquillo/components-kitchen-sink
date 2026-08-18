<?php

declare(strict_types=1);

use Illuminate\View\ViewException;
use Juaniquillo\BackendComponents\Builders\ComponentBuilder;
use Juaniquillo\BackendComponents\Enums\ComponentEnum;

it('applies a single theme variant to a button', function () {
    $button = ComponentBuilder::make(ComponentEnum::BUTTON)
        ->setContent('Submit')
        ->setTheme('action', 'success');

    $html = (string) $this->blade('{{ $button }}', ['button' => $button]);

    expect($html)->toContain('<button')
        ->toContain('Submit')
        ->toContain('class="');
});

it('applies multiple theme variants from the same theme file', function () {
    $button = ComponentBuilder::make(ComponentEnum::BUTTON)
        ->setContent('Submit')
        ->setTheme('table', ['th', 'th-dark']);

    $html = (string) $this->blade('{{ $button }}', ['button' => $button]);

    expect($html)->toContain('<button')
        ->toContain('Submit');
});

it('applies multiple themes to a button', function () {
    $button = ComponentBuilder::make(ComponentEnum::BUTTON)
        ->setContent('Submit')
        ->setThemes([
            'action' => 'success',
            'size' => 'lg',
        ]);

    $html = (string) $this->blade('{{ $button }}', ['button' => $button]);

    expect($html)->toContain('<button')
        ->toContain('Submit');
});

it('applies theme to a div component', function () {
    $div = ComponentBuilder::make(ComponentEnum::DIV)
        ->setContent('Themed content')
        ->setTheme('modal', 'default');

    $html = (string) $this->blade('{{ $div }}', ['div' => $div]);

    expect($html)->toContain('<div')
        ->toContain('Themed content');
});

it('applies theme to a heading', function () {
    $h1 = ComponentBuilder::make(ComponentEnum::H1)
        ->setContent('Styled Title')
        ->setTheme('text', 'lg');

    $html = (string) $this->blade('{{ $h1 }}', ['h1' => $h1]);

    expect($html)->toContain('<h1')
        ->toContain('Styled Title');
});

it('applies theme to a paragraph', function () {
    $p = ComponentBuilder::make(ComponentEnum::PARAGRAPH)
        ->setContent('Styled paragraph text')
        ->setTheme('text', 'sm');

    $html = (string) $this->blade('{{ $p }}', ['p' => $p]);

    expect($html)->toContain('<p')
        ->toContain('Styled paragraph text');
});

it('applies theme to an input', function () {
    $input = ComponentBuilder::make(ComponentEnum::TEXT_INPUT)
        ->setAttribute('name', 'email')
        ->setTheme('inputs', 'text');

    $html = (string) $this->blade('{{ $input }}', ['input' => $input]);

    expect($html)->toContain('<input')
        ->toContain('name="email"');
});

it('applies theme to a link', function () {
    $link = ComponentBuilder::make(ComponentEnum::LINK)
        ->setContent('Styled link')
        ->setAttribute('href', '/test')
        ->setTheme('action', 'link');

    $html = (string) $this->blade('{{ $link }}', ['link' => $link]);

    expect($html)->toContain('<a')
        ->toContain('Styled link')
        ->toContain('href="/test"');
});

it('applies theme to a table', function () {
    $table = ComponentBuilder::make(ComponentEnum::TABLE)
        ->setContents([
            ComponentBuilder::make(ComponentEnum::THEAD)
                ->setContent(
                    ComponentBuilder::make(ComponentEnum::TR)
                        ->setContent(
                            ComponentBuilder::make(ComponentEnum::TH)
                                ->setContent('Header')
                                ->setTheme('table', 'th')
                        )
                ),
        ]);

    $html = (string) $this->blade('{{ $table }}', ['table' => $table]);

    expect($html)->toContain('<table')
        ->toContain('Header');
});

it('applies theme to nested components', function () {
    $card = ComponentBuilder::make(ComponentEnum::DIV)
        ->setTheme('modal', 'default')
        ->setContents([
            ComponentBuilder::make(ComponentEnum::H2)
                ->setContent('Card Title')
                ->setTheme('text', 'lg'),
            ComponentBuilder::make(ComponentEnum::PARAGRAPH)
                ->setContent('Card body text')
                ->setTheme('text', 'sm'),
        ]);

    $html = (string) $this->blade('{{ $card }}', ['card' => $card]);

    expect($html)->toContain('<div')
        ->toContain('Card Title')
        ->toContain('Card body text');
});

it('applies theme to button with existing class', function () {
    $button = ComponentBuilder::make(ComponentEnum::BUTTON)
        ->setContent('Submit')
        ->setAttribute('class', 'existing-class')
        ->setTheme('action', 'success');

    $html = (string) $this->blade('{{ $button }}', ['button' => $button]);

    expect($html)->toContain('<button')
        ->toContain('existing-class')
        ->toContain('Submit');
});

it('applies theme to div with existing attributes', function () {
    $div = ComponentBuilder::make(ComponentEnum::DIV)
        ->setContent('Content')
        ->setAttribute('id', 'my-div')
        ->setAttribute('data-testid', 'content')
        ->setTheme('modal', 'default');

    $html = (string) $this->blade('{{ $div }}', ['div' => $div]);

    expect($html)->toContain('id="my-div"')
        ->toContain('data-testid="content"')
        ->toContain('Content');
});

it('applies multiple themes to a div', function () {
    $div = ComponentBuilder::make(ComponentEnum::DIV)
        ->setContent('Multi-themed content')
        ->setThemes([
            'modal' => 'default',
            'size' => 'lg',
        ]);

    $html = (string) $this->blade('{{ $div }}', ['div' => $div]);

    expect($html)->toContain('<div')
        ->toContain('Multi-themed content');
});

it('applies theme to a form', function () {
    $form = ComponentBuilder::make(ComponentEnum::FORM)
        ->setAttribute('action', '/submit')
        ->setTheme('display', 'flex');

    $html = (string) $this->blade('{{ $form }}', ['form' => $form]);

    expect($html)->toContain('<form')
        ->toContain('action="/submit"');
});

it('applies theme to a select', function () {
    $select = ComponentBuilder::make(ComponentEnum::SELECT)
        ->setAttribute('name', 'choice')
        ->setTheme('display', 'block');

    $html = (string) $this->blade('{{ $select }}', ['select' => $select]);

    expect($html)->toContain('<select')
        ->toContain('name="choice"');
});

it('applies theme to a textarea', function () {
    $textarea = ComponentBuilder::make(ComponentEnum::TEXTAREA)
        ->setAttribute('name', 'message')
        ->setTheme('inputs', 'textarea');

    $html = (string) $this->blade('{{ $textarea }}', ['textarea' => $textarea]);

    expect($html)->toContain('<textarea')
        ->toContain('name="message"');
});

it('renders component without theme', function () {
    $button = ComponentBuilder::make(ComponentEnum::BUTTON)
        ->setContent('No theme');

    $html = (string) $this->blade('{{ $button }}', ['button' => $button]);

    expect($html)->toContain('<button')
        ->toContain('No theme');
});

it('handles non-existent theme gracefully', function () {
    $this->expectException(ViewException::class);

    ComponentBuilder::make(ComponentEnum::BUTTON)
        ->setContent('Test')
        ->setTheme('nonexistent', 'variant')
        ->toHtml();
});

it('applies theme to a label', function () {
    $label = ComponentBuilder::make(ComponentEnum::LABEL)
        ->setAttribute('for', 'email')
        ->setContent('Email')
        ->setTheme('color', 'default');

    $html = (string) $this->blade('{{ $label }}', ['label' => $label]);

    expect($html)->toContain('<label')
        ->toContain('Email')
        ->toContain('for="email"');
});

it('applies theme to a list item', function () {
    $li = ComponentBuilder::make(ComponentEnum::LI)
        ->setContent('List item')
        ->setTheme('lists', 'disc');

    $html = (string) $this->blade('{{ $li }}', ['li' => $li]);

    expect($html)->toContain('<li')
        ->toContain('List item');
});

it('applies theme to a detail element', function () {
    $details = ComponentBuilder::make(ComponentEnum::DETAILS)
        ->setContents([
            ComponentBuilder::make(ComponentEnum::SUMMARY)
                ->setContent('Summary')
                ->setTheme('display', 'block'),
            ComponentBuilder::make(ComponentEnum::DIV)
                ->setContent('Details content'),
        ]);

    $html = (string) $this->blade('{{ $details }}', ['details' => $details]);

    expect($html)->toContain('<details')
        ->toContain('Summary')
        ->toContain('Details content');
});
