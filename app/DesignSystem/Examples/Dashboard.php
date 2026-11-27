<?php

declare(strict_types=1);

namespace App\DesignSystem\Examples;

use Juaniquillo\BackendComponents\Enums\ComponentEnum;
use Juaniquillo\BackendComponents\Factories\ComponentFactory;
use Juaniquillo\BackendComponents\Factories\LocalThemeComponentFactory;
use Juaniquillo\BackendComponents\MainBackendComponent;
use Juaniquillo\BackendDesignSystem\Components\Alert;
use Juaniquillo\BackendDesignSystem\Components\Badge;
use Juaniquillo\BackendDesignSystem\Components\Breadcrumb;
use Juaniquillo\BackendDesignSystem\Components\Button;
use Juaniquillo\BackendDesignSystem\Components\Card;
use Juaniquillo\BackendDesignSystem\Components\Dialog;
use Juaniquillo\BackendDesignSystem\Components\Field;
use Juaniquillo\BackendDesignSystem\Components\Form as FormComponent;
use Juaniquillo\BackendDesignSystem\Components\Heading;
use Juaniquillo\BackendDesignSystem\Components\Inline;
use Juaniquillo\BackendDesignSystem\Components\Input;
use Juaniquillo\BackendDesignSystem\Components\Link;
use Juaniquillo\BackendDesignSystem\Components\Select;
use Juaniquillo\BackendDesignSystem\Components\Stack;
use Juaniquillo\BackendDesignSystem\Components\Table;
use Juaniquillo\BackendDesignSystem\Components\Text;
use Juaniquillo\BackendDesignSystem\Contracts\DesignComponent;
use Juaniquillo\BackendDesignSystem\Designs\Design;
use Juaniquillo\BackendDesignSystem\Theme\AlertVariant;
use Juaniquillo\BackendDesignSystem\Theme\BadgeVariant;
use Juaniquillo\BackendDesignSystem\Theme\ButtonVariant;
use Juaniquillo\BackendDesignSystem\Theme\Size;
use Juaniquillo\BackendDesignSystem\Theme\State;
use Juaniquillo\BaseBackendComponent\Contracts\BackendComponent;
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
            Button::make($design)->label('New invoice')->variant(ButtonVariant::Primary)
                ->attributes(['command' => 'show-modal', 'commandfor' => 'new-invoice']),
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
                    Chart::sparkline($design, [12, 18, 15, 22, 28, 25, 34, 41, 38, 48]),
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
            self::invoicePanel($design, 'draft', [], null),
        ];

        return Card::make($design)
            ->title('Invoices')
            ->body(Stack::make($design)
                ->space(Size::Md)
                ->attributes(['x-data' => "{ tab: 'all' }"])
                ->children([
                    self::invoiceToolbar($design),
                    self::tabStrip($design, [
                        'all' => 'All',
                        'paid' => 'Paid',
                        'overdue' => 'Overdue',
                        'draft' => 'Draft',
                    ]),
                    ...$panels,
                ]))
            ->footer(Stack::make($design)->space(Size::Sm)->children([
                Inline::make($design)->space(Size::Sm)->children([
                    Text::make($design)->text('Showing 1–3 of 42'),
                    Button::make($design)->label('Previous')->variant(ButtonVariant::Ghost),
                    Button::make($design)->label('1')->variant(ButtonVariant::Primary)->attributes(['aria-current' => 'page']),
                    Button::make($design)->label('2')->variant(ButtonVariant::Ghost)->attributes(['aria-label' => 'Page 2']),
                    Button::make($design)->label('3')->variant(ButtonVariant::Ghost)->attributes(['aria-label' => 'Page 3']),
                    Button::make($design)->label('Next')->variant(ButtonVariant::Ghost),
                ]),
                Link::make($design)->label('View all invoices')->href('#invoices'),
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
                $design,
                [4.1, 4.4, 3.9, 4.8, 5.2, 5.0, 5.6, 5.9, 5.4, 6.1, 6.4, 6.9],
                ['J', 'F', 'M', 'A', 'M', 'J', 'J', 'A', 'S', 'O', 'N', 'D'],
                'Revenue by month, January to December',
                ['January · €4.1k', 'February · €4.4k', 'March · €3.9k', 'April · €4.8k', 'May · €5.2k', 'June · €5.0k', 'July · €5.6k', 'August · €5.9k', 'September · €5.4k', 'October · €6.1k', 'November · €6.4k', 'December · €6.9k'],
            ))
            ->footer(Badge::make($design)->label('+18.2% vs last year')->variant(BadgeVariant::Success));
    }

    /**
     * The Acme brand mark, shared by the sidebar and the mobile bar.
     */
    public static function brand(Design $design): MainBackendComponent
    {
        return self::element($design, ComponentEnum::DIV)
            ->setAttribute('class', 'flex items-center gap-2')
            ->setContents([
                Badge::make($design)->label('A')->component(),
                Heading::make($design)->text('Acme')->level(3)->component(),
            ]);
    }

    /**
     * The app shell sidebar: brand, section nav and user, built from
     * design components. The section links are the design's own links,
     * so the shell follows the active look.
     */
    public static function sidebar(Design $design): MainBackendComponent
    {
        return self::element($design, ComponentEnum::DIV)
            ->setAttribute('class', 'flex flex-col gap-4 lg:sticky lg:top-6 lg:self-start')
            ->setContents([
                self::element($design, ComponentEnum::DIV)
                    ->setAttribute('class', 'hidden items-center gap-2 lg:flex')
                    ->setContent(self::brand($design)),
                self::element($design, ComponentEnum::UL)
                    ->setAttribute('class', 'flex flex-col gap-2')
                    ->setContents(self::sectionItems($design)),
            ]);
    }

    /**
     * The mobile navigation: a native dialog sheet reusing the section
     * nav. The browser provides the backdrop, Escape and focus handling;
     * Alpine only opens it and closes it on backdrop taps.
     */
    public static function menuSheet(Design $design): MainBackendComponent
    {
        $glyph = LocalThemeComponentFactory::make(ComponentEnum::SPAN);

        if (! $glyph instanceof MainBackendComponent) {
            throw new RuntimeException('Dashboard builds MainBackendComponent instances.');
        }

        $close = self::element($design, ComponentEnum::BUTTON)
            ->setContent(
                $glyph
                    ->setAttributes(['aria-hidden' => 'true'])
                    ->setThemes(['nav' => 'icon-close'])
            )
            ->setAttributes([
                'type' => 'button',
                'class' => 'theme-switch__button',
                'aria-label' => 'Close menu',
                'x-on:click' => '$refs.menuPanel.close()',
            ]);

        return self::element($design, ComponentEnum::DIALOG)
            ->setAttributes([
                'id' => 'dashboard-menu',
                'class' => 'dashboard-menu-panel',
                'x-ref' => 'menuPanel',
                'aria-label' => 'Menu',
                'x-on:click' => 'if ($event.target === $event.currentTarget) $event.currentTarget.close()',
            ])
            ->setContents([
                self::element($design, ComponentEnum::DIV)
                    ->setAttribute('class', 'mb-4 flex items-center justify-between gap-3')
                    ->setContents([
                        Heading::make($design)->text('Menu')->level(2)->component(),
                        $close,
                    ]),
                self::element($design, ComponentEnum::UL)
                    ->setAttribute('class', 'flex flex-col gap-2')
                    ->setContents(self::sectionItems($design)),
            ]);
    }

    /**
     * @return array<int, MainBackendComponent>
     */
    private static function sectionItems(Design $design): array
    {
        $sections = [
            ['label' => 'Overview', 'href' => '#overview', 'icon' => 'overview', 'current' => true],
            ['label' => 'Invoices', 'href' => '#invoices', 'icon' => 'invoices'],
            ['label' => 'Clients', 'href' => '#clients', 'icon' => 'clients'],
            ['label' => 'Settings', 'href' => '#settings', 'icon' => 'settings'],
        ];

        $items = [];

        foreach ($sections as $section) {
            $isCurrent = $section['current'] ?? false;

            $link = Link::make($design)
                ->label(self::iconLabel($section['label'], $section['icon']))
                ->href($section['href'])
                ->attributes(['x-on:click' => "\$el.closest('dialog')?.close()"]);

            if ($isCurrent) {
                $link->current();
            }

            $items[] = self::element($design, ComponentEnum::LI)->setContent($link->component());
        }

        return $items;
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
     * Search and status filters above the tabs.
     */
    private static function invoiceToolbar(Design $design): MainBackendComponent
    {
        return self::element($design, ComponentEnum::DIV)
            ->setAttribute('class', 'flex flex-wrap items-end gap-2')
            ->setContents([
                self::element($design, ComponentEnum::DIV)
                    ->setAttribute('class', 'min-w-52 flex-1')
                    ->setContent(
                        Field::make($design)->label('Search')->input(
                            Input::make($design)->name('invoice-search')->placeholder('INV-…')->attributes(['type' => 'search', 'aria-label' => 'Search invoices'])
                        )->component()
                    ),
                self::element($design, ComponentEnum::DIV)
                    ->setAttribute('class', 'w-40')
                    ->setContent(
                        Field::make($design)->label('Status')->input(
                            Select::make($design)->name('invoice-status')->options(['all' => 'All statuses', 'paid' => 'Paid', 'open' => 'Open', 'overdue' => 'Overdue'])
                        )->component()
                    ),
            ]);
    }

    /**
     * The new-invoice layer, opened and closed with invoker commands so
     * no script is involved. The create button lives in the footer and
     * submits the body form by id.
     */
    public static function newInvoiceDialog(Design $design): Dialog
    {
        return Dialog::make($design)
            ->id('new-invoice')
            ->title('New invoice')
            ->body(FormComponent::make($design)
                ->action('#new-invoice')
                ->method('post')
                ->attributes(['id' => 'new-invoice-form'])
                ->fields([
                    Field::make($design)->label('Client')->input('invoice-client')->required(),
                    Field::make($design)->label('Amount')->input(
                        Input::make($design)->name('invoice-amount')->type('number')->placeholder('0.00')
                    ),
                    Field::make($design)->label('Due date')->input(
                        Input::make($design)->name('invoice-due')->type('date')
                    ),
                ]))
            ->footer(Inline::make($design)->children([
                Button::make($design)->label('Cancel')
                    ->attributes(['command' => 'close', 'commandfor' => 'new-invoice']),
                Button::make($design)->label('Create invoice')->variant(ButtonVariant::Primary)->type('submit')
                    ->attributes(['form' => 'new-invoice-form']),
            ]));
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
                            'x-on:keydown.arrow-right.prevent' => "\$el.closest('li').nextElementSibling?.querySelector('button')?.focus()",
                            'x-on:keydown.arrow-left.prevent' => "\$el.closest('li').previousElementSibling?.querySelector('button')?.focus()",
                            'x-on:keydown.home.prevent' => "\$el.closest('ul').querySelector('li:first-child button')?.focus()",
                            'x-on:keydown.end.prevent' => "\$el.closest('ul').querySelector('li:last-child button')?.focus()",
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
    private static function invoicePanel(Design $design, string $key, array $ids, ?string $total): MainBackendComponent
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
            self::invoiceTable($design, $rows, $total)
        );
    }

    /**
     * @param  array<int, array<int, string|DesignComponent|BackendComponent>>  $rows
     */
    private static function invoiceTable(Design $design, array $rows, ?string $total): MainBackendComponent
    {
        $table = Table::make($design)
            ->caption('Invoices')
            ->headers(['Invoice', 'Status', 'Amount', '']);

        if ($rows === []) {
            $table->empty('No draft invoices.');
        } else {
            $table->rows($rows)->footer(['', 'Total due', (string) $total, '']);
        }

        return $table->component();
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
     * A nav label: an icon glyph plus text, passed to the design's Link
     * as one component label. The glyph paints currentColor, so it
     * follows the link colour in every design.
     */
    private static function iconLabel(string $text, string $icon): MainBackendComponent
    {
        $glyph = LocalThemeComponentFactory::make(ComponentEnum::SPAN);

        if (! $glyph instanceof MainBackendComponent) {
            throw new RuntimeException('Dashboard builds MainBackendComponent instances.');
        }

        $label = LocalThemeComponentFactory::make(ComponentEnum::SPAN);

        if (! $label instanceof MainBackendComponent) {
            throw new RuntimeException('Dashboard builds MainBackendComponent instances.');
        }

        return $label
            ->setThemes(['nav' => 'icon-label'])
            ->setContents([
                $glyph
                    ->setAttributes(['aria-hidden' => 'true'])
                    ->setThemes(['nav' => 'icon-'.$icon]),
                $text,
            ]);
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
