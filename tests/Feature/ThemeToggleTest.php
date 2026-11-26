<?php

declare(strict_types=1);

use App\Builder\Contracts\BuilderNode;
use App\Builder\Support\WorkspaceRepository;

it('shows the theme switch on a default page', function () {
    $html = $this->get('/')->assertOk()->getContent();

    expect($html)
        ->toContain('data-appearance-group')
        ->toContain('data-appearance="light"')
        ->toContain('data-appearance="system"')
        ->toContain('data-appearance="dark"');
});

it('shows the theme switch on the canvas dashboard', function () {
    $html = $this->get('/design-system/dashboard')->assertOk()->getContent();

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
