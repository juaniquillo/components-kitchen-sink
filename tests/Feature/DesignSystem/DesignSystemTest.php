<?php

declare(strict_types=1);

it('serves the design system page', function () {
    $response = $this->get('/design-system');

    $response->assertOk()
        ->assertSee('Design System', false)
        ->assertSee('Buttons', false)
        ->assertSee('Base Dark', false);
});

it('lists the bundled designs and marks the requested one', function () {
    $html = $this->get('/design-system?design=base-dual')->getContent();

    $dom = new DOMDocument;
    @$dom->loadHTML('<!DOCTYPE html><html><body>'.$html.'</body></html>');

    $xpath = new DOMXPath($dom);

    $labels = [];

    foreach ($xpath->query('//a[contains(@href, "design=")]') as $link) {
        $labels[] = trim($link->textContent);
    }

    expect($labels)->toBe(['Base', 'Base Dark', 'Base Dual']);

    $active = $xpath->query('//a[contains(@href, "design=base-dual")]')->item(0);

    expect($active)->toBeInstanceOf(DOMElement::class)
        ->and($active->getAttribute('class'))->toContain('bg-zinc-900');
});

it('switches designs through the query string', function () {
    $this->get('/design-system?design=base')->assertOk();
    $this->get('/design-system?design=base-dark')->assertOk();
    $this->get('/design-system?design=base-dual')->assertOk();
});

it('rejects unknown designs', function () {
    $this->get('/design-system?design=nope')->assertNotFound();
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
