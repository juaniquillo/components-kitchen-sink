<?php

declare(strict_types=1);

use App\Builder\Contracts\BuilderNode;
use App\Builder\Support\WorkspaceRepository;

it('shows the theme switch as labelled icon buttons', function () {
    $html = $this->get('/')->assertOk()->getContent();

    expect($html)
        ->toContain('data-appearance-group')
        ->toContain('data-appearance="light"')
        ->toContain('data-appearance="system"')
        ->toContain('data-appearance="dark"')
        ->toContain('aria-label="Light"')
        ->toContain('aria-label="Auto"')
        ->toContain('aria-label="Dark"')
        ->toContain('nav-icon--sun')
        ->toContain('nav-icon--auto')
        ->toContain('nav-icon--moon')
        ->toContain('theme-switch__button');
});

it('shows the theme switch on the canvas dashboard', function () {
    $html = $this->get('/design-system/dashboard')->assertOk()->getContent();

    expect($html)->toContain('data-appearance-group');
});

it('shows the theme switch on the canvas landing page', function () {
    $html = $this->get('/design-system/landing')->assertOk()->getContent();

    expect($html)->toContain('data-appearance-group');
});

it('shows the theme switch on a page with replaced assets', function () {
    // The Bootstrap group replaces app.css with its own bundle; the switch
    // ships its own entries so it renders there too.
    $html = $this->get('/components/bootstrap')->assertOk()->getContent();

    expect($html)
        ->toContain('data-appearance-group')
        ->toContain('data-appearance="system"');
});

it('keeps the switch identical on every design', function () {
    // The toggle is page chrome, not a design component: the classic and
    // soft buttons must render byte-identical classes.
    $classic = $this->get('/design-system/dashboard?design=classic')->assertOk()->getContent();
    $soft = $this->get('/design-system/dashboard?design=soft')->assertOk()->getContent();

    $classes = function (string $html): array {
        $dom = new DOMDocument;
        @$dom->loadHTML('<!DOCTYPE html><html><body>'.$html.'</body></html>');

        $xpath = new DOMXPath($dom);
        $found = [];

        foreach ($xpath->query('//*[@data-appearance]') as $button) {
            $found[] = $button->getAttribute('class');
        }

        return $found;
    };

    $classicButtons = $classes($classic);
    $softButtons = $classes($soft);

    expect($classicButtons)->toHaveCount(3)
        ->and($softButtons)->toBe($classicButtons)
        ->and($classicButtons[0])->toContain('theme-switch__button');
});

it('hides the theme switch where its script cannot load', function () {
    // The builder disables local assets, so the toggle script never runs
    // there and the buttons stay out of the nav.
    $id = (new WorkspaceRepository)->create(
        WorkspaceRepository::DEFAULT_NAME,
        BuilderNode::tree('n1', 'div'),
    );

    $html = $this->get('/builder/'.$id)->assertOk()->getContent();

    expect($html)->not->toContain('data-appearance-group');
});
