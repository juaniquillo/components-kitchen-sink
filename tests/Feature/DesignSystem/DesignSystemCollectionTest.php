<?php

declare(strict_types=1);

use App\Components\Options\Enums\Items;
use App\Components\Options\Enums\Justify;
use App\Components\Options\GroupOptions;
use App\DesignSystem\Boxes\Contracts\Box;
use App\DesignSystem\DesignSystemCollection;
use Juaniquillo\BackendDesignSystem\Contracts\DesignComponent;
use Juaniquillo\BackendDesignSystem\Designs\Design;
use Juaniquillo\BaseBackendComponent\Contracts\BackendComponent;

it('builds every gallery box for the given design', function () {
    $design = app(Design::class);
    $boxes = DesignSystemCollection::boxes();

    $names = array_map(fn (Box $box): string => $box::NAME, $boxes);

    expect($names)->toHaveCount(16)
        ->and(array_unique($names))->toHaveCount(16)
        ->and($names)->toEqualCanonicalizing([
            'Buttons',
            'Badges',
            'Dialog',
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
            'Data table',
            'Tabs',
            'Alerts',
        ]);

    foreach ($boxes as $box) {
        expect($box)->toBeInstanceOf(Box::class);

        $components = $box->components($design);

        expect($components)->not->toBeEmpty();

        foreach ($components as $component) {
            expect($component)->toBeInstanceOf(DesignComponent::class);
        }
    }
});

it('renders every box on the page', function () {
    $response = $this->get('/design-system');

    foreach (['Buttons', 'Badges', 'Cards', 'Typography', 'Layout', 'Inputs', 'Fields', 'Disclosure', 'Navigation', 'Data table', 'Tabs', 'Dialog', 'Alerts'] as $title) {
        $response->assertSee($title, false);
    }

    // Labels live inside the field components rather than in a box of
    // their own.
    $response->assertDontSee('<h2 class="component-box-h2">Labels</h2>', false);
});

it('uses a backend component where a box needs a finished element', function () {
    $cards = collect(DesignSystemCollection::boxes())
        ->first(fn (Box $box): bool => $box::NAME === 'Cards')
        ?->components(app(Design::class)) ?? [];

    expect($cards)->not->toBeEmpty()
        ->and($cards[0]->component())->toBeInstanceOf(BackendComponent::class);
});

it('gives every box its own container options', function () {
    $boxes = collect(DesignSystemCollection::boxes())->keyBy(fn (Box $box): string => $box::NAME);

    foreach ($boxes as $box) {
        expect($box->options())->toBeInstanceOf(GroupOptions::class);
    }

    $buttons = $boxes['Buttons']->options();

    expect($buttons->container->disableFlex)->toBeFalse()
        ->and($buttons->container->flexColumn)->toBeFalse()
        ->and($buttons->container->justify)->toBe(Justify::Start)
        ->and($buttons->container->items)->toBe(Items::Center)
        ->and($buttons->box->getCSS())->toBe('p-sm')
        ->and($boxes['Typography']->options()->container->disableFlex)->toBeTrue()
        ->and($boxes['Disclosure']->options()->container->flexColumn)->toBeTrue()
        ->and($boxes['Disclosure']->options()->container->justify)->toBe(Justify::Start)
        ->and($boxes['Disclosure']->options()->container->items)->toBe(Items::Stretch);
});

it('wires the interactive boxes without script', function () {
    $html = $this->get('/design-system?design=soft')->assertOk()->getContent();

    $dom = new DOMDocument;
    @$dom->loadHTML('<!DOCTYPE html><html><body>'.$html.'</body></html>');

    $xpath = new DOMXPath($dom);

    // The dialog starts closed, named by its own title, and the buttons
    // command it natively: show-modal opens, close closes.
    $dialog = $xpath->query('//dialog[@id="billing-dialog"]')->item(0);

    expect($dialog)->toBeInstanceOf(DOMElement::class)
        ->and($dialog->hasAttribute('open'))->toBeFalse();

    $titleId = $dialog->getAttribute('aria-labelledby');

    expect($titleId)->not->toBe('')
        ->and($xpath->query('//*[@id="'.$titleId.'"]')->length)->toBe(1);

    $open = $xpath->query('//button[@command="show-modal"][@commandfor="billing-dialog"]')->item(0);

    expect($open)->toBeInstanceOf(DOMElement::class)
        ->and(trim($open->textContent))->toBe('Open dialog');

    $close = $xpath->query('//button[@command="close"][@commandfor="billing-dialog"]');

    expect($close->length)->toBe(2);

    // Tabs are a labelled navigation with the current item marked.
    $current = $xpath->query('//*[@role="navigation"][@aria-label="Billing sections"]//*[@aria-current="page"]')->item(0);

    expect($current)->toBeInstanceOf(DOMElement::class)
        ->and(trim($current->textContent))->toBe('Overview');

    // Both tables keep their header semantics, and the first has a caption.
    expect($xpath->query('//table//th[@scope="col"]')->length)->toBe(5)
        ->and($xpath->query('//table//caption')->item(0)?->textContent)->toContain('Invoices');
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
