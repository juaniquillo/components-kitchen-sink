<?php

declare(strict_types=1);

use App\Components\Groups\Slate\SlateButtons;

it('has the correct NAME constant', function () {
    expect(SlateButtons::NAME)->toBe('Buttons');
});

it('returns 9 button variants', function () {
    expect(SlateButtons::list())->toHaveCount(9);
});

it('returns default options', function () {
    $options = SlateButtons::options();

    expect($options->disableFlex)->toBeFalse()
        ->and($options->flexColumn)->toBeFalse()
        ->and($options->flexGap)->toBe('flex-gap-sm');
});

it('renders a default button', function () {
    $html = (string) $this->blade('{{ $button }}', ['button' => SlateButtons::defaultButton()]);

    expect($html)->toContain('data-slot="button"')
        ->toContain('Simple Button')
        ->toContain('bg-primary text-primary-foreground hover:bg-primary/90');
});

it('renders a secondary button', function () {
    $html = (string) $this->blade('{{ $button }}', ['button' => SlateButtons::secondaryButton()]);

    expect($html)->toContain('Secondary Button')
        ->toContain('bg-secondary text-secondary-foreground hover:bg-secondary/80');
});

it('renders an outline button', function () {
    $html = (string) $this->blade('{{ $button }}', ['button' => SlateButtons::outlineButton()]);

    expect($html)->toContain('Outline Button')
        ->toContain('border bg-background shadow-xs');
});

it('renders a ghost button without a solid background', function () {
    $html = (string) $this->blade('{{ $button }}', ['button' => SlateButtons::ghostButton()]);

    expect($html)->toContain('Ghost Button')
        ->toContain('hover:bg-accent hover:text-accent-foreground')
        ->not->toContain('bg-primary');
});

it('renders a destructive button', function () {
    $html = (string) $this->blade('{{ $button }}', ['button' => SlateButtons::destructiveButton()]);

    expect($html)->toContain('Destructive Button')
        ->toContain('bg-destructive text-white');
});

it('renders a link button', function () {
    $html = (string) $this->blade('{{ $button }}', ['button' => SlateButtons::linkButton()]);

    expect($html)->toContain('Link Button')
        ->toContain('underline-offset-4 hover:underline');
});

it('renders all size variants', function () {
    $xs = (string) $this->blade('{{ $button }}', ['button' => SlateButtons::xsButton()]);
    $sm = (string) $this->blade('{{ $button }}', ['button' => SlateButtons::smButton()]);
    $lg = (string) $this->blade('{{ $button }}', ['button' => SlateButtons::lgButton()]);

    expect($xs)->toContain('XS Button')
        ->toContain('h-6 gap-1 px-2')
        ->and($sm)->toContain('SM Button')
        ->toContain('h-8 gap-1.5 px-3')
        ->and($lg)->toContain('LG Button')
        ->toContain('h-10 px-6');
});

it('renders all button variants without errors', function () {
    foreach (SlateButtons::list() as $button) {
        $html = (string) $this->blade('{{ $button }}', ['button' => $button]);

        expect($html)->toContain('data-slot="button"');
    }
});
