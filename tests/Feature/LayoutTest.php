<?php

declare(strict_types=1);

it('loads a remote script without going through vite', function () {
    $this->blade('<x-layouts.default :additionalAssets="[\'https://cdn.example.com/tailwind.js\']" />')
        ->assertSee('<script src="https://cdn.example.com/tailwind.js"></script>', false);
});

it('loads a remote stylesheet as a link', function () {
    $this->blade('<x-layouts.default :additionalAssets="[\'https://cdn.example.com/theme.css?v=2\']" />')
        ->assertSee('<link rel="stylesheet" href="https://cdn.example.com/theme.css?v=2">', false);
});

it('keeps the local assets with vite', function () {
    $this->blade('<x-layouts.default />')
        ->assertSee('app.css');
});
