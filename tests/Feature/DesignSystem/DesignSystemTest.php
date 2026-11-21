<?php

declare(strict_types=1);

use App\DesignSystem\Components\Alert;
use App\DesignSystem\Components\Badge;
use App\DesignSystem\Components\Button;
use App\DesignSystem\Components\Card;
use App\DesignSystem\Components\Field;
use App\DesignSystem\Components\Input;
use App\DesignSystem\DarkMode;
use App\DesignSystem\Design;
use App\DesignSystem\DesignRegistry;
use App\DesignSystem\Theme\AlertVariant;
use App\DesignSystem\Theme\BadgeVariant;
use App\DesignSystem\Theme\ButtonVariant;

it('registers the designs', function () {
    expect(DesignRegistry::all())->toHaveKeys(['base', 'base-dark', 'base-dual'])
        ->and(DesignRegistry::get('base'))->toBeInstanceOf(Design::class)
        ->and(DesignRegistry::get('base-dark')->label)->toBe('Base Dark')
        ->and(DesignRegistry::get('base-dual')->label)->toBe('Base Dual')
        ->and(DesignRegistry::get('base')->darkMode)->toBe(DarkMode::None)
        ->and(DesignRegistry::get('base-dark')->darkMode)->toBe(DarkMode::Always)
        ->and(DesignRegistry::get('base-dual')->darkMode)->toBe(DarkMode::Adaptive)
        ->and(DesignRegistry::get('missing'))->toBeNull()
        ->and(DesignRegistry::current()->key)->toBe('base');
});

it('serves the design system page', function () {
    $response = $this->get('/design-system');

    $response->assertOk()
        ->assertSee('Design System', false)
        ->assertSee('Buttons', false)
        ->assertSee('Base Dark', false);
});

it('switches designs through the query string', function () {
    $this->get('/design-system?design=base')->assertOk();
    $this->get('/design-system?design=base-dual')->assertOk();
});

it('rejects unknown designs', function () {
    $this->get('/design-system?design=nope')->assertNotFound();
});

it('renders with the configured design when none is passed', function () {
    config()->set('design-system.default', 'base-dual');

    $html = (string) $this->blade('{{ $button }}', [
        'button' => Button::make()->label('Save')->variant(ButtonVariant::Primary),
    ]);

    expect($html)->toContain('bg-zinc-900')
        ->toContain('dark:bg-zinc-100');
});

it('lets an explicit design beat the configured one', function () {
    config()->set('design-system.default', 'base-dual');

    $html = (string) $this->blade('{{ $button }}', [
        'button' => Button::make(DesignRegistry::get('base'))->label('Save')->variant(ButtonVariant::Primary),
    ]);

    expect($html)->toContain('bg-zinc-900')
        ->not->toContain('dark:bg-zinc-100');
});

it('refuses an unknown configured design', function () {
    config()->set('design-system.default', 'nope');

    expect(fn () => DesignRegistry::current())->toThrow(InvalidArgumentException::class);
});

it('renders components for both modes in the dual design', function () {
    $dual = DesignRegistry::get('base-dual');

    $button = (string) $this->blade('{{ $button }}', [
        'button' => Button::make($dual)->label('Save')->variant(ButtonVariant::Primary),
    ]);

    expect($button)->toContain('bg-zinc-900')
        ->toContain('dark:bg-zinc-100');
});

it('renders every component on dark surfaces in the dark design', function () {
    $dark = DesignRegistry::get('base-dark');

    $button = (string) $this->blade('{{ $button }}', [
        'button' => Button::make($dark)->label('Save')->variant(ButtonVariant::Primary),
    ]);
    $badge = (string) $this->blade('{{ $badge }}', [
        'badge' => Badge::make($dark)->label('Active')->variant(BadgeVariant::Success),
    ]);
    $card = (string) $this->blade('{{ $card }}', [
        'card' => Card::make($dark)->title('Billing')->body('Renews soon.'),
    ]);
    $input = (string) $this->blade('{{ $input }}', [
        'input' => Input::make($dark)->name('email'),
    ]);
    $field = (string) $this->blade('{{ $field }}', [
        'field' => Field::make($dark)->label('Email')->input('email'),
    ]);
    $alert = (string) $this->blade('{{ $alert }}', [
        'alert' => Alert::make($dark)->variant(AlertVariant::Info)->title('Heads up'),
    ]);

    expect($button)->toContain('bg-zinc-100')
        ->and($badge)->toContain('bg-green-950')
        ->and($card)->toContain('bg-zinc-900')
        ->and($input)->toContain('bg-zinc-900')
        ->and($field)->toContain('grid gap-1')
        ->and($field)->toContain('bg-zinc-900')
        ->and($alert)->toContain('bg-blue-950');
});

it('keeps the gallery addressable for assistive technology', function () {
    $html = $this->get('/design-system')->getContent();

    $dom = new DOMDocument;
    @$dom->loadHTML('<!DOCTYPE html><html><body>'.$html.'</body></html>');

    $xpath = new DOMXPath($dom);

    $gallery = $xpath->query('//*[contains(concat(" ", normalize-space(@class), " "), " design-system ")]')->item(0);

    expect($gallery)->toBeInstanceOf(DOMElement::class);

    $ids = [];

    foreach ($xpath->query('.//*[@id]', $gallery) as $element) {
        $ids[] = $element->getAttribute('id');
    }

    expect($ids)->toBe(array_values(array_unique($ids)));

    // A label associates either by for or by wrapping the control.
    foreach ($xpath->query('.//label', $gallery) as $label) {
        $for = $label->getAttribute('for');
        $wraps = $xpath->query('.//input|.//select|.//textarea', $label)->length > 0;

        expect($for !== '' || $wraps)->toBeTrue();

        if ($for !== '') {
            expect($xpath->query('.//*[@id="'.$for.'"]', $gallery)->length)->toBe(1);
        }
    }

    // Every control needs a name: a label pointing at it, a wrapping label,
    // or an aria-label (or aria-labelledby that resolves).
    foreach ($xpath->query('.//input|.//select|.//textarea', $gallery) as $input) {
        $id = $input->getAttribute('id');

        $named = $input->getAttribute('aria-label') !== ''
            || ($id !== ''
                && $xpath->query('.//label[@for="'.$id.'"]', $gallery)->length === 1)
            || $xpath->query('.//*[@id="'.$input->getAttribute('aria-labelledby').'"]', $gallery)->length === 1
            || $xpath->query('ancestor::label', $input)->length === 1;

        expect($named)->toBeTrue();
    }

    // A describedby that points nowhere is the same silent failure as a
    // label without its for.
    foreach ($xpath->query('.//*[@aria-describedby]', $gallery) as $element) {
        foreach (preg_split('/\s+/', trim($element->getAttribute('aria-describedby'))) ?: [] as $token) {
            expect($xpath->query('.//*[@id="'.$token.'"]', $gallery)->length)->toBe(1);
        }
    }
});

it('keeps the base look on disabled inputs in every design', function () {
    $base = (string) $this->blade('{{ $input }}', [
        'input' => Input::make(DesignRegistry::get('base'))->name('nickname')->disabled(),
    ]);
    $dark = (string) $this->blade('{{ $input }}', [
        'input' => Input::make(DesignRegistry::get('base-dark'))->name('nickname')->disabled(),
    ]);
    $dual = (string) $this->blade('{{ $input }}', [
        'input' => Input::make(DesignRegistry::get('base-dual'))->name('nickname')->disabled(),
    ]);

    expect($base)->toContain('opacity-50')
        ->toContain('bg-white')
        ->and($dark)->toContain('opacity-50')
        ->toContain('bg-zinc-900')
        ->and($dual)->toContain('opacity-50')
        ->toContain('bg-white')
        ->toContain('dark:bg-zinc-900');
});
