<?php

declare(strict_types=1);

namespace App\Emails\Presenters;

use App\Emails\Contracts\EmailInterface;
use App\Emails\Contracts\EmailPresenterInterface;
use Illuminate\Support\Str;
use Juaniquillo\BackendComponents\Enums\ComponentEnum;
use Juaniquillo\BackendComponents\Factories\ComponentFactory;
use Juaniquillo\BackendComponents\Factories\LocalComponentFactory;
use Juaniquillo\BaseBackendComponent\Contracts\BackendComponent;
use Juaniquillo\FluxBackendComponents\Factories\FluxComponentFactory;
use Juaniquillo\FluxBackendComponents\FluxComponentEnum;

final class EmailPresenter implements EmailPresenterInterface
{
    public static function present(EmailInterface $email, string $previewUrl): BackendComponent
    {
        $modalName = 'email-modal-'.Str::slug($email::name());

        return ComponentFactory::make(ComponentEnum::COLLECTION)
            ->setContents([
                FluxComponentFactory::make(FluxComponentEnum::MODAL_TRIGGER)
                    ->setAttribute('name', $modalName)
                    ->setContent(
                        ComponentFactory::make(ComponentEnum::BUTTON)
                            ->setTheme('action', 'default')
                            ->setTheme('padding', 'xs')
                            ->setTheme('border-radius', 'sm')
                            ->setContent($email::name())
                    ),
                FluxComponentFactory::make(FluxComponentEnum::MODAL)
                    ->setAttribute('name', $modalName)
                    ->setTheme('modal', [
                        'default',
                        '3xl',
                    ])
                    ->setContent(
                        FluxComponentFactory::make(FluxComponentEnum::HEADING)
                            ->setTheme('padding', 'bottom-sm')
                            ->setTheme('display', 'flex')
                            ->setTheme('flex', [
                                'gap-md',
                                'justify-center',
                                'wrap',

                            ])
                            ->setAttribute('size', 'lg')
                            ->setContent($email::name())
                            ->setContent(
                                FluxComponentFactory::make(FluxComponentEnum::LINK)
                                    ->setContent('Open in new tab')
                                    ->setAttribute('href', $previewUrl)
                                    ->setAttribute('size', 'sm')
                                    ->setAttribute('target', '_blank')
                            )
                    )
                    ->setContent(
                        LocalComponentFactory::make('iframe')
                            ->setTheme('iframe', 'default')
                            ->setAttributes([
                                'src' => $previewUrl,
                                'loading' => 'lazy',
                                'title' => $email::name(),
                            ])
                    ),
            ]);
    }
}
