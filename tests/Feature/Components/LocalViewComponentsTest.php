<?php

declare(strict_types=1);

use Juaniquillo\BackendComponents\Builders\LocalComponentBuilder;
use Juaniquillo\BackendComponents\MainBackendComponent;
use Juaniquillo\BackendComponents\Themes\LocalThemeManager;

it('renders the local iframe with its attributes', function () {
    // The email preview embeds an email in one of these, the way
    // EmailPresenter builds it.
    $html = (string) $this->blade('{{ $component }}', ['component' => LocalComponentBuilder::make('iframe')
        ->setTheme('iframe', 'default')
        ->setAttributes([
            'src' => 'http://example.test/mail/x',
            'loading' => 'lazy',
            'title' => 'Welcome',
        ])]);

    expect($html)->toContain('<iframe')
        ->toContain('src="http://example.test/mail/x"')
        ->toContain('loading="lazy"')
        ->toContain('title="Welcome"')
        ->toContain('w-full');
});

it('renders the form group web component with attributes and content', function () {
    // The crud schemas wrap inputs in one of these.
    $html = (string) $this->blade('{{ $component }}', ['component' => (new MainBackendComponent(name: 'form-group-web-component.main', themeManager: new LocalThemeManager))
        ->useLocal()
        ->setAttribute('data-test', 'group')
        ->setContent('Hello')]);

    expect($html)->toContain('<form-group')
        ->toContain('data-test="group"')
        ->toContain('Hello');
});
