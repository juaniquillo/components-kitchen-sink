<?php

declare(strict_types=1);

namespace App\Emails\Messages;

use App\Emails\Contracts\EmailInterface;
use App\Mail\SendNotificationEmail;
use Illuminate\Contracts\Mail\Mailable;
use Juaniquillo\MjmlBackendComponents\Builders\MjmlComponentBuilder;
use Juaniquillo\MjmlBackendComponents\Enums\MjmlComponentEnum;
use Juaniquillo\MjmlBackendComponents\MjmlBackendComponent;

class NotificationEmail implements EmailInterface
{
    public static function name(): string
    {
        return 'Notification Email';
    }

    public static function identifier(): string
    {
        return 'notification';
    }

    /** @return class-string<Mailable> */
    public static function mailer(): string
    {
        return SendNotificationEmail::class;
    }

    public static function build(): MjmlBackendComponent
    {
        return MjmlComponentBuilder::document([
            MjmlComponentBuilder::head([
                MjmlComponentBuilder::make(MjmlComponentEnum::TITLE)
                    ->setContent('You have a new notification'),
                MjmlComponentBuilder::preview('You have a new notification'),
                MjmlComponentBuilder::font('Inter', 'https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700&display=swap'),
                self::globalAttributes('#059669'),
            ]),
            MjmlComponentBuilder::make(MjmlComponentEnum::BODY)
                ->setAttributes([
                    'background-color' => '#eef2f7',
                    'width' => '600px',
                ])
                ->setContents([
                    self::masthead(),
                    self::alert(),
                    self::details(),
                    self::footer(),
                ]),
        ])->setAttributes([
            'lang' => 'en',
            'dir' => 'ltr',
        ]);
    }

    private static function column(array $contents, array $attributes = []): MjmlBackendComponent
    {
        return MjmlComponentBuilder::make(MjmlComponentEnum::COLUMN)
            ->setAttributes($attributes)
            ->setContents($contents);
    }

    private static function masthead(): MjmlBackendComponent
    {
        return MjmlComponentBuilder::section([
            self::column([
                MjmlComponentBuilder::make(MjmlComponentEnum::IMAGE)
                    ->setAttributes([
                        'src' => asset('images/mail/yellow-lightning-bolt.png'),
                        'alt' => 'The Workbench',
                        'width' => '64px',
                        'href' => 'https://example.com',
                        'align' => 'center',
                        'padding' => '0 24px 4px',
                    ]),
                MjmlComponentBuilder::make(MjmlComponentEnum::DIVIDER),
            ]),
        ], [
            'background-color' => '#ffffff',
            'padding' => '24px 0 0',
        ]);
    }

    private static function alert(): MjmlBackendComponent
    {
        return MjmlComponentBuilder::section([
            self::column([
                MjmlComponentBuilder::text('Hi <strong>{{ $name }}</strong>,', [
                    'align' => 'center',
                    'font-size' => '17px',
                ]),
                MjmlComponentBuilder::text('New notification', [
                    'mj-class' => 'h1',
                    'align' => 'center',
                ]),
                MjmlComponentBuilder::text('You have received a new notification. Log in to your account to view the details and take action.', [
                    'align' => 'center',
                ]),
                MjmlComponentBuilder::make(MjmlComponentEnum::SPACER)
                    ->setAttribute('height', '8px'),
                MjmlComponentBuilder::make(MjmlComponentEnum::BUTTON)
                    ->setAttributes([
                        'href' => 'https://example.com/notifications',
                        'align' => 'center',
                    ])
                    ->setContent('View Notification'),
            ]),
        ], [
            'background-color' => '#ffffff',
            'padding' => '0',
        ]);
    }

    private static function details(): MjmlBackendComponent
    {
        return MjmlComponentBuilder::section([
            self::column([
                MjmlComponentBuilder::text('What happened', [
                    'mj-class' => 'h2',
                    'padding' => '8px 24px 0',
                ]),
                MjmlComponentBuilder::text('There is new activity on your account that needs your attention. Nothing is urgent, but it is worth a look.', [
                    'padding' => '0 24px 8px',
                ]),
                MjmlComponentBuilder::text('What to do next', [
                    'mj-class' => 'h2',
                    'padding' => '8px 24px 0',
                ]),
                MjmlComponentBuilder::text('Open your notifications to review the details. If everything looks right, no further action is needed.', [
                    'padding' => '0 24px 8px',
                ]),
            ]),
        ], [
            'background-color' => '#ecfdf5',
            'padding' => '8px 0',
        ]);
    }

    private static function footer(): MjmlBackendComponent
    {
        return MjmlComponentBuilder::section([
            self::column([
                MjmlComponentBuilder::make(MjmlComponentEnum::DIVIDER),
                MjmlComponentBuilder::text('You are receiving this because you enabled notifications at example.com. <a href="https://example.com/unsubscribe" style="color:#64748b;">Unsubscribe</a>', [
                    'mj-class' => 'meta',
                    'align' => 'center',
                ]),
            ]),
        ], [
            'background-color' => '#ffffff',
            'padding' => '0 0 24px',
        ]);

    }

    private static function globalAttributes(string $buttonColor = '#059669'): MjmlBackendComponent
    {
        return MjmlComponentBuilder::attributes([
            MjmlComponentBuilder::make(MjmlComponentEnum::ALL)
                ->setAttributes([
                    'font-family' => 'Inter, Arial, sans-serif',
                ]),
            MjmlComponentBuilder::make(MjmlComponentEnum::TEXT)
                ->setAttributes([
                    'color' => '#334155',
                    'font-size' => '15px',
                    'line-height' => '24px',
                    'padding' => '8px 24px',
                ]),
            MjmlComponentBuilder::make(MjmlComponentEnum::BUTTON)
                ->setAttributes([
                    'background-color' => $buttonColor,
                    'color' => '#ffffff',
                    'font-size' => '15px',
                    'font-weight' => '600',
                    'border-radius' => '8px',
                    'inner-padding' => '12px 28px',
                ]),
            MjmlComponentBuilder::make(MjmlComponentEnum::DIVIDER)
                ->setAttributes([
                    'border-color' => '#e2e8f0',
                    'border-width' => '1px',
                    'padding' => '16px 24px',
                ]),
            MjmlComponentBuilder::make(MjmlComponentEnum::MJML_CLASS)
                ->setAttributes([
                    'name' => 'h1',
                    'font-size' => '26px',
                    'line-height' => '34px',
                    'font-weight' => '700',
                    'color' => '#0f172a',
                ]),
            MjmlComponentBuilder::make(MjmlComponentEnum::MJML_CLASS)
                ->setAttributes([
                    'name' => 'h2',
                    'font-size' => '19px',
                    'line-height' => '26px',
                    'font-weight' => '700',
                    'color' => '#0f172a',
                ]),
            MjmlComponentBuilder::make(MjmlComponentEnum::MJML_CLASS)
                ->setAttributes([
                    'name' => 'meta',
                    'font-size' => '12px',
                    'line-height' => '18px',
                    'color' => '#64748b',
                ]),
        ]);
    }
}
