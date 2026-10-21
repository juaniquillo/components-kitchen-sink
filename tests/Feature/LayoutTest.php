<?php

declare(strict_types=1);

it('loads a remote script without going through vite', function () {
    $this->blade('<x-layouts.default :remoteAssets="[\'js\' => [\'https://cdn.example.com/tailwind.js\']]" />')
        ->assertSee('<script src="https://cdn.example.com/tailwind.js"></script>', false);
});

it('loads a remote stylesheet as a link', function () {
    $this->blade('<x-layouts.default :remoteAssets="[\'css\' => [\'https://cdn.example.com/theme.css?v=2\']]" />')
        ->assertSee('<link rel="stylesheet" href="https://cdn.example.com/theme.css?v=2" data-navigate-track="reload">', false);
});

it('keeps the local assets with vite', function () {
    // The name of the file is hashed when the assets are built, so only the
    // shape of the tag is checked.
    expect((string) $this->blade('<x-layouts.default />'))
        ->toMatch('/<link rel="stylesheet" href="[^"]*app[^"]*\.css"/');
});

it('leaves the local assets out when they are disabled', function () {
    $this->blade('<x-layouts.default :disable-local-assets="true" />')
        ->assertDontSee('resources/css/app.css')
        ->assertDontSee('@vite/client');
});
