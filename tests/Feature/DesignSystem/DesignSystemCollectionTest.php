<?php

declare(strict_types=1);

use App\DesignSystem\Contracts\DesignComponent;
use App\DesignSystem\DesignRegistry;
use App\DesignSystem\DesignSystemCollection;
use Juaniquillo\BaseBackendComponent\Contracts\BackendComponent;

it('builds the gallery boxes against the given design', function () {
    $boxes = DesignSystemCollection::boxes(DesignRegistry::get('base'));

    expect(array_column($boxes, 'title'))->toBe([
        'Buttons',
        'Badges',
        'Cards',
        'Inputs',
        'Choices',
        'Select and Textarea',
        'Fields',
        'Form',
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

    foreach (['Buttons', 'Badges', 'Cards', 'Inputs', 'Fields', 'Alerts'] as $title) {
        $response->assertSee($title, false);
    }

    // Labels live inside the field components rather than in a box of
    // their own.
    $response->assertDontSee('<h2 class="component-box-h2">Labels</h2>', false);
});

it('uses a backend component where a box needs a finished element', function () {
    $cards = DesignSystemCollection::boxes(DesignRegistry::get('base'))[2]['components'];

    expect($cards[0]->component())->toBeInstanceOf(BackendComponent::class);
});
