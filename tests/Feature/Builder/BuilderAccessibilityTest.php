<?php

declare(strict_types=1);

use App\Livewire\Builder\BuilderCanvas;
use Livewire\Livewire;

/**
 * The drawer element of a rendered builder.
 */
function builderDrawer(string $html): DOMElement
{
    $dom = new DOMDocument;

    @$dom->loadHTML($html);

    $drawer = (new DOMXPath($dom))->query('//aside[@aria-label="Builder drawer"]')->item(0);

    expect($drawer)->toBeInstanceOf(DOMElement::class);

    return $drawer;
}

it('takes the closed drawer out of the page', function () {
    // The drawer is only moved off screen while it is closed, so without
    // `inert` the keyboard still walks into a panel nobody can see.
    $component = Livewire::test(BuilderCanvas::class);

    expect(builderDrawer($component->html())->hasAttribute('inert'))->toBeTrue();

    $component->call('openConfig', 'n1');

    expect(builderDrawer($component->html())->hasAttribute('inert'))->toBeFalse();
});

it('names the regions of the page', function () {
    $html = Livewire::test(BuilderCanvas::class)->html();

    expect($html)->toContain('aria-labelledby="builder-canvas-heading"')
        ->toContain('id="builder-canvas-heading"')
        ->toContain('aria-labelledby="builder-palette-heading"')
        ->toContain('id="builder-palette-heading"');
});

it('ties the name field to its label', function () {
    $html = Livewire::test(BuilderCanvas::class)->html();

    expect($html)->toContain('for="builder-workspace-name"')
        ->toContain('id="builder-workspace-name"')
        ->toContain('maxlength="120"');
});

it('gives every control of a node a name', function () {
    $preview = Livewire::test(BuilderCanvas::class)
        ->call('add', 'headers.h1')
        ->instance()
        ->preview();

    expect($preview)->toContain('aria-label="Drag to move"')
        ->toContain('aria-label="Configure the Heading 1"')
        ->toContain('aria-label="Add content to the Heading 1"')
        ->toContain('aria-label="Delete the Heading 1"');
});

it('makes the add menu out of buttons', function () {
    // The entries of the menu were spans with a click handler, which cannot
    // be reached with the keyboard or fired with enter and space.
    $preview = Livewire::test(BuilderCanvas::class)
        ->call('add', 'headers.h1')
        ->instance()
        ->preview();

    $dom = new DOMDocument;

    @$dom->loadHTML('<div>'.$preview.'</div>');

    $xpath = new DOMXPath($dom);

    $menu = $xpath->query('//*[@id="bw-menu-n2"]')->item(0);

    expect($menu)->toBeInstanceOf(DOMElement::class)
        ->and($xpath->query('.//button', $menu)->length)->toBeGreaterThan(0)
        ->and($xpath->query('.//span[@*[name()="x-on:click"]]', $menu)->length)->toBe(0);
});

it('says which theme chips are on a node', function () {
    $component = Livewire::test(BuilderCanvas::class)
        ->set('themeGroup', 'action')
        ->set('themeVariant', 'primary')
        ->set('themeClasses', 'bg-indigo-600')
        ->call('saveTheme')
        ->call('openConfig', 'n1');

    expect($component->html())->toContain('aria-pressed="false"');

    $component->call('toggleTheme', 'n1', 'action', 'primary');

    expect($component->html())->toContain('aria-pressed="true"');
});

it('makes each theme group a disclosure', function () {
    // Every group starts collapsed: the file path in the header is the
    // button that opens it, and the bulk buttons move all of them at once.
    $html = Livewire::test(BuilderCanvas::class)
        ->set('themeGroup', 'action')
        ->set('themeVariant', 'primary')
        ->set('themeClasses', 'bg-indigo-600')
        ->call('saveTheme')
        ->call('openThemes')
        ->html();

    expect($html)->toContain('aria-controls="theme-group-action"')
        ->toContain('id="theme-group-action"')
        ->toContain('x-bind:aria-expanded')
        ->toContain('aria-label="Classes of action / primary"')
        ->toContain('Collapse all')
        ->toContain('Expand all');
});

it('offers the themes as a download', function () {
    $component = Livewire::test(BuilderCanvas::class)
        ->set('themeGroup', 'action')
        ->set('themeVariant', 'primary')
        ->set('themeClasses', 'bg-indigo-600')
        ->call('saveTheme')
        ->call('openThemes');

    expect($component->html())->toContain('Download all themes (.zip)')
        ->toContain(route('builder.export', ['workspace' => $component->instance()->workspace]));
});
