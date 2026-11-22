<?php

declare(strict_types=1);

namespace App\DesignSystem;

use Juaniquillo\BackendDesignSystem\Components\Alert;
use Juaniquillo\BackendDesignSystem\Components\Badge;
use Juaniquillo\BackendDesignSystem\Components\Button;
use Juaniquillo\BackendDesignSystem\Components\Card;
use Juaniquillo\BackendDesignSystem\Components\Checkbox;
use Juaniquillo\BackendDesignSystem\Components\Field;
use Juaniquillo\BackendDesignSystem\Components\Form;
use Juaniquillo\BackendDesignSystem\Components\Input;
use Juaniquillo\BackendDesignSystem\Components\Radio;
use Juaniquillo\BackendDesignSystem\Components\Select;
use Juaniquillo\BackendDesignSystem\Components\Textarea;
use Juaniquillo\BackendDesignSystem\Contracts\DesignComponent;
use Juaniquillo\BackendDesignSystem\Designs\Design;
use Juaniquillo\BackendDesignSystem\Theme\AlertVariant;
use Juaniquillo\BackendDesignSystem\Theme\BadgeVariant;
use Juaniquillo\BackendDesignSystem\Theme\ButtonVariant;
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
     * @return array<int, array{title: string, components: array<int, DesignComponent|BackendComponent>}>
     */
    public static function boxes(Design $design): array
    {
        return [
            [
                'title' => 'Buttons',
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
                'components' => [
                    Card::make($design)
                        ->title('Billing')
                        ->body('Your plan renews on the first of every month.')
                        ->footer(Button::make($design)->label('Manage')->variant(ButtonVariant::Primary)->component()),
                    Card::make($design)
                        ->title('About')
                        ->body('A kitchen sink of components built with backend-component trees.'),
                ],
            ],
            [
                'title' => 'Inputs',
                'components' => [
                    Input::make($design)->name('email')->type('email')->placeholder('you@example.com')->attributes(['aria-label' => 'Email']),
                    Input::make($design)->name('password')->type('password')->placeholder('Password')->required()->attributes(['aria-label' => 'Password']),
                    Input::make($design)->name('nickname')->placeholder('Disabled')->disabled()->attributes(['aria-label' => 'Disabled example']),
                ],
            ],
            [
                'title' => 'Choices',
                'components' => [
                    Checkbox::make($design)->name('terms')->label('I accept the terms')->checked(),
                    Checkbox::make($design)->name('newsletter')->label('Send me the newsletter'),
                    Checkbox::make($design)->name('locked')->label('Locked choice')->disabled(),
                    Radio::make($design)->name('plan')->value('free')->label('Free')->checked(),
                    Radio::make($design)->name('plan')->value('pro')->label('Pro'),
                    Radio::make($design)->name('plan')->value('team')->label('Team'),
                ],
            ],
            [
                'title' => 'Select and Textarea',
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
                'components' => [
                    Field::make($design)
                        ->label('Email')
                        ->input(Input::make($design)->name('email')->type('email')->attributes(['id' => 'signup-email'])->component()),
                    Field::make($design)
                        ->label('Backup email')
                        ->input('backup-email')
                        ->error('That address is already taken.')
                        ->required(),
                ],
            ],
            [
                'title' => 'Form',
                'components' => [
                    Form::make($design)
                        ->action('/subscribe')
                        ->method('post')
                        ->fields([
                            Field::make($design)->label('Email')->input('form-email')->required(),
                            Field::make($design)->label('Country')->input(
                                Select::make($design)->name('form-country')->options(['' => 'Choose', 'es' => 'Spain', 'fr' => 'France'])->component()
                            ),
                            Checkbox::make($design)->name('form-terms')->label('I accept the terms'),
                        ])
                        ->actions(Button::make($design)->label('Subscribe')->variant(ButtonVariant::Primary)->type('submit')->component()),
                ],
            ],
            [
                'title' => 'Alerts',
                'components' => [
                    Alert::make($design)->variant(AlertVariant::Info)->title('Heads up')->body('Your trial ends in three days.'),
                    Alert::make($design)->variant(AlertVariant::Success)->title('Saved')->body('Your changes are live.'),
                    Alert::make($design)->variant(AlertVariant::Warning)->title('Careful')->body('This action cannot be undone.'),
                    Alert::make($design)->variant(AlertVariant::Danger)->title('Failed')->body('The export did not finish.'),
                ],
            ],
        ];
    }
}
