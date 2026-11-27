<?php

declare(strict_types=1);

namespace App\DesignSystem\Examples;

use Juaniquillo\BackendComponents\Enums\ComponentEnum;
use Juaniquillo\BackendComponents\Factories\ComponentFactory;
use Juaniquillo\BackendComponents\MainBackendComponent;
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
use Juaniquillo\BackendDesignSystem\Components\Text;
use Juaniquillo\BackendDesignSystem\Designs\Design;
use Juaniquillo\BackendDesignSystem\Theme\AlertVariant;
use Juaniquillo\BackendDesignSystem\Theme\BadgeVariant;
use Juaniquillo\BackendDesignSystem\Theme\ButtonVariant;
use Juaniquillo\BackendDesignSystem\Theme\Size;
use Juaniquillo\BackendDesignSystem\Theme\State;
use RuntimeException;

/**
 * The dashboard preview, composed from package components only.
 *
 * Every section takes the active design, so the same dashboard renders in
 * all five looks. The blade owns the responsive grid; this class owns the
 * component trees.
 */
final class Dashboard
{
    /**
     * The invoice filters, keyed by the tab that shows them.
     */
    private const INVOICES = [
        'INV-1042' => ['Paid', BadgeVariant::Success, '€1,240.00'],
        'INV-1043' => ['Overdue', BadgeVariant::Warning, '€860.00'],
        'INV-1044' => ['Open', BadgeVariant::Default, '€2,020.00'],
    ];

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
                ->body(Stack::make($design)->children([
                    Heading::make($design)->text('$48,290')->level(2),
                    Chart::sparkline([12, 18, 15, 22, 28, 25, 34, 41, 38, 48]),
                ]))
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

    /**
     * The invoices card: a working tab widget over one table per filter.
     *
     * Tabs are real tabs (tablist/tab/tabpanel), not the package's
     * navigation Tabs component, because they switch panels. The look
     * still comes from the design's own tabs theme.
     */
    public static function invoices(Design $design): Card
    {
        $panels = [
            self::invoicePanel($design, 'all', array_keys(self::INVOICES), '€4,120.00'),
            self::invoicePanel($design, 'paid', ['INV-1042'], '€1,240.00'),
            self::invoicePanel($design, 'overdue', ['INV-1043'], '€860.00'),
        ];

        return Card::make($design)
            ->title('Invoices')
            ->body(Stack::make($design)
                ->space(Size::Md)
                ->attributes(['x-data' => "{ tab: 'all' }"])
                ->children([
                    self::tabStrip($design, [
                        'all' => 'All',
                        'paid' => 'Paid',
                        'overdue' => 'Overdue',
                    ]),
                    ...$panels,
                ]));
    }

    /**
     * Revenue by month: a labelled bar chart with a year-over-year badge.
     */
    public static function revenue(Design $design): Card
    {
        return Card::make($design)
            ->title('Revenue by month')
            ->body(Chart::bars(
                [4.1, 4.4, 3.9, 4.8, 5.2, 5.0, 5.6, 5.9, 5.4, 6.1, 6.4, 6.9],
                ['J', 'F', 'M', 'A', 'M', 'J', 'J', 'A', 'S', 'O', 'N', 'D'],
                'Revenue by month, January to December',
            ))
            ->footer(Badge::make($design)->label('+18.2% vs last year')->variant(BadgeVariant::Success));
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

    /**
     * @param  array<string, string>  $tabs
     */
    private static function tabStrip(Design $design, array $tabs): MainBackendComponent
    {
        $link = (string) $design->themeManager->processTheme('tabs', 'link');
        $current = (string) $design->themeManager->processTheme('tabs', State::Current->value);

        $items = [];

        foreach ($tabs as $key => $label) {
            $isCurrent = $key === 'all';

            $items[] = self::element($design, ComponentEnum::LI)
                ->setThemes(['tabs' => 'item'])
                ->setContent(
                    self::element($design, ComponentEnum::BUTTON)
                        ->setContent($label)
                        ->setAttributes([
                            'type' => 'button',
                            'role' => 'tab',
                            'id' => 'invoice-tab-'.$key,
                            'aria-controls' => 'invoice-panel-'.$key,
                            'aria-selected' => $isCurrent ? 'true' : 'false',
                            'x-on:click' => "tab = '".$key."'",
                            'x-bind:aria-selected' => "tab === '".$key."'",
                            'x-bind:class' => self::classBinding($link, $current, $key),
                        ])
                );
        }

        return self::element($design, ComponentEnum::DIV)
            ->setThemes(['tabs' => 'container'])
            ->setAttributes([
                'role' => 'tablist',
                'aria-label' => 'Invoice filters',
                'x-cloak' => '',
            ])
            ->setContent(
                self::element($design, ComponentEnum::UL)
                    ->setThemes(['tabs' => 'list'])
                    ->setContents($items)
            );
    }

    /**
     * @param  array<int, string>  $ids
     */
    private static function invoicePanel(Design $design, string $key, array $ids, string $total): MainBackendComponent
    {
        $rows = [];

        foreach ($ids as $id) {
            [$status, $variant, $amount] = self::INVOICES[$id];

            $rows[] = [
                $id,
                Badge::make($design)->label($status)->variant($variant),
                $amount,
                Button::make($design)->label('View')->variant(ButtonVariant::Ghost),
            ];
        }

        $panel = self::element($design, ComponentEnum::DIV)
            ->setAttributes([
                'role' => 'tabpanel',
                'id' => 'invoice-panel-'.$key,
                'aria-labelledby' => 'invoice-tab-'.$key,
                'tabindex' => '0',
                'x-show' => "tab === '".$key."'",
            ]);

        if ($key !== 'all') {
            $panel->setAttribute('x-cloak', '');
        }

        return $panel->setContent(
            Table::make($design)
                ->caption('Invoices')
                ->headers(['Invoice', 'Status', 'Amount', ''])
                ->rows($rows)
                ->footer(['', 'Total due', $total, ''])
                ->component()
        );
    }

    /**
     * The design's own link/current classes, toggled by Alpine. Both sets
     * are complete variants, so exactly one applies at a time.
     */
    private static function classBinding(string $link, string $current, string $key): string
    {
        return sprintf(
            "{ '%s': tab !== '%s', '%s': tab === '%s' }",
            $link,
            $key,
            $current,
            $key,
        );
    }

    /**
     * Plain elements rendered through the design's theme manager: the
     * app's own factory resolves the app's theme folder, which has no tabs
     * theme, so the tablist would render unstyled.
     */
    private static function element(Design $design, ComponentEnum $name): MainBackendComponent
    {
        $component = ComponentFactory::make($name);

        if (! $component instanceof MainBackendComponent) {
            throw new RuntimeException('Dashboard builds MainBackendComponent instances.');
        }

        return $component->setThemeManager($design->themeManager);
    }
}
