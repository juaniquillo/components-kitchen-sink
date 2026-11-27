<?php

declare(strict_types=1);

function exampleHeader(string $html): DOMElement
{
    $dom = new DOMDocument;
    @$dom->loadHTML('<!DOCTYPE html><html><body>'.$html.'</body></html>');

    $headers = (new DOMXPath($dom))->query('//header[contains(@class, "example-header")]');

    expect($headers->length)->toBe(1);

    $header = $headers->item(0);

    expect($header)->toBeInstanceOf(DOMElement::class);

    return $header;
}

it('shares one header across the examples', function () {
    foreach (['/design-system/dashboard', '/design-system/landing'] as $page) {
        $html = $this->get($page)->assertOk()->getContent();

        $dom = new DOMDocument;
        @$dom->loadHTML('<!DOCTYPE html><html><body>'.$html.'</body></html>');

        $xpath = new DOMXPath($dom);

        exampleHeader($html);

        // Gallery link, labelled design switcher, labelled theme switch.
        expect($html)
            ->toContain('← Gallery')
            ->toContain('>Design<')
            ->toContain('>Theme<')
            ->toContain('data-appearance-group')
            ->and($xpath->query('//header//a[contains(@href, "design-system?design=")]')->length)->toBeGreaterThan(0)
            ->and($xpath->query('//header//*[@data-appearance]')->length)->toBe(3);
    }
});

it('points the landing switcher at the landing page', function () {
    $html = $this->get('/design-system/landing?design=soft')->assertOk()->getContent();

    $dom = new DOMDocument;
    @$dom->loadHTML('<!DOCTYPE html><html><body>'.$html.'</body></html>');

    $xpath = new DOMXPath($dom);

    expect($xpath->query('//header//a[contains(@href, "design-system/landing")]')->length)->toBeGreaterThan(0)
        ->and($xpath->query('//header//a[contains(@href, "design-system/dashboard")]')->length)->toBe(0);
});

it('keeps the example toggle identical to the dashboard toggle', function () {
    $headers = function (string $html): array {
        $dom = new DOMDocument;
        @$dom->loadHTML('<!DOCTYPE html><html><body>'.$html.'</body></html>');

        $xpath = new DOMXPath($dom);
        $found = [];

        foreach ($xpath->query('//header//*[@data-appearance]') as $button) {
            $found[] = $button->getAttribute('class');
        }

        return $found;
    };

    $dashboard = $headers($this->get('/design-system/dashboard')->assertOk()->getContent());
    $landing = $headers($this->get('/design-system/landing')->assertOk()->getContent());

    expect($dashboard)->toHaveCount(3)
        ->and($landing)->toBe($dashboard);
});
