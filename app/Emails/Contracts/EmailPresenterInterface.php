<?php

declare(strict_types=1);

namespace App\Emails\Contracts;

use Juaniquillo\BaseBackendComponent\Contracts\BackendComponent;

interface EmailPresenterInterface
{
    public static function present(EmailInterface $email, string $previewUrl): BackendComponent;
}
