<?php

declare(strict_types=1);

use App\Builder\Registries\ComponentRegistry;
use Juaniquillo\BackendComponents\Enums\ComponentEnum;

it('only offers components the package knows about', function () {
    $known = array_map(
        static fn (ComponentEnum $case): string => $case->value,
        ComponentEnum::cases(),
    );

    foreach (ComponentRegistry::all() as $type => $descriptor) {
        expect($type)->toBe($descriptor->type)
            ->and($known)->toContain($descriptor->type);
    }
});

it('maps a component type to its enum case name', function () {
    expect(ComponentRegistry::enumCase('headers.h1'))->toBe('H1')
        ->and(ComponentRegistry::enumCase('inline.button'))->toBe('BUTTON')
        ->and(ComponentRegistry::enumCase('form.text'))->toBe('TEXT_INPUT')
        ->and(ComponentRegistry::enumCase('nope.nope'))->toBeNull();
});

it('groups every component for the palette', function () {
    $groups = ComponentRegistry::groups();

    expect($groups)->toHaveKey(ComponentRegistry::GROUP_LAYOUT)
        ->toHaveKey(ComponentRegistry::GROUP_TYPOGRAPHY)
        ->toHaveKey(ComponentRegistry::GROUP_FORM);

    $total = array_sum(array_map('count', $groups));

    expect($total)->toBe(count(ComponentRegistry::all()));
});

it('only allows list items inside a list', function () {
    $list = ComponentRegistry::get('lists.ul');

    expect($list->allowsChild('lists.li'))->toBeTrue()
        ->and($list->allowsChild('div'))->toBeFalse()
        ->and($list->allowsChild('paragraph'))->toBeFalse();
});

it('keeps list items out of a div', function () {
    // A list item only belongs in a list, and the browser would hoist it
    // straight out of a div if it were allowed in.
    expect(ComponentRegistry::get('div')->allowsChild('lists.li'))->toBeFalse()
        ->and(ComponentRegistry::get('div')->allowsChild('lists.ul'))->toBeTrue();
});

it('only allows rows inside a table body and cells inside a row', function () {
    expect(ComponentRegistry::get('table.tbody')->allowsChild('table.tr'))->toBeTrue()
        ->and(ComponentRegistry::get('table.tbody')->allowsChild('table.td'))->toBeFalse()
        ->and(ComponentRegistry::get('table.tr')->allowsChild('table.td'))->toBeTrue()
        ->and(ComponentRegistry::get('table.tr')->allowsChild('table.th'))->toBeTrue()
        ->and(ComponentRegistry::get('table.tr')->allowsChild('div'))->toBeFalse();
});

it('keeps block elements out of paragraphs and headings', function () {
    expect(ComponentRegistry::get('paragraph')->allowsChild('div'))->toBeFalse()
        ->and(ComponentRegistry::get('paragraph')->allowsChild('paragraph'))->toBeFalse()
        ->and(ComponentRegistry::get('paragraph')->allowsChild('inline.strong'))->toBeTrue()
        ->and(ComponentRegistry::get('headers.h1')->allowsChild('inline.em'))->toBeTrue();
});

it('lets a paragraph hold a link', function () {
    // A paragraph takes phrasing content, so a link belongs inside one.
    expect(ComponentRegistry::get('paragraph')->allowsChild('inline.link'))->toBeTrue()
        ->and(ComponentRegistry::get('paragraph')->acceptsChildren())->toBeTrue()
        ->and(ComponentRegistry::get('paragraph')->acceptsText())->toBeTrue();
});

it('keeps the elements that only take text from holding children', function () {
    expect(ComponentRegistry::get('form.option')->acceptsChildren())->toBeFalse()
        ->and(ComponentRegistry::get('form.textarea')->acceptsChildren())->toBeFalse()
        ->and(ComponentRegistry::get('inline.img')->acceptsChildren())->toBeFalse()
        ->and(ComponentRegistry::get('form.text')->acceptsChildren())->toBeFalse();
});

it('keeps a void element from holding anything', function () {
    expect(ComponentRegistry::get('inline.img')->allowsChild('div'))->toBeFalse()
        ->and(ComponentRegistry::get('form.text')->allowsChild('paragraph'))->toBeFalse();
});

it('allows block elements inside a div', function () {
    $div = ComponentRegistry::get('div');

    expect($div->allowsChild('paragraph'))->toBeTrue()
        ->and($div->allowsChild('headers.h2'))->toBeTrue()
        ->and($div->allowsChild('lists.ul'))->toBeTrue();
});

it('keeps an element from holding one of its own kind', function () {
    // A button cannot hold a button, and a link cannot hold a link: the
    // browser takes the inner one out of the outer one, so the tree the
    // builder holds would no longer be the tree in the page.
    expect(ComponentRegistry::get('inline.button')->allowsChild('inline.button'))->toBeFalse()
        ->and(ComponentRegistry::get('inline.link')->allowsChild('inline.link'))->toBeFalse();
});

it('still lets a button hold other phrasing content', function () {
    expect(ComponentRegistry::get('inline.button')->allowsChild('inline.span'))->toBeTrue()
        ->and(ComponentRegistry::get('inline.button')->allowsChild('inline.link'))->toBeTrue()
        ->and(ComponentRegistry::get('inline.link')->allowsChild('inline.span'))->toBeTrue();
});

it('suggests the attributes that suit a component', function () {
    $div = ComponentRegistry::get('div');
    $link = ComponentRegistry::get('inline.link');

    expect($div->suggestedAttributes)->toContain('id', 'class')
        ->and($link->suggestedAttributes)->toContain('href', 'target');
});

it('tells a text element apart from a container', function () {
    expect(ComponentRegistry::isTextual('paragraph'))->toBeTrue()
        ->and(ComponentRegistry::isTextual('headers.h1'))->toBeTrue()
        ->and(ComponentRegistry::isTextual('inline.link'))->toBeTrue()
        ->and(ComponentRegistry::isTextual('form.label'))->toBeTrue()
        ->and(ComponentRegistry::isTextual('div'))->toBeFalse()
        ->and(ComponentRegistry::isTextual('lists.ul'))->toBeFalse()
        ->and(ComponentRegistry::isTextual('form.text'))->toBeFalse();
});

it('reports what each component accepts', function () {
    expect(ComponentRegistry::get('paragraph')->acceptsText())->toBeTrue()
        ->and(ComponentRegistry::get('paragraph')->acceptsChildren())->toBeTrue()
        ->and(ComponentRegistry::get('div')->acceptsText())->toBeTrue()
        ->and(ComponentRegistry::get('div')->acceptsChildren())->toBeTrue()
        ->and(ComponentRegistry::get('inline.img')->acceptsText())->toBeFalse()
        ->and(ComponentRegistry::get('inline.img')->acceptsChildren())->toBeFalse();
});
