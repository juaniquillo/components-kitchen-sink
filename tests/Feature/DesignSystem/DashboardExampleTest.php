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

it('gives classic its own canvas', function () {
    $classic = $this->get('/design-system/dashboard')->assertOk()->getContent();

    expect($classic)->toContain('bg-slate-100');

    $soft = $this->get('/design-system/dashboard?design=soft')->assertOk()->getContent();

    expect($soft)->toContain('bg-zinc-100');

    $gallery = $this->get('/design-system?design=classic')->assertOk()->getContent();

    expect($gallery)->toContain('dark:bg-slate-950');

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
