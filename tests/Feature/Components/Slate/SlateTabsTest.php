<?php

declare(strict_types=1);

use App\Components\Groups\Slate\SlateTabs;

it('has the correct NAME constant', function () {
    expect(SlateTabs::NAME)->toBe('Tabs');
});

it('returns 1 tab variant', function () {
    expect(SlateTabs::list())->toHaveCount(1);
});

it('sets flex-column option to false', function () {
    expect(SlateTabs::options()->flexColumn)->toBeFalse();
});

it('renders tabs with triggers and content panels', function () {
    $html = (string) $this->blade('{{ $tabs }}', ['tabs' => SlateTabs::simple()]);

    expect($html)->toContain('data-slot="tabs"')
        ->toContain('data-orientation="horizontal"')
        ->toContain('data-slot="tabs-list"')
        ->toContain('role="tablist"')
        ->toContain('data-slot="tabs-trigger"')
        ->toContain('role="tab"')
        ->toContain('data-value="account"')
        ->toContain('data-value="password"')
        ->toContain('data-slot="tabs-content"')
        ->toContain('role="tabpanel"')
        ->toContain('This is account')
        ->toContain('This is password');
});
