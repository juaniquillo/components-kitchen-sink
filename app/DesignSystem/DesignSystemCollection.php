<?php

declare(strict_types=1);

namespace App\DesignSystem;

use App\Components\ContainerOptions;
use Juaniquillo\BackendDesignSystem\Components\Accordion;
use Juaniquillo\BackendDesignSystem\Components\Alert;
use Juaniquillo\BackendDesignSystem\Components\Badge;
use Juaniquillo\BackendDesignSystem\Components\Breadcrumb;
use Juaniquillo\BackendDesignSystem\Components\Button;
use Juaniquillo\BackendDesignSystem\Components\Card;
use Juaniquillo\BackendDesignSystem\Components\Checkbox;
use Juaniquillo\BackendDesignSystem\Components\ChoiceGroup;
use Juaniquillo\BackendDesignSystem\Components\Dialog;
use Juaniquillo\BackendDesignSystem\Components\Divider;
use Juaniquillo\BackendDesignSystem\Components\Field;
use Juaniquillo\BackendDesignSystem\Components\Form;
use Juaniquillo\BackendDesignSystem\Components\Heading;
use Juaniquillo\BackendDesignSystem\Components\Inline;
use Juaniquillo\BackendDesignSystem\Components\Input;
use Juaniquillo\BackendDesignSystem\Components\Link;
use Juaniquillo\BackendDesignSystem\Components\Radio;
use Juaniquillo\BackendDesignSystem\Components\Select;
use Juaniquillo\BackendDesignSystem\Components\Stack;
use Juaniquillo\BackendDesignSystem\Components\Table;
use Juaniquillo\BackendDesignSystem\Components\Tabs;
use Juaniquillo\BackendDesignSystem\Components\Text;
use Juaniquillo\BackendDesignSystem\Components\Textarea;
use Juaniquillo\BackendDesignSystem\Contracts\DesignComponent;
use Juaniquillo\BackendDesignSystem\Designs\Design;
use Juaniquillo\BackendDesignSystem\Theme\AlertVariant;
use Juaniquillo\BackendDesignSystem\Theme\BadgeVariant;
use Juaniquillo\BackendDesignSystem\Theme\ButtonVariant;
use Juaniquillo\BackendDesignSystem\Theme\HeadingLevel;
use Juaniquillo\BackendDesignSystem\Theme\Size;
use Juaniquillo\BaseBackendComponent\Contracts\BackendComponent;

/**
 * The gallery the design system page renders.
 *
 * Every component is built against the design that was asked for, so the
 * same gallery runs on any design. The page itself only picks the design
 * and renders the boxes; what is shown belongs here.
 */
final class DesignSystemCollection
{
    /**
     * @return array<int, array{title: string, options: ContainerOptions, components: array<int, DesignComponent|BackendComponent>}>
     */
    public static function boxes(Design $design): array
    {
        return [
            [
                'title' => 'Buttons',
                'options' => self::options(),
                'components' => [
                    Button::make($design)->label('Primary')->variant(ButtonVariant::Primary),
                    Button::make($design)->label('Secondary')->variant(ButtonVariant::Secondary),
                    Button::make($design)->label('Danger')->variant(ButtonVariant::Danger),
                    Button::make($design)->label('Ghost')->variant(ButtonVariant::Ghost),
                    Button::make($design)->label('Small')->size(Size::Sm),
                    Button::make($design)->label('Large')->size(Size::Lg),
                    Button::make($design)->label('Save')->variant(ButtonVariant::Primary)->attributes(['wire:click' => 'save']),
                ],
            ],
            [
                'title' => 'Badges',
                'options' => self::options(),
                'components' => [
                    Badge::make($design)->label('Default'),
                    Badge::make($design)->label('Active')->variant(BadgeVariant::Success),
                    Badge::make($design)->label('New')->variant(BadgeVariant::Info),
                    Badge::make($design)->label('Expiring')->variant(BadgeVariant::Warning),
                    Badge::make($design)->label('Failed')->variant(BadgeVariant::Danger),
                ],
            ],
            [
                'title' => 'Cards',
                'options' => self::options(),
                'components' => [
                    Card::make($design)
                        ->title('Billing')
                        ->body('Your plan renews on the first of every month.')
                        ->footer(Button::make($design)->label('Manage')->variant(ButtonVariant::Primary)),
                    Card::make($design)
                        ->title('About')
                        ->body('A kitchen sink of components built with backend-component trees.'),
                ],
            ],
            [
                'title' => 'Typography',
                'options' => self::options(disableFlex: true),
                'components' => [
                    Stack::make($design)
                        ->space(Size::Sm)
                        ->children([
                            Heading::make($design)->text('Billing overview')->level(HeadingLevel::H3),
                            Text::make($design)->text('Your plan renews on the first of every month.'),
                            Text::make($design)->text('Prices exclude taxes.')->size(Size::Sm),
                            Inline::make($design)
                                ->space(Size::Sm)
                                ->children([
                                    Link::make($design)->label('Manage billing')->href('#typography'),
                                    Link::make($design)->label('Current page')->href('#typography')->current(),
                                    Link::make($design)->label('Documentation')->href('https://example.com/docs')->external(),
                                ]),
                            Divider::make($design),
                        ]),
                ],
            ],
            [
                'title' => 'Layout',
                'options' => self::options(disableFlex: true),
                'components' => [
                    Stack::make($design)
                        ->space(Size::Lg)
                        ->children([
                            Stack::make($design)
                                ->space(Size::Sm)
                                ->children([
                                    Text::make($design)->text('Stacked heading'),
                                    Text::make($design)->text('Stacked body copy.')->size(Size::Sm),
                                ]),
                            Inline::make($design)
                                ->space(Size::Sm)
                                ->children([
                                    Button::make($design)->label('Cancel'),
                                    Button::make($design)->label('Save')->variant(ButtonVariant::Primary),
                                ]),
                        ]),
                ],
            ],
            [
                'title' => 'Inputs',
                'options' => self::options(),
                'components' => [
                    Input::make($design)->name('email')->type('email')->placeholder('you@example.com')->attributes(['aria-label' => 'Email']),
                    Input::make($design)->name('password')->type('password')->placeholder('Password')->required()->attributes(['aria-label' => 'Password']),
                    Input::make($design)->name('nickname')->placeholder('Disabled')->disabled()->attributes(['aria-label' => 'Disabled example']),
                ],
            ],
            [
                'title' => 'Choices',
                'options' => self::options(disableFlex: true),
                'components' => [
                    Stack::make($design)
                        ->space(Size::Sm)
                        ->children([
                            Inline::make($design)
                                ->space(Size::Sm)
                                ->children([
                                    Checkbox::make($design)->name('terms')->label('I accept the terms')->checked(),
                                    Checkbox::make($design)->name('newsletter')->label('Send me the newsletter'),
                                    Checkbox::make($design)->name('locked')->label('Locked choice')->disabled(),
                                ]),
                            Inline::make($design)
                                ->space(Size::Sm)
                                ->children([
                                    Radio::make($design)->name('plan')->value('free')->label('Free')->checked(),
                                    Radio::make($design)->name('plan')->value('pro')->label('Pro'),
                                    Radio::make($design)->name('plan')->value('team')->label('Team'),
                                ]),
                            ChoiceGroup::make($design)
                                ->legend('Billing cycle')
                                ->id('billing-cycle')
                                ->choices([
                                    Radio::make($design)->name('cycle')->value('monthly')->label('Monthly'),
                                    Radio::make($design)->name('cycle')->value('yearly')->label('Yearly'),
                                ])
                                ->error('Choose a billing cycle.'),
                        ]),
                ],
            ],
            [
                'title' => 'Select and Textarea',
                'options' => self::options(flexColumn: true, items: 'stretch'),
                'components' => [
                    Select::make($design)->name('country')->options([
                        '' => 'Choose a country',
                        'es' => 'Spain',
                        'fr' => 'France',
                        'de' => 'Germany',
                    ])->value('fr')->attributes(['aria-label' => 'Country']),
                    Select::make($design)->name('disabled-select')->options(['' => 'Not available'])->disabled()->attributes(['aria-label' => 'Unavailable country']),
                    Textarea::make($design)->name('notes')->rows(3)->placeholder('Anything we should know?')->attributes(['aria-label' => 'Notes']),
                ],
            ],
            [
                'title' => 'Fields',
                'options' => self::options(),
                'components' => [
                    Field::make($design)
                        ->label('Email')
                        ->input(Input::make($design)->name('email')->type('email')->attributes(['id' => 'signup-email'])),
                    Field::make($design)
                        ->label('Confirm email')
                        ->input('confirm-email')
                        ->error('The email addresses do not match.')
                        ->required(),
                ],
            ],
            [
                'title' => 'Form',
                'options' => self::options(disableFlex: true),
                'components' => [
                    Form::make($design)
                        ->action('/subscribe')
                        ->method('post')
                        ->fields([
                            Field::make($design)->label('Email')->input('form-email')->required(),
                            Field::make($design)->label('Country')->input(
                                Select::make($design)->name('form-country')->options(['' => 'Choose', 'es' => 'Spain', 'fr' => 'France'])
                            ),
                            Checkbox::make($design)->name('form-terms')->label('I accept the terms'),
                        ])
                        ->actions(Button::make($design)->label('Subscribe')->variant(ButtonVariant::Primary)->type('submit')),
                ],
            ],
            [
                'title' => 'Disclosure',
                'options' => self::options(flexColumn: true, items: 'stretch'),
                'components' => [
                    Accordion::make($design)
                        ->summary('Shipping details')
                        ->content('Ships in two business days.'),
                    Accordion::make($design)
                        ->summary('Returns')
                        ->content('Thirty days, unused and in the original box.')
                        ->open(),
                ],
            ],
            [
                'title' => 'Navigation',
                'options' => self::options(disableFlex: true),
                'components' => [
                    Breadcrumb::make($design)->items([
                        ['label' => 'Home', 'href' => '#navigation'],
                        ['label' => 'Billing', 'href' => '#navigation'],
                        ['label' => 'Invoices'],
                    ]),
                ],
            ],
            [
                'title' => 'Data table',
                'options' => self::options(disableFlex: true),
                'components' => [
                    Stack::make($design)
                        ->space(Size::Md)
                        ->children([
                            Table::make($design)
                                ->caption('Invoices')
                                ->headers(['Invoice', 'Status', 'Amount'])
                                ->rows([
                                    ['INV-001', 'Paid', '€120.00'],
                                    ['INV-002', 'Open', '€80.00'],
                                ])
                                ->footer(['', '', '€200.00']),
                            Table::make($design)
                                ->headers(['Invoice', 'Status'])
                                ->empty('No invoices yet.'),
                        ]),
                ],
            ],
            [
                'title' => 'Tabs',
                'options' => self::options(disableFlex: true),
                'components' => [
                    Tabs::make($design)
                        ->label('Billing sections')
                        ->items([
                            ['label' => 'Overview', 'href' => '#tabs', 'current' => true],
                            ['label' => 'Invoices', 'href' => '#tabs'],
                            ['label' => 'Settings'],
                        ]),
                ],
            ],
            [
                'title' => 'Dialog',
                'options' => self::options(disableFlex: true),
                'components' => [
                    Button::make($design)->label('Open dialog')->variant(ButtonVariant::Primary)->attributes([
                        'command' => 'show-modal',
                        'commandfor' => 'billing-dialog',
                    ]),
                    Dialog::make($design)
                        ->id('billing-dialog')
                        ->title('Cancel subscription')
                        ->body('You keep access until the end of the billing period.')
                        ->footer(Inline::make($design)->space(Size::Sm)->children([
                            Button::make($design)->label('Keep plan')
                                ->attributes([
                                    'command' => 'close', 'commandfor' => 'billing-dialog',
                                ]),
                            Button::make($design)->label('Cancel plan')->variant(ButtonVariant::Danger)
                                ->attributes([
                                    'command' => 'close', 'commandfor' => 'billing-dialog',
                                ]),
                        ])),
                ],
            ],
            [
                'title' => 'Alerts',
                'options' => self::options(),
                'components' => [
                    Alert::make($design)->variant(AlertVariant::Info)->title('Heads up')->body('Your trial ends in three days.'),
                    Alert::make($design)->variant(AlertVariant::Success)->title('Saved')->body('Your changes are live.'),
                    Alert::make($design)->variant(AlertVariant::Warning)->title('Careful')->body('This action cannot be undone.'),
                    Alert::make($design)->variant(AlertVariant::Danger)->title('Failed')->body('The export did not finish.'),
                ],
            ],
        ];
    }

    /**
     * The container each box's components sit in.
     *
     * The gallery reuses the options of the kitchen-sink groups, so a box can
     * turn flex off or sit in a column without a new view change.
     */
    private static function options(
        bool $disableFlex = false,
        bool $flexColumn = false,
        string $justify = 'start',
        string $items = 'center',
    ): ContainerOptions {
        return new ContainerOptions(
            disableFlex: $disableFlex,
            flexColumn: $flexColumn,
            flexGap: 'flex-gap',
            justify: $justify,
            items: $items,
        );
    }
}
