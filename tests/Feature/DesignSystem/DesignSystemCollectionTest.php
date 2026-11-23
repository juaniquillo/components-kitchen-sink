<?php

declare(strict_types=1);

use App\Components\ContainerOptions;
use App\DesignSystem\DesignSystemCollection;
use Juaniquillo\BackendDesignSystem\Contracts\DesignComponent;
use Juaniquillo\BackendDesignSystem\Designs\Design;
use Juaniquillo\BaseBackendComponent\Contracts\BackendComponent;

it('builds the gallery boxes against the given design', function () {
    $boxes = DesignSystemCollection::boxes(app(Design::class));

    expect(array_column($boxes, 'title'))->toBe([
        'Buttons',
        'Badges',
        'Cards',
        'Typography',
        'Layout',
        'Inputs',
        'Choices',
        'Select and Textarea',
        'Fields',
        'Form',
        'Disclosure',
        'Navigation',
        'Alerts',
    ]);

    foreach ($boxes as $box) {
        expect($box['components'])->not->toBeEmpty();

        foreach ($box['components'] as $component) {
            expect($component)->toBeInstanceOf(DesignComponent::class);
        }
    }
});

it('renders every box on the page', function () {
    $response = $this->get('/design-system');

    foreach (['Buttons', 'Badges', 'Cards', 'Typography', 'Layout', 'Inputs', 'Fields', 'Disclosure', 'Navigation', 'Alerts'] as $title) {
        $response->assertSee($title, false);
    }

    // Labels live inside the field components rather than in a box of
    // their own.
    $response->assertDontSee('<h2 class="component-box-h2">Labels</h2>', false);
});

it('uses a backend component where a box needs a finished element', function () {
    $cards = [];

    foreach (DesignSystemCollection::boxes(app(Design::class)) as $box) {
        if ($box['title'] === 'Cards') {
            $cards = $box['components'];
        }
    }

    expect($cards)->not->toBeEmpty()
        ->and($cards[0]->component())->toBeInstanceOf(BackendComponent::class);
});

it('gives every box its own container options', function () {
    $boxes = collect(DesignSystemCollection::boxes(app(Design::class)))->keyBy('title');

    foreach ($boxes as $box) {
        expect($box['options'])->toBeInstanceOf(ContainerOptions::class);
    }

    expect($boxes['Buttons']['options']->disableFlex)->toBeFalse()
        ->and($boxes['Buttons']['options']->flexColumn)->toBeFalse()
        ->and($boxes['Typography']['options']->disableFlex)->toBeTrue()
        ->and($boxes['Disclosure']['options']->flexColumn)->toBeTrue()
        ->and($boxes['Disclosure']['options']->justifyCenter)->toBeFalse();
});

it('renders the gallery in every design', function () {
    // One class per design proves the gallery reaches the requested theme;
    // the package owns the component-level assertions.
    $expectations = [
        'base' => 'bg-zinc-900',
        'base-dark' => 'bg-zinc-100',
        'base-dual' => 'dark:bg-zinc-100',
        'soft' => 'bg-indigo-600',
    ];

    foreach ($expectations as $design => $token) {
        $html = $this->get('/design-system?design='.$design)
            ->assertOk()
            ->getContent();

        $dom = new DOMDocument;
        @$dom->loadHTML('<!DOCTYPE html><html><body>'.$html.'</body></html>');

        $xpath = new DOMXPath($dom);

        $button = $xpath
            ->query('//*[contains(concat(" ", normalize-space(@class), " "), " design-system ")]//button')
            ->item(0);

        expect($button)->toBeInstanceOf(DOMElement::class, "The {$design} gallery has no button.")
            ->and($button->getAttribute('class'))->toContain($token);
    }
});
