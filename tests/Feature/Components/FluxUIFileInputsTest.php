<?php

declare(strict_types=1);

use App\Components\Groups\FluxUIFileInputs;

it('has the correct NAME constant', function () {
    expect(FluxUIFileInputs::NAME)->toBe('Flux UI Inputs');
});

it('returns 10 input variants', function () {
    expect(FluxUIFileInputs::list())->toHaveCount(10);
});

it('sets flex-column option', function () {
    expect(FluxUIFileInputs::options()->flexColumn)->toBeTrue();
});

it('renders a text input with placeholder', function () {
    $html = (string) $this->blade('{{ $input }}', ['input' => FluxUIFileInputs::textInput()]);

    expect($html)->toContain('data-flux-input')
        ->toContain('type="text"')
        ->toContain('placeholder="Enter your name"');
});

it('renders an email input', function () {
    $html = (string) $this->blade('{{ $input }}', ['input' => FluxUIFileInputs::emailInput()]);

    expect($html)->toContain('data-flux-input')
        ->toContain('type="email"')
        ->toContain('placeholder="you@example.com"');
});

it('renders a password input', function () {
    $html = (string) $this->blade('{{ $input }}', ['input' => FluxUIFileInputs::passwordInput()]);

    expect($html)->toContain('data-flux-input')
        ->toContain('type="password"')
        ->toContain('placeholder="Enter password"');
});

it('renders a number input', function () {
    $html = (string) $this->blade('{{ $input }}', ['input' => FluxUIFileInputs::numberInput()]);

    expect($html)->toContain('data-flux-input')
        ->toContain('type="number"');
});

it('renders a single file input', function () {
    $html = (string) $this->blade('{{ $input }}', ['input' => FluxUIFileInputs::fileInput()]);

    expect($html)->toContain('data-flux-input')
        ->toContain('name="avatar"');
});

it('renders a multiple file input', function () {
    $html = (string) $this->blade('{{ $input }}', ['input' => FluxUIFileInputs::fileInputMultiple()]);

    expect($html)->toContain('data-flux-input')
        ->toContain('name="documents"')
        ->toContain('multiple');
});

it('renders a select with options', function () {
    $html = (string) $this->blade('{{ $input }}', ['input' => FluxUIFileInputs::selectInput()]);

    expect($html)->toContain('Admin')
        ->toContain('Editor')
        ->toContain('Viewer')
        ->toContain('value="admin"')
        ->toContain('value="editor"')
        ->toContain('value="viewer"');
});

it('renders a checkbox with label', function () {
    $html = (string) $this->blade('{{ $input }}', ['input' => FluxUIFileInputs::checkboxInput()]);

    expect($html)->toContain('data-flux-checkbox')
        ->toContain('Accept terms and conditions');
});

it('renders a radio with label', function () {
    $html = (string) $this->blade('{{ $input }}', ['input' => FluxUIFileInputs::radioInput()]);

    expect($html)->toContain('data-flux-radio')
        ->toContain('Option A');
});

it('renders a textarea with placeholder', function () {
    $html = (string) $this->blade('{{ $input }}', ['input' => FluxUIFileInputs::textareaInput()]);

    expect($html)->toContain('data-flux-textarea')
        ->toContain('Write your message here...');
});
