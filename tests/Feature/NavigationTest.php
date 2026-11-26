<?php

declare(strict_types=1);

it('marks the current section in the site nav', function () {
    $home = $this->get('/')->assertOk()->getContent();

    $dom = new DOMDocument;
    @$dom->loadHTML('<!DOCTYPE html><html><body>'.$home.'</body></html>');

    $xpath = new DOMXPath($dom);
    $current = $xpath->query('//nav//a[@aria-current="page"]');

    expect($current->length)->toBe(1)
        ->and(trim($current->item(0)->textContent))->toBe('Home');

    // A section page marks its own entry: the bootstrap group lives under
    // /components, so Components stays current there.
    $group = $this->get('/components/bootstrap')->assertOk()->getContent();

    $dom = new DOMDocument;
    @$dom->loadHTML('<!DOCTYPE html><html><body>'.$group.'</body></html>');

    $xpath = new DOMXPath($dom);
    $current = $xpath->query('//nav//a[@aria-current="page"]');

    expect($current->length)->toBe(1)
        ->and(trim($current->item(0)->textContent))->toBe('Components');
});

it('shares one pill across nav, chips and toggle', function () {
    $html = $this->get('/design-system')->assertOk()->getContent();

    expect($html)
        ->toContain('rounded-full')
        ->toContain('aria-current="page"')
        ->toContain('data-appearance-group')
        ->not->toContain('text-blue-500 underline');
});

it('paints the site chrome zinc', function () {
    $html = $this->get('/')->assertOk()->getContent();

    expect($html)->toContain('<body class="font-sans antialiased bg-zinc-200 dark:bg-zinc-900');
});
