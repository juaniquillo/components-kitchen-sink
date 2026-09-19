<?php

declare(strict_types=1);

use App\Components\Groups\Slate\SlateDropDownMenu;

it('has the correct NAME constant', function () {
    expect(SlateDropDownMenu::NAME)->toBe('Drop Down Menus');
});

it('returns 1 dropdown menu variant', function () {
    expect(SlateDropDownMenu::list())->toHaveCount(1);
});

it('returns default options', function () {
    $options = SlateDropDownMenu::options();

    expect($options->disableFlex)->toBeFalse()
        ->and($options->flexColumn)->toBeFalse()
        ->and($options->flexGap)->toBe('flex-gap-sm');
});

it('renders a dropdown menu with trigger and items', function () {
    $html = (string) $this->blade('{{ $menu }}', ['menu' => SlateDropDownMenu::simple()]);

    expect($html)->toContain('data-slot="dropdown-menu"')
        ->toContain('data-slot="dropdown-menu-trigger"')
        ->toContain('data-slot="button"')
        ->toContain('Open')
        ->toContain('data-slot="dropdown-menu-content"')
        ->toContain('role="menu"')
        ->toContain('w-48')
        ->toContain('data-slot="dropdown-menu-label"')
        ->toContain('My Account')
        ->toContain('data-slot="dropdown-menu-separator"')
        ->toContain('role="separator"')
        ->toContain('data-slot="dropdown-menu-item"')
        ->toContain('Profile')
        ->toContain('Billing')
        ->toContain('Settings')
        ->toContain('text-destructive')
        ->toContain('Log out');
});
