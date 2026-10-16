<?php

declare(strict_types=1);

namespace App\Emails;

use App\Emails\Contracts\EmailInterface;
use App\Emails\Contracts\EmailPresenterInterface;
use App\Emails\Messages\NewsletterEmail;
use App\Emails\Messages\NotificationEmail;
use App\Emails\Messages\WelcomeEmail;
use App\Emails\Presenters\EmailPresenter;
use Juaniquillo\BackendComponents\Builders\ComponentBuilder;
use Juaniquillo\BackendComponents\Contracts\BackendComponent;
use Juaniquillo\BackendComponents\Enums\ComponentEnum;

final class EmailCollection
{
    /** @return array<string, class-string<EmailInterface>> */
    public static function list(): array
    {
        return [
            WelcomeEmail::identifier() => WelcomeEmail::class,
            NotificationEmail::identifier() => NotificationEmail::class,
            NewsletterEmail::identifier() => NewsletterEmail::class,
        ];
    }

    public static function getByIdentifier(string $identifier): ?EmailInterface
    {
        $emails = self::list();

        if (! array_key_exists($identifier, $emails)) {
            return null;
        }

        $emailClass = $emails[$identifier];

        return new $emailClass;
    }

    /**
     * @param  class-string<EmailPresenterInterface>  $presenter
     * @return array<string, BackendComponent>
     */
    public static function present(string $presenter = EmailPresenter::class): array
    {
        $modals = [];

        foreach (self::list() as $key => $email) {
            $modals[$key] = $presenter::present(
                new $email,
                route('email.preview', ['name' => $key])
            );
        }

        return $modals;
    }

    /** @param array<string, BackendComponent> $emails */
    public static function presentGroup(array $emails = []): BackendComponent
    {
        $emails = $emails ?: self::present();

        $container = ComponentBuilder::make(ComponentEnum::DIV)
            ->setTheme('margin', 'top-md')
            ->setTheme('text', 'center')
            ->setTheme('padding', 'sm')
            ->setTheme('display', 'flex')
            ->setTheme('flex', [
                'gap-md',
                'justify-center',
                'wrap',
            ])
            ->setContents($emails);

        return $container;
    }
}
