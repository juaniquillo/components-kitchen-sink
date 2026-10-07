<?php

declare(strict_types=1);

namespace App\Cruds\Contracts;

use Juaniquillo\BackendComponents\Contracts\BackendComponent;
use Juaniquillo\CrudAssistant\Contracts\InputCollectionInterface;

interface Crud
{
    public static function make(): InputCollectionInterface;

    public static function build(?array $values = null, ?array $errors = null): BackendComponent;
}
