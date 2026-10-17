<?php

declare(strict_types=1);

use Juaniquillo\MjmlBackendComponents\Builders\MjmlComponentBuilder;
use Juaniquillo\MjmlBackendComponents\Enums\MjmlComponentEnum;

test('single quotes pass through toHtml() escaping flag', function () {
    $component = MjmlComponentBuilder::make(MjmlComponentEnum::IMAGE)
        ->setAttribute('src', "{{ asset('images/hero.jpg') }}");

    $html = $component->toHtml();

    // Single quotes stay literal — MJML keeps them as-is when ENT_COMPAT flag is used
    expect($html)->toContain("src=\"{{ asset('images/hero.jpg') }}\"");
});

test('double quotes and & still escape', function () {
    $component = MjmlComponentBuilder::make(MjmlComponentEnum::IMAGE)
        ->setAttribute('src', 'https://example.com/?a=1&b="x"');

    $html = $component->toHtml();

    // Double quote becomes "
    expect($html)->not->toContain('"x"');
    expect($html)->toContain('"');

    // Ampersand becomes &
    expect($html)->toContain('&');
});

test('enum output sanity checks MJmlComponentEnum string-backed conversion', function () {
    // SECTION → <mj-section>
    $section = MjmlComponentBuilder::make(MjmlComponentEnum::SECTION);
    expect($section->toHtml())->toContain('<mj-section');

    // MJML root → <mjml>
    $root = MjmlComponentBuilder::document([]);
    expect($root->toHtml())->toContain('<mjml');

    // NEW: MJML_CLASS → <mj-class>
    $class = MjmlComponentBuilder::make(MjmlComponentEnum::MJML_CLASS);
    expect($class->toHtml())->toContain('<mj-class');
});
