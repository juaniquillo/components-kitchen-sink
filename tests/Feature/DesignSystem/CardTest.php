<?php

declare(strict_types=1);

use App\DesignSystem\Components\Button;
use App\DesignSystem\Components\Card;
use Juaniquillo\BackendComponents\Enums\ComponentEnum;
use Juaniquillo\BackendComponents\Factories\ComponentFactory;
use Juaniquillo\BaseBackendComponent\Contracts\BackendComponent;

it('renders a card with title, body and footer', function () {
    $html = (string) $this->blade('{{ $card }}', [
        'card' => Card::make(designSystemBase())
            ->title('Billing')
            ->body('Your plan renews soon.')
            ->footer('Footer'),
    ]);

    expect($html)->toContain('Billing')
        ->toContain('Your plan renews soon.')
        ->toContain('Footer');
});

it('renders the title as a heading', function () {
    $html = (string) $this->blade('{{ $card }}', [
        'card' => Card::make(designSystemBase())->title('Billing')->body('Renews soon.'),
    ]);

    expect($html)->toContain('<h3')
        ->toContain('Billing');
});

it('lets the title level be chosen and clamped', function () {
    $two = (string) $this->blade('{{ $card }}', [
        'card' => Card::make(designSystemBase())->title('Billing')->titleLevel(2),
    ]);
    $nine = (string) $this->blade('{{ $card }}', [
        'card' => Card::make(designSystemBase())->title('Billing')->titleLevel(9),
    ]);

    expect($two)->toContain('<h2')
        ->and($nine)->toContain('<h6');
});

it('leaves a component title as it was built', function () {
    $html = (string) $this->blade('{{ $card }}', [
        'card' => Card::make(designSystemBase())
            ->title(ComponentFactory::make(ComponentEnum::H4)->setContent('Custom')),
    ]);

    expect($html)->toContain('<h4')
        ->toContain('Custom');
});

it('accepts components in every slot', function () {
    $html = (string) $this->blade('{{ $card }}', [
        'card' => Card::make(designSystemBase())
            ->title('Billing')
            ->body('Your plan renews soon.')
            ->footer(Button::make(designSystemBase())->label('Manage')->component()),
    ]);

    expect($html)->toContain('<button')
        ->toContain('Manage');
});

it('omits slots that are not set', function () {
    $html = (string) $this->blade('{{ $card }}', [
        'card' => Card::make(designSystemBase())->body('Only body.'),
    ]);

    // A string body still travels in its own section div, so a body-only
    // card renders two divs: the container and the body section.
    expect($html)->toContain('Only body.')
        ->and(substr_count($html, '<div'))->toBe(2);
});

it('exposes a plain backend component', function () {
    expect(Card::make(designSystemBase())->body('Hi')->component())->toBeInstanceOf(BackendComponent::class);
});
