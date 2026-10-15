<?php

declare(strict_types=1);

namespace App\Emails\Contracts;

use Illuminate\Contracts\Mail\Mailable;
use Juaniquillo\MjmlBackendComponents\MjmlBackendComponent;

interface EmailInterface
{
    public static function identifier(): string;

    public static function name(): string;

    public static function build(): MjmlBackendComponent;

    /** @return class-string<Mailable> */
    public static function mailer(): string;
}
