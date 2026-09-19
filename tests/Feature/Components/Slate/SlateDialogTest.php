<?php

declare(strict_types=1);

use App\Components\Groups\Slate\SlateDialog;

it('has the correct NAME constant', function () {
    expect(SlateDialog::NAME)->toBe('Dialogs');
});

it('returns 2 dialog variants', function () {
    expect(SlateDialog::list())->toHaveCount(2);
});

it('returns default options', function () {
    $options = SlateDialog::options();

    expect($options->disableFlex)->toBeFalse()
        ->and($options->flexColumn)->toBeFalse()
        ->and($options->flexGap)->toBe('flex-gap-sm');
});

it('renders a simple dialog with trigger and content', function () {
    $html = (string) $this->blade('{{ $dialog }}', ['dialog' => SlateDialog::simple()]);

    expect($html)->toContain('data-slot="dialog"')
        ->toContain('data-slot="dialog-trigger"')
        ->toContain('data-slot="button"')
        ->toContain('Open')
        ->toContain('data-slot="dialog-content"')
        ->toContain('role="dialog"')
        ->toContain('aria-modal="true"')
        ->toContain('data-slot="dialog-title"')
        ->toContain('Cure Dialog')
        ->toContain('Yo, I&#039;m here');
});

it('renders a simple dialog with a close button', function () {
    $html = (string) $this->blade('{{ $dialog }}', ['dialog' => SlateDialog::simple()]);

    expect($html)->toContain('data-slot="dialog-close"')
        ->toContain('sr-only">Close');
});

it('renders a confirm dialog with header and footer', function () {
    $html = (string) $this->blade('{{ $dialog }}', ['dialog' => SlateDialog::confirm()]);

    expect($html)->toContain('data-slot="dialog"')
        ->toContain('data-slot="dialog-trigger"')
        ->toContain('Are you sure?')
        ->toContain('data-slot="dialog-description"')
        ->toContain('This action cannot be undone.')
        ->toContain('data-slot="dialog-footer"')
        ->toContain('Cancel');
});

it('hides the standalone close button on the confirm dialog', function () {
    $html = (string) $this->blade('{{ $dialog }}', ['dialog' => SlateDialog::confirm()]);

    expect($html)->not->toContain('sr-only">Close');
});

it('renders all dialog variants without errors', function () {
    foreach (SlateDialog::list() as $dialog) {
        $html = (string) $this->blade('{{ $dialog }}', ['dialog' => $dialog]);

        expect($html)->toContain('data-slot="dialog"');
    }
});
