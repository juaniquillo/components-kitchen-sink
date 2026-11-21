<?php

declare(strict_types=1);

use App\DesignSystem\Theme\ButtonVariant;
use App\DesignSystem\Theme\Size;
use App\DesignSystem\Theme\State;
use App\DesignSystem\ThemeVariants;

it('accepts enums and prefixes plain strings the same way', function () {
    expect(ThemeVariants::make()->look(ButtonVariant::Primary)->size(Size::Md)->state(State::Disabled)->all())
        ->toBe(['primary', 'size.md', 'state.disabled'])
        ->and(ThemeVariants::make()->look('primary')->size('md')->state('disabled')->all())
        ->toBe(['primary', 'size.md', 'state.disabled']);
});

it('prefixes the size and the state', function () {
    expect(ThemeVariants::make()->look('primary')->size('md')->state('disabled')->all())
        ->toBe(['primary', 'size.md', 'state.disabled']);
});

it('leaves out a null size or state', function () {
    expect(ThemeVariants::make()->look('default')->size(null)->state(null)->all())
        ->toBe(['default']);
});

it('refuses empty names', function () {
    expect(fn () => ThemeVariants::make()->look(''))->toThrow(InvalidArgumentException::class)
        ->and(fn () => ThemeVariants::make()->size(''))->toThrow(InvalidArgumentException::class)
        ->and(fn () => ThemeVariants::make()->state('  '))->toThrow(InvalidArgumentException::class);
});

it('refuses a name that already carries a dimension prefix', function () {
    // The prefix belongs to the helpers, so a doubled one would look for a
    // key no theme file has.
    expect(fn () => ThemeVariants::make()->look('size.md'))->toThrow(InvalidArgumentException::class)
        ->and(fn () => ThemeVariants::make()->size('size.md'))->toThrow(InvalidArgumentException::class)
        ->and(fn () => ThemeVariants::make()->state('state.disabled'))->toThrow(InvalidArgumentException::class);
});
