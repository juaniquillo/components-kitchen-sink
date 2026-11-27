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
        ->toContain('← Gallery')
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

    // The bar chart is a labelled image: twelve grouped columns over a baseline.
    $chart = $xpath->query('//svg[@role="img"]')->item(0);

    expect($chart)->toBeInstanceOf(DOMElement::class)
        ->and($chart->getAttribute('aria-label'))->toContain('Revenue')
        ->and($xpath->query('.//rect', $chart)->length)->toBe(24)
        ->and($xpath->query('.//line', $chart)->length)->toBe(1);

    $columns = $xpath->query('.//g[@class="group"]', $chart);

    expect($columns->length)->toBe(12);

    foreach ($columns as $column) {
        expect($xpath->query('.//rect', $column)->length)->toBe(2)
            ->and($xpath->query('.//text', $column)->length)->toBe(1);
    }

    // Hovering a column lifts the bar and the label.
    expect($html)
        ->toContain('group-hover:brightness-110')
        ->toContain('group-hover:opacity-100');

    // The sparkline decorates the KPI number, so it hides from assistive tech.
    $sparkline = $xpath->query('//svg[@aria-hidden="true"]')->item(0);

    expect($sparkline)->toBeInstanceOf(DOMElement::class)
        ->and($xpath->query('.//polyline', $sparkline)->length)->toBe(1);

    // Both charts inherit the card colour; nothing hardcodes a hex fill.
    expect($html)
        ->toContain('fill="currentColor"')
        ->toContain('+18.2% vs last year');
});

it('paints charts from the design chart theme', function () {
    $classic = $this->get('/design-system/dashboard?design=classic')->assertOk()->getContent();

    expect($classic)
        ->toContain('fill-blue-600')
        ->toContain('stroke-blue-600')
        ->toContain('fill-blue-200')
        ->toContain('stroke-slate-300');

    $soft = $this->get('/design-system/dashboard?design=soft')->assertOk()->getContent();

    expect($soft)
        ->toContain('fill-indigo-600')
        ->toContain('stroke-indigo-600');

    // Bundled designs ship no chart theme, so the charts fall back to
    // the card's text colour.
    $dual = $this->get('/design-system/dashboard?design=base-dual')->assertOk()->getContent();

    expect($dual)
        ->toContain('fill-current')
        ->not->toContain('fill-blue-600')
        ->not->toContain('fill-indigo-600');
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

    expect($tabs->length)->toBe(4)
        ->and($panels->length)->toBe(4);

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

it('reads as an app, not a showcase page', function () {
    $html = $this->get('/design-system/dashboard')->assertOk()->getContent();

    $dom = new DOMDocument;
    @$dom->loadHTML('<!DOCTYPE html><html><body>'.$html.'</body></html>');

    $xpath = new DOMXPath($dom);

    // One H1: the showcase heading is gone, Overview is the page.
    $headings = $xpath->query('//h1');

    expect($headings->length)->toBe(1)
        ->and(trim($headings->item(0)->textContent))->toBe('Overview');

    // The sidebar marks its section and the top strip links home.
    $current = $xpath->query('//aside//a[@aria-current="page"]')->item(0);

    expect($current)->toBeInstanceOf(DOMElement::class)
        ->and(trim($current->textContent))->toBe('Overview')
        ->and($xpath->query('//a[contains(@href, "design-system?design=")]')->length)->toBeGreaterThan(0)
        ->and($xpath->query('//aside//a//span[contains(@class, "nav-icon--overview")]')->length)->toBe(1)
        ->and($xpath->query('//aside//a//span[contains(@class, "nav-icon--settings")]')->length)->toBe(1);

    // Full width capped at 6xl and left-aligned; the aside becomes a
    // native dialog sheet on mobile.
    expect($html)
        ->toContain('<div class="px-4 py-6"')
        ->toContain('max-w-6xl')
        ->toContain('id="dashboard-menu"')
        ->toContain('aria-label="Menu"')
        ->toContain('aria-haspopup="dialog"')
        ->toContain('aria-controls="dashboard-menu"')
        ->toContain('nav-icon--menu')
        ->toContain('nav-icon--close')
        ->toContain('dashboard-menu-button');

    $menu = $xpath->query('//dialog[@id="dashboard-menu"]')->item(0);

    expect($menu)->toBeInstanceOf(DOMElement::class);

    $close = $xpath->query('//dialog[@id="dashboard-menu"]//button[@aria-label="Close menu"]')->item(0);

    expect($close)->toBeInstanceOf(DOMElement::class);

    $aside = $xpath->query('//aside')->item(0);

    expect($aside)->toBeInstanceOf(DOMElement::class)
        ->and($aside->getAttribute('class'))->toContain('hidden lg:block');
});

it('builds the shell from design links and labels each switcher', function () {
    $html = $this->get('/design-system/dashboard')->assertOk()->getContent();

    $dom = new DOMDocument;
    @$dom->loadHTML('<!DOCTYPE html><html><body>'.$html.'</body></html>');

    $xpath = new DOMXPath($dom);

    // The sidebar follows the active design: plain links, no pills.
    foreach ($xpath->query('//aside//a') as $link) {
        expect($link->getAttribute('class'))->not->toContain('rounded-full');
    }

    // Design and theme switchers are labelled and no longer adjacent.
    expect($html)
        ->toContain('>Design<')
        ->toContain('>Theme<');

    // Grid columns can shrink, so the table wrapper scrolls instead of
    // the page growing.
    expect($html)
        ->toContain('<div class="min-w-0">')
        ->toContain('<div class="min-w-0 lg:col-span-2">');
});

it('opens the invoice dialog without script', function () {
    $html = $this->get('/design-system/dashboard')->assertOk()->getContent();

    $dom = new DOMDocument;
    @$dom->loadHTML('<!DOCTYPE html><html><body>'.$html.'</body></html>');

    $xpath = new DOMXPath($dom);

    $dialog = $xpath->query('//dialog[@id="new-invoice"]')->item(0);

    expect($dialog)->toBeInstanceOf(DOMElement::class)
        ->and($dialog->hasAttribute('open'))->toBeFalse();

    $open = $xpath->query('//button[@command="show-modal"][@commandfor="new-invoice"]')->item(0);

    expect($open)->toBeInstanceOf(DOMElement::class)
        ->and(trim($open->textContent))->toBe('New invoice');

    $close = $xpath->query('//button[@command="close"][@commandfor="new-invoice"]')->item(0);

    expect($close)->toBeInstanceOf(DOMElement::class)
        ->and(trim($close->textContent))->toBe('Cancel');

    $create = $xpath->query('//button[@type="submit"][@form="new-invoice-form"]')->item(0);

    expect($create)->toBeInstanceOf(DOMElement::class)
        ->and(trim($create->textContent))->toBe('Create invoice')
        ->and($xpath->query('//form[@id="new-invoice-form"]//input[@name="invoice-client"]')->length)->toBe(1);
});

it('equips the invoices card with a toolbar and pagination', function () {
    $html = $this->get('/design-system/dashboard')->assertOk()->getContent();

    $dom = new DOMDocument;
    @$dom->loadHTML('<!DOCTYPE html><html><body>'.$html.'</body></html>');

    $xpath = new DOMXPath($dom);

    expect($xpath->query('//input[@name="invoice-search"]')->length)->toBe(1)
        ->and($xpath->query('//select[@name="invoice-status"]')->length)->toBe(1)
        ->and($xpath->query('//a[contains(text(), "View all invoices")]')->length)->toBe(1);

    expect($html)
        ->toContain('Showing 1–3 of 42')
        ->toContain('aria-label="Page 2"');
});

it('covers keyboard tabs, empty states and bar tooltips', function () {
    $html = $this->get('/design-system/dashboard')->assertOk()->getContent();

    $dom = new DOMDocument;
    @$dom->loadHTML('<!DOCTYPE html><html><body>'.$html.'</body></html>');

    $xpath = new DOMXPath($dom);

    expect($xpath->query('//*[@role="tab"]')->length)->toBe(4)
        ->and($xpath->query('//*[@role="tabpanel"]')->length)->toBe(4)
        ->and($xpath->query('//*[@role="tabpanel"]')->item(3)->textContent)->toContain('No draft invoices.');

    expect($html)
        ->toContain('x-on:keydown.arrow-right')
        ->toContain('x-on:keydown.home')
        ->toContain('January · €4.1k')
        ->toContain("\$el.closest('dialog')?.close()");
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

it('sends invites and creates invoices without a 405', function () {
    $html = $this->get('/design-system/dashboard')->assertOk()->getContent();

    expect($html)
        ->toContain(route('design-system.dashboard.invite'))
        ->toContain(route('design-system.dashboard.invoice'))
        ->toContain('name="_token"');

    $this->post(route('design-system.dashboard.invite'), [
        'invite-email' => 'sam@example.com',
        'invite-role' => 'member',
    ])
        ->assertRedirect()
        ->assertSessionHas('dashboard-notice', 'Invite sent to sam@example.com.');

    $this->post(route('design-system.dashboard.invoice'), [
        'invoice-client' => 'Acme',
        'invoice-amount' => '120.50',
    ])
        ->assertRedirect()
        ->assertSessionHas('dashboard-notice', 'Invoice for Acme created.');

    $this->post(route('design-system.dashboard.invite'), [
        'invite-email' => 'not-an-email',
        'invite-role' => 'member',
    ])->assertSessionHasErrors('invite-email');
});

it('shows the dashboard flash after a demo submit', function () {
    $this
        ->post(route('design-system.dashboard.invite'), [
            'invite-email' => 'sam@example.com',
            'invite-role' => 'member',
        ])
        ->assertRedirect();

    $html = $this->get('/design-system/dashboard')->assertOk()->getContent();

    expect($html)->toContain('Invite sent to sam@example.com.');
});
