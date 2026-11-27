<?php

declare(strict_types=1);

it('serves the landing page with gallery and theme chrome only', function () {
    $html = $this->get('/design-system/landing')->assertOk()->getContent();

    $dom = new DOMDocument;
    @$dom->loadHTML('<!DOCTYPE html><html><body>'.$html.'</body></html>');

    $xpath = new DOMXPath($dom);

    // One H1: the hero owns the page.
    $headings = $xpath->query('//h1');

    expect($headings->length)->toBe(1)
        ->and(trim($headings->item(0)->textContent))->toBe('Invoices that pay themselves');

    // A promotion page: no nav, no switchers, no sidebar.
    expect($xpath->query('//nav')->length)->toBe(0)
        ->and($xpath->query('//aside')->length)->toBe(0);

    expect($html)->not->toContain('DesignSwitcher');

    // The shared header: a way back, a labelled design switcher and
    // the theme switch. No navigation bar.
    expect($html)
        ->toContain('← Gallery')
        ->toContain(route('design-system', ['design' => 'classic']))
        ->toContain('>Design<')
        ->toContain('data-appearance-group')
        ->toContain('data-appearance="light"')
        ->toContain('data-appearance="system"')
        ->toContain('data-appearance="dark"')
        ->toContain('theme-switch__button');

    // Every section renders.
    expect($html)
        ->toContain('Acme Studio 2.0 is live')
        ->toContain('Trusted by finance teams')
        ->toContain('Chases itself')
        ->toContain('Reconciles itself')
        ->toContain('Reports itself')
        ->toContain('Pricing that bills itself')
        ->toContain('Frequently asked questions')
        ->toContain('Get product updates')
        ->toContain('All rights reserved');
});

it('renders the landing page in every design', function () {
    foreach (['base', 'base-dark', 'base-dual', 'classic', 'soft'] as $design) {
        $html = $this->get('/design-system/landing?design='.$design)->assertOk()->getContent();

        expect($html)->toContain('Invoices that pay themselves');
    }

    $this->get('/design-system/landing?design=nope')->assertNotFound();
});

it('links out to the main website exactly twice', function () {
    $html = $this->get('/design-system/landing')->assertOk()->getContent();

    $dom = new DOMDocument;
    @$dom->loadHTML('<!DOCTYPE html><html><body>'.$html.'</body></html>');

    $xpath = new DOMXPath($dom);

    $outbound = $xpath->query('//a[@href="https://example.com"]');

    expect($outbound->length)->toBe(2);

    foreach ($outbound as $link) {
        expect($link->getAttribute('target'))->toBe('_blank')
            ->and($link->getAttribute('rel'))->toBe('noopener noreferrer');
    }

    // The primary CTA stays inside the app: a button navigating home.
    expect($html)->toContain(route('design-system.dashboard', ['design' => 'classic']));
});

it('previews the product with the revenue chart', function () {
    $html = $this->get('/design-system/landing')->assertOk()->getContent();

    $dom = new DOMDocument;
    @$dom->loadHTML('<!DOCTYPE html><html><body>'.$html.'</body></html>');

    $xpath = new DOMXPath($dom);

    $chart = $xpath->query('//svg[@role="img"][@aria-label="Revenue by month, January to December"]');

    expect($chart->length)->toBe(1);

    expect($html)->toContain('nav-icon--bolt')
        ->toContain('nav-icon--shield')
        ->toContain('nav-icon--layers');
});

it('toggles pricing between monthly and yearly', function () {
    $html = $this->get('/design-system/landing')->assertOk()->getContent();

    expect($html)
        ->toContain('x-data')
        ->toContain('yearly = true')
        ->toContain('x-bind:aria-pressed')
        ->toContain("x-text=\"yearly ? '$240/yr' : '$24/mo'\"")
        ->toContain('Starter')
        ->toContain('Team')
        ->toContain('Enterprise')
        ->toContain('Popular');
});

it('answers five questions with one open', function () {
    $html = $this->get('/design-system/landing')->assertOk()->getContent();

    $dom = new DOMDocument;
    @$dom->loadHTML('<!DOCTYPE html><html><body>'.$html.'</body></html>');

    $xpath = new DOMXPath($dom);

    expect($xpath->query('//details')->length)->toBe(5)
        ->and($xpath->query('//details[@open]')->length)->toBe(1)
        ->and($xpath->query('//details//summary')->length)->toBe(5);
});

it('subscribes without a 405', function () {
    $html = $this->get('/design-system/landing')->assertOk()->getContent();

    expect($html)
        ->toContain(route('design-system.landing.subscribe'))
        ->toContain('name="_token"')
        ->toContain('name="newsletter-email"');

    $this->post(route('design-system.landing.subscribe'), [
        'newsletter-email' => 'sam@example.com',
    ])
        ->assertRedirect()
        ->assertSessionHas('landing-notice', 'You are on the list. See you Thursday.');

    $this->post(route('design-system.landing.subscribe'), [
        'newsletter-email' => 'not-an-email',
    ])->assertSessionHasErrors('newsletter-email');
});

it('shows the subscribe flash after a demo submit', function () {
    $this
        ->post(route('design-system.landing.subscribe'), [
            'newsletter-email' => 'sam@example.com',
        ])
        ->assertRedirect();

    $html = $this->get('/design-system/landing')->assertOk()->getContent();

    expect($html)->toContain('You are on the list. See you Thursday.');
});

it('links the landing page from the gallery', function () {
    $html = $this->get('/design-system')->assertOk()->getContent();

    expect($html)->toContain(route('design-system.landing', ['design' => 'base']));
});
