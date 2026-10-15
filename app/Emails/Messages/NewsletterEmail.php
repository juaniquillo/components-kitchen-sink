<?php

declare(strict_types=1);

namespace App\Emails\Messages;

use App\Emails\Contracts\EmailInterface;
use App\Mail\SendNewsletterEmail;
use Illuminate\Contracts\Mail\Mailable;
use Juaniquillo\MjmlBackendComponents\Builders\MjmlComponentBuilder;
use Juaniquillo\MjmlBackendComponents\Enums\MjmlComponentEnum;
use Juaniquillo\MjmlBackendComponents\MjmlBackendComponent;

class NewsletterEmail implements EmailInterface
{
    public static function name(): string
    {
        return 'Newsletter Email';
    }

    public static function identifier(): string
    {
        return 'newsletter';
    }

    /** @return class-string<Mailable> */
    public static function mailer(): string
    {
        return SendNewsletterEmail::class;
    }

    public static function build(): MjmlBackendComponent
    {
        return MjmlComponentBuilder::document([
            MjmlComponentBuilder::head([
                MjmlComponentBuilder::make(MjmlComponentEnum::TITLE)
                    ->setContent('The Workbench — Issue 42'),
                MjmlComponentBuilder::preview('Issue 42: three small changes that survive a busy week.'),
                MjmlComponentBuilder::font('Inter', 'https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700&display=swap'),
                MjmlComponentBuilder::attributes([
                    '<mj-all font-family="Inter, Arial, sans-serif" />',
                    '<mj-text color="#334155" font-size="15px" line-height="24px" padding="8px 24px" />',
                    '<mj-button background-color="#4f46e5" color="#ffffff" font-size="15px" font-weight="600" border-radius="8px" inner-padding="12px 28px" />',
                    '<mj-divider border-color="#e2e8f0" border-width="1px" padding="16px 24px" />',
                    '<mj-class name="h1" font-size="26px" line-height="34px" font-weight="700" color="#0f172a" />',
                    '<mj-class name="h2" font-size="19px" line-height="26px" font-weight="700" color="#0f172a" />',
                    '<mj-class name="meta" font-size="12px" line-height="18px" color="#64748b" />',
                ]),
            ]),
            MjmlComponentBuilder::make(MjmlComponentEnum::BODY)
                ->setAttributes([
                    'background-color' => '#eef2f7',
                    'width' => '600px',
                ])
                ->setContents([
                    self::masthead(),
                    self::hero(),
                    self::story(
                        '1. Raise the monitor, not the chair',
                        'The top of the screen should sit level with your eyes. Raising the chair instead just moves the problem to your feet.',
                        'A monitor raised to eye level on a wooden riser'
                    ),
                    self::story(
                        '2. Set the keyboard to elbow height',
                        'Forearms parallel to the floor. If the desk cannot go lower, a keyboard tray costs less than a new desk and fixes the same problem.',
                        'A split keyboard positioned at elbow height'
                    ),
                    self::story(
                        '3. Give your eyes a distant target',
                        'Follow the 20-20-20 rule: every 20 minutes, look at something 20 feet away for 20 seconds. A window view beats a blank wall.',
                        'A desk by a window with a distant view to rest the eyes'
                    ),
                    self::ctaBand(),
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
                MjmlComponentBuilder::text('Issue 42 &middot; 10 August 2026', [
                    'mj-class' => 'meta',
                    'align' => 'center',
                ]),
                MjmlComponentBuilder::make(MjmlComponentEnum::DIVIDER),
            ]),
        ], [
            'background-color' => '#ffffff',
            'padding' => '24px 0 0',
        ]);
    }

    private static function hero(): MjmlBackendComponent
    {
        return MjmlComponentBuilder::section([
            self::column([
                MjmlComponentBuilder::text('Hi <strong>{{ $name }}</strong>,', [
                    'align' => 'center',
                    'font-size' => '17px',
                ]),
                MjmlComponentBuilder::text('Desk ergonomics that actually stick', [
                    'mj-class' => 'h1',
                    'align' => 'center',
                ]),
                MjmlComponentBuilder::text('Most ergonomics advice fails because it asks you to remember something. The three changes below work because they are physical defaults — once set, they hold without any effort from you.', [
                    'align' => 'center',
                ]),
                MjmlComponentBuilder::make(MjmlComponentEnum::SPACER)
                    ->setAttribute('height', '8px'),
                MjmlComponentBuilder::make(MjmlComponentEnum::BUTTON)
                    ->setAttributes([
                        'href' => 'https://example.com/newsletter/issue-42',
                        'align' => 'center',
                    ])
                    ->setContent('Read the full issue'),
            ]),
        ], [
            'background-color' => '#ffffff',
            'padding' => '0',
        ]);
    }

    private static function story(string $heading, string $body, string $imageAlt): MjmlBackendComponent
    {
        return MjmlComponentBuilder::section([
            self::column([
                MjmlComponentBuilder::make(MjmlComponentEnum::IMAGE)
                    ->setAttributes([
                        'src' => asset('images/mail/newsletter.jpg'),
                        'alt' => $imageAlt,
                        'width' => '170px',
                        'border-radius' => '8px',
                        'padding' => '8px 0 8px 24px',
                    ]),
            ], [
                'width' => '35%',
                'vertical-align' => 'top',
            ]),
            self::column([
                MjmlComponentBuilder::text($heading, [
                    'mj-class' => 'h2',
                    'padding' => '8px 24px 0',
                ]),
                MjmlComponentBuilder::text($body, [
                    'padding' => '0 24px 8px',
                ]),
            ], [
                'width' => '65%',
                'vertical-align' => 'top',
            ]),
        ], [
            'background-color' => '#ffffff',
            'padding' => '0',
        ]);
    }

    private static function ctaBand(): MjmlBackendComponent
    {
        return MjmlComponentBuilder::section([
            self::column([
                MjmlComponentBuilder::text('Get the checklist', [
                    'mj-class' => 'h2',
                    'align' => 'center',
                ]),
                MjmlComponentBuilder::text('All three setups on one printable page — tape it to the side of your monitor.', [
                    'align' => 'center',
                ]),
                MjmlComponentBuilder::make(MjmlComponentEnum::SPACER)
                    ->setAttribute('height', '8px'),
                MjmlComponentBuilder::make(MjmlComponentEnum::BUTTON)
                    ->setAttributes([
                        'href' => 'https://example.com/newsletter/checklist',
                        'align' => 'center',
                    ])
                    ->setContent('Download checklist'),
            ]),
        ], [
            'background-color' => '#eef2ff',
            'padding' => '8px 0',
        ]);
    }

    private static function footer(): MjmlBackendComponent
    {
        return MjmlComponentBuilder::section([
            self::column([
                MjmlComponentBuilder::make(MjmlComponentEnum::DIVIDER),
                MjmlComponentBuilder::text('You are receiving this because you subscribed at example.com. <a href="https://example.com/unsubscribe" style="color:#64748b;">Unsubscribe</a>', [
                    'mj-class' => 'meta',
                    'align' => 'center',
                ]),
            ]),
        ], [
            'background-color' => '#ffffff',
            'padding' => '0 0 24px',
        ]);
    }
}
