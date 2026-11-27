<?php

declare(strict_types=1);

use App\DesignSystem\Boxes\Contracts\Box;
use App\DesignSystem\DesignSystemCollection;
use Juaniquillo\BackendDesignSystem\Designs\Design;

it('serves the dashboard in the classic design by default', function () {
    $html = $this->get('/design-system/dashboard')->assertOk()->getContent();

    expect($html)
        ->toContain('Overview')
        ->toContain('Total revenue')
        ->toContain('3 invoices are overdue')
        ->toContain('Invite a teammate')
        ->toContain('Back to the gallery')
        ->toContain('bg-slate-900');
});

it('renders the dashboard in every design', function () {
    // One class per design proves the dashboard reaches the requested theme.
    $expectations = [
        'base' => 'bg-zinc-900',
        'base-dark' => 'bg-zinc-100',
        'base-dual' => 'dark:bg-zinc-100',
        'classic' => 'bg-slate-900',
        'soft' => 'bg-indigo-600',
    ];

    foreach ($expectations as $design => $token) {
        $html = $this->get('/design-system/dashboard?design='.$design)
            ->assertOk()
            ->getContent();

        expect($html)->toContain($token);
    }
});

it('404s on an unknown dashboard design', function () {
    $this->get('/design-system/dashboard?design=nope')->assertNotFound();
});

it('charts revenue with accessible SVGs', function () {
    $html = $this->get('/design-system/dashboard')->assertOk()->getContent();

    $dom = new DOMDocument;
    @$dom->loadHTML('<!DOCTYPE html><html><body>'.$html.'</body></html>');

    $xpath = new DOMXPath($dom);

    // The bar chart is a labelled image: twelve bars over a baseline.
    $chart = $xpath->query('//svg[@role="img"]')->item(0);

    expect($chart)->toBeInstanceOf(DOMElement::class)
        ->and($chart->getAttribute('aria-label'))->toContain('Revenue')
        ->and($xpath->query('.//rect', $chart)->length)->toBe(12)
        ->and($xpath->query('.//line', $chart)->length)->toBe(1);

    // The sparkline decorates the KPI number, so it hides from assistive tech.
    $sparkline = $xpath->query('//svg[@aria-hidden="true"]')->item(0);

    expect($sparkline)->toBeInstanceOf(DOMElement::class)
        ->and($xpath->query('.//polyline', $sparkline)->length)->toBe(1);

    // Both charts inherit the card colour; nothing hardcodes a hex fill.
    expect($html)
        ->toContain('fill="currentColor"')
        ->toContain('+18.2% vs last year');
});

it('wires the invoice tabs as a real tablist', function () {
    $html = $this->get('/design-system/dashboard')->assertOk()->getContent();

    $dom = new DOMDocument;
    @$dom->loadHTML('<!DOCTYPE html><html><body>'.$html.'</body></html>');

    $xpath = new DOMXPath($dom);

    $tablist = $xpath->query('//*[@role="tablist"][@aria-label="Invoice filters"]');

    expect($tablist->length)->toBe(1);

    $tabs = $xpath->query('//*[@role="tab"]');
    $panels = $xpath->query('//*[@role="tabpanel"]');

    expect($tabs->length)->toBe(3)
        ->and($panels->length)->toBe(3);

    // Every tab controls a panel that exists, and Alpine owns the state.
    $ids = [];

    foreach ($panels as $panel) {
        $ids[] = $panel->getAttribute('id');
    }

    foreach ($tabs as $tab) {
        expect($ids)->toContain($tab->getAttribute('aria-controls'));
    }

    expect($html)
        ->toContain('x-data')
        ->toContain('x-show')
        ->toContain('x-bind:aria-selected')
        ->toContain('invoice-panel-paid')
        ->toContain('INV-1042');
});

it('styles the tablist from the active design', function () {
    foreach (['base', 'base-dual', 'classic', 'soft'] as $key) {
        $html = $this->get('/design-system/dashboard?design='.$key)->assertOk()->getContent();

        $dom = new DOMDocument;
        @$dom->loadHTML('<!DOCTYPE html><html><body>'.$html.'</body></html>');

        $xpath = new DOMXPath($dom);
        $tablist = $xpath->query('//*[@role="tablist"]')->item(0);

        expect($tablist)->toBeInstanceOf(DOMElement::class);

        // The container's bottom rule and the list's flex row: without the
        // design's theme manager the strip renders bare and stacks.
        expect($tablist->getAttribute('class'))->toContain('border-b');

        $list = $xpath->query('//*[@role="tablist"]/ul')->item(0);

        expect($list)->toBeInstanceOf(DOMElement::class)
            ->and($list->getAttribute('class'))->toContain('flex');
    }
});

it('gives classic its own canvas', function () {
    $classic = $this->get('/design-system/dashboard')->assertOk()->getContent();

    expect($classic)->toContain('<body class="font-sans antialiased bg-slate-100 dark:bg-slate-900');

    $soft = $this->get('/design-system/dashboard?design=soft')->assertOk()->getContent();

    expect($soft)->toContain('<body class="font-sans antialiased bg-zinc-100 dark:bg-zinc-900');

    $gallery = $this->get('/design-system?design=classic')->assertOk()->getContent();

    expect($gallery)->toContain('<div class="bg-slate-100 dark:bg-slate-900 p-sm">');

    $base = $this->get('/design-system')->assertOk()->getContent();

    expect($base)->toContain('component-box-bg');
});

it('renders every box example in every design', function () {
    $boxes = DesignSystemCollection::boxes();

    $withExamples = array_filter($boxes, fn (Box $box): bool => $box->examples() !== []);

    expect($withExamples)->toHaveCount(6);

    foreach (['base', 'base-dual', 'classic', 'soft'] as $key) {
        config(['backend-design-system.default' => $key]);
        app()->forgetInstance(Design::class);

        $design = app(Design::class);

        foreach ($boxes as $box) {
            foreach ($box->examples() as $example) {
                expect($example->component($design)->toHtml())->not->toBe('');
            }
        }
    }
});
