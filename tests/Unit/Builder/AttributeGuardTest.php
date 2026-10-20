<?php

declare(strict_types=1);

use App\Builder\Support\AttributeGuard;

it('allows the ordinary attribute names', function () {
    expect(AttributeGuard::allows('id'))->toBeTrue()
        ->and(AttributeGuard::allows('class'))->toBeTrue()
        ->and(AttributeGuard::allows('aria-label'))->toBeTrue()
        ->and(AttributeGuard::allows('data-testid'))->toBeTrue()
        ->and(AttributeGuard::allows('href'))->toBeTrue()
        ->and(AttributeGuard::allows('itemprop'))->toBeTrue()
        ->and(AttributeGuard::allows('xml:lang'))->toBeTrue();
});

it('refuses an event handler', function () {
    expect(AttributeGuard::allows('onclick'))->toBeFalse()
        ->and(AttributeGuard::allows('onerror'))->toBeFalse()
        ->and(AttributeGuard::allows('ONLOAD'))->toBeFalse();
});

it('refuses an inline style', function () {
    expect(AttributeGuard::allows('style'))->toBeFalse();
});

it('refuses a livewire directive, which would be a way in', function () {
    expect(AttributeGuard::allows('wire:click'))->toBeFalse()
        ->and(AttributeGuard::allows('wire:model'))->toBeFalse();
});

it('refuses an alpine directive', function () {
    expect(AttributeGuard::allows('x-data'))->toBeFalse()
        ->and(AttributeGuard::allows('x-html'))->toBeFalse()
        ->and(AttributeGuard::allows('x-on:click'))->toBeFalse();
});

it('refuses the builders own attributes', function () {
    expect(AttributeGuard::allows('data-node-id'))->toBeFalse()
        ->and(AttributeGuard::allows('data-selected'))->toBeFalse();
});

it('refuses a name that could break out of an expression', function () {
    // The name is interpolated into a Livewire expression, so a quote or a
    // bracket in it would let the value run past the expression.
    expect(AttributeGuard::allows("foo'bar"))->toBeFalse()
        ->and(AttributeGuard::allows('foo bar'))->toBeFalse()
        ->and(AttributeGuard::allows('foo=bar'))->toBeFalse()
        ->and(AttributeGuard::allows('foo)bar'))->toBeFalse()
        ->and(AttributeGuard::allows('foo<bar'))->toBeFalse()
        ->and(AttributeGuard::allows('1foo'))->toBeFalse();
});

it('refuses an empty or absurdly long name', function () {
    expect(AttributeGuard::allows(''))->toBeFalse()
        ->and(AttributeGuard::allows(str_repeat('a', 200)))->toBeFalse();
});

it('explains why a name was refused', function () {
    expect(AttributeGuard::reason('id'))->toBeNull()
        ->and(AttributeGuard::reason('onclick'))->not->toBeNull()
        ->and(AttributeGuard::reason('style'))->not->toBeNull()
        ->and(AttributeGuard::reason(''))->not->toBeNull();
});
