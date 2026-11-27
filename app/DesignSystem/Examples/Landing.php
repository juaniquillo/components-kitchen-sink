<?php

declare(strict_types=1);

namespace App\DesignSystem\Examples;

use Juaniquillo\BackendComponents\Enums\ComponentEnum;
use Juaniquillo\BackendComponents\Factories\ComponentFactory;
use Juaniquillo\BackendComponents\Factories\LocalThemeComponentFactory;
use Juaniquillo\BackendComponents\MainBackendComponent;
use Juaniquillo\BackendDesignSystem\Components\Accordion;
use Juaniquillo\BackendDesignSystem\Components\Badge;
use Juaniquillo\BackendDesignSystem\Components\Button;
use Juaniquillo\BackendDesignSystem\Components\Card;
use Juaniquillo\BackendDesignSystem\Components\Checkbox;
use Juaniquillo\BackendDesignSystem\Components\Divider;
use Juaniquillo\BackendDesignSystem\Components\Field;
use Juaniquillo\BackendDesignSystem\Components\Form as FormComponent;
use Juaniquillo\BackendDesignSystem\Components\Heading;
use Juaniquillo\BackendDesignSystem\Components\Inline;
use Juaniquillo\BackendDesignSystem\Components\Link;
use Juaniquillo\BackendDesignSystem\Components\Stack;
use Juaniquillo\BackendDesignSystem\Components\Text;
use Juaniquillo\BackendDesignSystem\Designs\Design;
use Juaniquillo\BackendDesignSystem\Theme\BadgeVariant;
use Juaniquillo\BackendDesignSystem\Theme\ButtonVariant;
use Juaniquillo\BackendDesignSystem\Theme\Size;
use RuntimeException;

/**
 * The Acme Studio landing page, composed from package components only.
 *
 * A promotion page, not an app shell: no nav, no switchers, just a hero
 * with a product preview, proof, features, pricing with a billing toggle,
 * an FAQ accordion, a newsletter form and a footer with one outbound
 * link. Designs switch through ?design= like the dashboard.
 */
final class Landing
{
    public const WEBSITE = 'https://example.com';

    /**
     * @return array<int, float|int>
     */
    private const REVENUE = [4.1, 4.4, 3.9, 4.8, 5.2, 5.0, 5.6, 5.9, 5.4, 6.1, 6.4, 6.9];

    /**
     * @return array<int, string>
     */
    private const MONTHS = ['J', 'F', 'M', 'A', 'M', 'J', 'J', 'A', 'S', 'O', 'N', 'D'];

    public static function hero(Design $design): Stack
    {
        return Stack::make($design)
            ->space(Size::Lg)
            ->attributes(['class' => 'text-center'])
            ->children([
                self::centered($design, Dashboard::brand($design)),
                Inline::make($design)
                    ->space(Size::Sm)
                    ->attributes(['class' => 'justify-center'])
                    ->children([
                        Badge::make($design)->label('New')->variant(BadgeVariant::Info),
                        Link::make($design)->label('Acme Studio 2.0 is live')->href(self::WEBSITE)->external(),
                    ]),
                Heading::make($design)->text('Invoices that pay themselves')->level(1),
                Text::make($design)->text('Acme Studio chases late payments, reconciles the books and shows you the month at a glance, so Thursday afternoons belong to you again.'),
                Inline::make($design)
                    ->space(Size::Sm)
                    ->attributes(['class' => 'justify-center'])
                    ->children([
                        Button::make($design)->label('See it live')->variant(ButtonVariant::Primary)
                            ->attributes(['onclick' => "location.href='".route('design-system.dashboard', ['design' => $design->key])."'"]),
                        Button::make($design)->label('Our website')->variant(ButtonVariant::Ghost)
                            ->attributes(['onclick' => "window.open('".self::WEBSITE."', '_blank', 'noopener')"]),
                    ]),
            ]);
    }

    public static function preview(Design $design): Card
    {
        return Card::make($design)
            ->title('Acme Studio')
            ->body(Chart::bars(
                $design,
                self::REVENUE,
                self::MONTHS,
                'Revenue by month, January to December',
            ))
            ->footer(Inline::make($design)->space(Size::Sm)->children([
                Badge::make($design)->label('Live')->variant(BadgeVariant::Success),
                Text::make($design)->text('Updated just now'),
            ]));
    }

    public static function proof(Design $design): Stack
    {
        return Stack::make($design)
            ->space(Size::Sm)
            ->attributes(['class' => 'text-center'])
            ->children([
                Text::make($design)->text('Trusted by finance teams at Meridian, Foundry, Northloop and Copperline'),
                Divider::make($design),
            ]);
    }

    /**
     * @return array<int, Card>
     */
    public static function features(Design $design): array
    {
        $features = [
            ['icon' => 'bolt', 'title' => 'Chases itself', 'body' => 'Reminders go out on your schedule, with the overdue total attached. Nothing to remember, nothing to dread.'],
            ['icon' => 'shield', 'title' => 'Reconciles itself', 'body' => 'Payments match their invoices the moment they land, and the books stay audit-ready without a spreadsheet.'],
            ['icon' => 'layers', 'title' => 'Reports itself', 'body' => 'The month, the quarter and the channel mix render as charts you can read in one glance, every glance.'],
        ];

        $cards = [];

        foreach ($features as $feature) {
            $cards[] = Card::make($design)
                ->title(Stack::make($design)
                    ->space(Size::Sm)
                    ->children([
                        self::glyph($design, $feature['icon']),
                        Heading::make($design)->text($feature['title'])->level(3),
                    ]))
                ->body(Text::make($design)->text($feature['body']));
        }

        return $cards;
    }

    public static function pricing(Design $design): Card
    {
        $plans = [
            self::plan($design, 'Starter', '$0', 'For trying the waters', ['3 active clients', 'Monthly reports']),
            self::plan($design, 'Team', null, 'For studios that bill weekly', ['Unlimited clients', 'Automatic reminders', 'Channel breakdown'], true),
            self::plan($design, 'Enterprise', 'Custom', 'For finance departments', ['Everything in Team', 'Audit log', 'Dedicated support']),
        ];

        return Card::make($design)
            ->title('Pricing that bills itself')
            ->body(Stack::make($design)
                ->space(Size::Md)
                ->attributes(['x-data' => '{ yearly: false }'])
                ->children([
                    Inline::make($design)
                        ->space(Size::Sm)
                        ->attributes(['class' => 'justify-center', 'role' => 'group', 'aria-label' => 'Billing period'])
                        ->children([
                            Button::make($design)->label('Monthly')->variant(ButtonVariant::Ghost)
                                ->attributes(['type' => 'button', 'x-on:click' => 'yearly = false', 'x-bind:aria-pressed' => '! yearly']),
                            Button::make($design)->label('Yearly')->variant(ButtonVariant::Ghost)
                                ->attributes(['type' => 'button', 'x-on:click' => 'yearly = true', 'x-bind:aria-pressed' => 'yearly']),
                        ]),
                    self::element($design, ComponentEnum::DIV)
                        ->setAttribute('class', 'grid gap-4 md:grid-cols-3')
                        ->setContents(array_map(
                            static fn (Card $plan): MainBackendComponent => $plan->component(),
                            $plans,
                        )),
                ]));
    }

    public static function faq(Design $design): Stack
    {
        $questions = [
            ['Is there a free trial?', 'Yes. Starter is free forever for three active clients, and every paid plan starts with a fourteen-day trial. No card required.', true],
            ['Can I cancel any time?', 'Yes. Cancel from settings and the plan runs to the end of the billing period. Your data stays exportable for a year.'],
            ['How do I bring my old invoices?', 'Upload a CSV or connect your bank and Acme matches historic payments to invoices as they import. Most studios finish in an afternoon.'],
            ['Is my data safe?', 'Data is encrypted in transit and at rest, backed up daily, and never sold. Enterprise adds an audit log and SSO.'],
            ['Do you offer support?', 'Every plan includes email support from people, not scripts. Team and Enterprise add priority queues and onboarding calls.'],
        ];

        $items = [];

        foreach ($questions as $item) {
            [$question, $answer] = $item;
            $open = $item[2] ?? false;

            $items[] = Accordion::make($design)
                ->summary($question)
                ->content(Text::make($design)->text($answer))
                ->open($open);
        }

        return Stack::make($design)
            ->space(Size::Md)
            ->children([
                Heading::make($design)->text('Frequently asked questions')->level(2),
                ...$items,
            ]);
    }

    public static function newsletter(Design $design): Card
    {
        return Card::make($design)
            ->title('Get product updates')
            ->body(FormComponent::make($design)
                ->action(route('design-system.landing.subscribe'))
                ->method('post')
                ->csrf()
                ->fields([
                    Field::make($design)->label('Email')->input('newsletter-email')->required(),
                    Checkbox::make($design)->name('updates')->label('Email me product updates'),
                ])
                ->actions(Button::make($design)->label('Notify me')->variant(ButtonVariant::Primary)->type('submit')));
    }

    public static function footer(Design $design): Stack
    {
        return Stack::make($design)
            ->space(Size::Sm)
            ->attributes(['class' => 'text-center'])
            ->children([
                Divider::make($design),
                self::centered($design, Dashboard::brand($design)),
                Text::make($design)->text('© 2026 Acme Studio. All rights reserved.'),
                Link::make($design)->label('Our website')->href(self::WEBSITE)->external(),
            ]);
    }

    /**
     * One pricing plan: the Team card swaps its price with the toggle.
     */
    private static function plan(Design $design, string $name, ?string $price, string $blurb, array $points, bool $popular = false): Card
    {
        $heading = Heading::make($design)->text($price ?? '$24/mo')->level(3);

        if ($price === null) {
            $heading->attributes(['x-text' => "yearly ? '$240/yr' : '$24/mo'"]);
        }

        $card = Card::make($design)
            ->title(Stack::make($design)
                ->space(Size::Sm)
                ->children([
                    $popular
                        ? Inline::make($design)->space(Size::Sm)->children([
                            Heading::make($design)->text($name)->level(3),
                            Badge::make($design)->label('Popular')->variant(BadgeVariant::Success),
                        ])
                        : Heading::make($design)->text($name)->level(3),
                    $heading,
                    Text::make($design)->text($blurb),
                ]))
            ->body(Stack::make($design)
                ->space(Size::Sm)
                ->children(array_map(
                    fn (string $point): Text => Text::make($design)->text('· '.$point),
                    $points,
                )))
            ->footer(Button::make($design)
                ->label($name === 'Enterprise' ? 'Talk to us' : 'Start free')
                ->variant($popular ? ButtonVariant::Primary : ButtonVariant::Ghost)
                ->attributes(['onclick' => "window.open('".self::WEBSITE."', '_blank', 'noopener')"]));

        return $card;
    }

    /**
     * The brand mark centred for the hero and the footer stacks.
     */
    private static function centered(Design $design, MainBackendComponent $content): MainBackendComponent
    {
        return self::element($design, ComponentEnum::DIV)
            ->setAttribute('class', 'flex justify-center')
            ->setContent($content);
    }

    /**
     * A feature glyph: an icon mask painted with the page text colour.
     */
    private static function glyph(Design $design, string $icon): MainBackendComponent
    {
        $glyph = LocalThemeComponentFactory::make(ComponentEnum::SPAN);

        if (! $glyph instanceof MainBackendComponent) {
            throw new RuntimeException('Landing builds MainBackendComponent instances.');
        }

        return $glyph
            ->setAttributes(['aria-hidden' => 'true'])
            ->setThemes(['nav' => 'icon-'.$icon]);
    }

    /**
     * Plain elements rendered through the design's theme manager.
     */
    private static function element(Design $design, ComponentEnum $name): MainBackendComponent
    {
        $component = ComponentFactory::make($name);

        if (! $component instanceof MainBackendComponent) {
            throw new RuntimeException('Landing builds MainBackendComponent instances.');
        }

        return $component->setThemeManager($design->themeManager);
    }
}
