<?php

declare(strict_types=1);

namespace App\DesignSystem\Examples;

use Juaniquillo\BackendDesignSystem\Components\Alert;
use Juaniquillo\BackendDesignSystem\Components\Badge;
use Juaniquillo\BackendDesignSystem\Components\Breadcrumb;
use Juaniquillo\BackendDesignSystem\Components\Button;
use Juaniquillo\BackendDesignSystem\Components\Card;
use Juaniquillo\BackendDesignSystem\Components\Field;
use Juaniquillo\BackendDesignSystem\Components\Form as FormComponent;
use Juaniquillo\BackendDesignSystem\Components\Heading;
use Juaniquillo\BackendDesignSystem\Components\Inline;
use Juaniquillo\BackendDesignSystem\Components\Select;
use Juaniquillo\BackendDesignSystem\Components\Stack;
use Juaniquillo\BackendDesignSystem\Components\Table;
use Juaniquillo\BackendDesignSystem\Components\Tabs;
use Juaniquillo\BackendDesignSystem\Components\Text;
use Juaniquillo\BackendDesignSystem\Designs\Design;
use Juaniquillo\BackendDesignSystem\Theme\AlertVariant;
use Juaniquillo\BackendDesignSystem\Theme\BadgeVariant;
use Juaniquillo\BackendDesignSystem\Theme\ButtonVariant;
use Juaniquillo\BackendDesignSystem\Theme\Size;

/**
 * The dashboard preview, composed from package components only.
 *
 * Every section takes the active design, so the same dashboard renders in
 * all five looks. The blade owns the responsive grid; this class owns the
 * component trees.
 */
final class Dashboard
{
    public static function breadcrumb(Design $design): Breadcrumb
    {
        return Breadcrumb::make($design)->items([
            ['label' => 'Workspace', 'href' => '#overview'],
            ['label' => 'Overview'],
        ]);
    }

    public static function title(Design $design): Heading
    {
        return Heading::make($design)->text('Overview')->level(1);
    }

    public static function subtitle(Design $design): Text
    {
        return Text::make($design)->text('Thursday, 26 November 2026 · Acme Studio');
    }

    public static function actions(Design $design): Inline
    {
        return Inline::make($design)->children([
            Button::make($design)->label('Export'),
            Button::make($design)->label('New invoice')->variant(ButtonVariant::Primary),
        ]);
    }

    public static function notice(Design $design): Alert
    {
        return Alert::make($design)
            ->variant(AlertVariant::Warning)
            ->title('3 invoices are overdue')
            ->body('Totalling €4,120.00. Send reminders before Friday.');
    }

    /**
     * @return array<int, Card>
     */
    public static function kpis(Design $design): array
    {
        return [
            Card::make($design)
                ->title('Total revenue')
                ->body(Heading::make($design)->text('$48,290')->level(2))
                ->footer(Badge::make($design)->label('+12.4% vs last month')->variant(BadgeVariant::Success)),
            Card::make($design)
                ->title('Open invoices')
                ->body(Heading::make($design)->text('18')->level(2))
                ->footer(Badge::make($design)->label('6 due this week')->variant(BadgeVariant::Info)),
            Card::make($design)
                ->title('Overdue')
                ->body(Heading::make($design)->text('€4,120.00')->level(2))
                ->footer(Badge::make($design)->label('3 invoices')->variant(BadgeVariant::Danger)),
            Card::make($design)
                ->title('Active clients')
                ->body(Heading::make($design)->text('132')->level(2))
                ->footer(Badge::make($design)->label('+4 this month')->variant(BadgeVariant::Success)),
        ];
    }

    public static function invoices(Design $design): Card
    {
        return Card::make($design)
            ->title('Invoices')
            ->body(Stack::make($design)->space(Size::Md)->children([
                Tabs::make($design)->label('Invoice filters')->items([
                    ['label' => 'All', 'href' => '#invoices', 'current' => true],
                    ['label' => 'Paid', 'href' => '#invoices'],
                    ['label' => 'Overdue', 'href' => '#invoices'],
                ]),
                Table::make($design)
                    ->caption('Invoices')
                    ->headers(['Invoice', 'Status', 'Amount', ''])
                    ->rows([
                        ['INV-1042', Badge::make($design)->label('Paid')->variant(BadgeVariant::Success), '€1,240.00', Button::make($design)->label('View')->variant(ButtonVariant::Ghost)],
                        ['INV-1043', Badge::make($design)->label('Overdue')->variant(BadgeVariant::Warning), '€860.00', Button::make($design)->label('View')->variant(ButtonVariant::Ghost)],
                        ['INV-1044', Badge::make($design)->label('Open'), '€2,020.00', Button::make($design)->label('View')->variant(ButtonVariant::Ghost)],
                    ])
                    ->footer(['', 'Total due', '€4,120.00', '']),
            ]));
    }

    public static function invite(Design $design): Card
    {
        return Card::make($design)
            ->title('Invite a teammate')
            ->body(FormComponent::make($design)
                ->action('#invite')
                ->method('post')
                ->fields([
                    Field::make($design)->label('Email')->input('invite-email')->required(),
                    Field::make($design)->label('Role')->input(
                        Select::make($design)->name('invite-role')->options(['member' => 'Member', 'admin' => 'Admin'])
                    ),
                ])
                ->actions(Button::make($design)->label('Send invite')->variant(ButtonVariant::Primary)->type('submit')));
    }
}
