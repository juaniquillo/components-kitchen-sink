<?php

declare(strict_types=1);

namespace App\Emails\Messages;

use App\Emails\Contracts\EmailInterface;
use App\Mail\SendWelcomeEmail;
use Illuminate\Contracts\Mail\Mailable;
use Juaniquillo\MjmlBackendComponents\Builders\MjmlComponentBuilder;
use Juaniquillo\MjmlBackendComponents\Enums\MjmlComponentEnum;
use Juaniquillo\MjmlBackendComponents\MjmlBackendComponent;

class WelcomeEmail implements EmailInterface
{
    public static function name(): string
    {
        return 'Welcome Email';
    }

    public static function identifier(): string
    {
        return 'welcome';
    }

    /** @return class-string<Mailable> */
    public static function mailer(): string
    {
        return SendWelcomeEmail::class;
    }

    public static function build(): MjmlBackendComponent
    {
        return MjmlComponentBuilder::document([
            MjmlComponentBuilder::head([
                MjmlComponentBuilder::make(MjmlComponentEnum::TITLE)
                    ->setContent('Welcome to the platform'),
                MjmlComponentBuilder::preview('Welcome to our platform!'),
                MjmlComponentBuilder::font('Inter', 'https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700&display=swap'),
                self::globalAttributes(),
            ]),
            MjmlComponentBuilder::make(MjmlComponentEnum::BODY)
                ->setAttributes([
                    'background-color' => '#eef2f7',
                    'width' => '600px',
                ])
                ->setContents([
                    self::masthead(),
                    self::hero(),
                    self::prop(
                        'Your dashboard, ready to go',
                        'Track activity, manage settings, and pick up where you left off — everything lives in one place.'
                    ),
                    self::prop(
                        'Learn as you go',
                        'Short guides and templates walk you through your first week, step by step. No manual required.'
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
                MjmlComponentBuilder::text('Welcome aboard!', [
                    'mj-class' => 'h1',
                    'align' => 'center',
                ]),
                MjmlComponentBuilder::text('We are excited to have you on board. Get started by exploring your new dashboard and discovering all the features available to you.', [
                    'align' => 'center',
                ]),
                MjmlComponentBuilder::make(MjmlComponentEnum::SPACER)
                    ->setAttribute('height', '8px'),
                MjmlComponentBuilder::make(MjmlComponentEnum::BUTTON)
                    ->setAttributes([
                        'href' => 'https://example.com/dashboard',
                        'align' => 'center',
                    ])
                    ->setContent('Get Started'),
            ]),
        ], [
            'background-color' => '#ffffff',
            'padding' => '0',
        ]);
    }

    private static function prop(string $heading, string $body): MjmlBackendComponent
    {
        return MjmlComponentBuilder::section([
            self::column([
                MjmlComponentBuilder::text($heading, [
                    'mj-class' => 'h2',
                    'padding' => '8px 24px 0',
                ]),
                MjmlComponentBuilder::text($body, [
                    'padding' => '0 24px 8px',
                ]),
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
                MjmlComponentBuilder::text('Ready when you are', [
                    'mj-class' => 'h2',
                    'align' => 'center',
                ]),
                MjmlComponentBuilder::text('Your workspace is set up and waiting. Jump in whenever you like — we will keep things warm.', [
                    'align' => 'center',
                ]),
                MjmlComponentBuilder::make(MjmlComponentEnum::SPACER)
                    ->setAttribute('height', '8px'),
                MjmlComponentBuilder::make(MjmlComponentEnum::BUTTON)
                    ->setAttributes([
                        'href' => 'https://example.com/dashboard',
                        'align' => 'center',
                    ])
                    ->setContent('Open your dashboard'),
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
                MjmlComponentBuilder::text('You are receiving this because you created an account at example.com.', [
                    'mj-class' => 'meta',
                    'align' => 'center',
                ]),
            ]),
        ], [
            'background-color' => '#ffffff',
            'padding' => '0 0 24px',
        ]);
    }

    private static function globalAttributes(string $buttonColor = '#4f46e5'): MjmlBackendComponent
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
