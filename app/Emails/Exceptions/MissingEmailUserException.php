<?php

declare(strict_types=1);

namespace App\Emails\Exceptions;

class MissingEmailUserException extends \RuntimeException
{
    public function __construct(string $identifier)
    {
        parent::__construct("Cannot preview email [{$identifier}]: no user available to personalize the greeting.");
    }
}
