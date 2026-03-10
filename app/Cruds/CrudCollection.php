<?php

namespace App\Cruds;

use App\Cruds\Squema\Flux\FluxCrud;
use App\Cruds\Squema\Simple\SimpleCrud;
use App\Cruds\Squema\Unstyled\UnstyledCrud;

class CrudCollection
{
    public static function list(?array $values = null, ?array $errors = null) : array
    {
        return [
            UnstyledCrud::IDENTIFIER => [
                'identifier' => UnstyledCrud::IDENTIFIER,
                'name' => UnstyledCrud::NAME,
                'component' => UnstyledCrud::build($values, $errors),
                'crud' => UnstyledCrud::class,
            ],
            SimpleCrud::IDENTIFIER => [
                'identifier' => SimpleCrud::IDENTIFIER,
                'name' => SimpleCrud::NAME,
                'component' => SimpleCrud::build($values, $errors),
                'crud' => SimpleCrud::class,
            ],
            FluxCrud::IDENTIFIER => [
                'identifier' => FluxCrud::IDENTIFIER,
                'name' => FluxCrud::NAME,
                'component' => FluxCrud::build($values, $errors),
                'crud' => FluxCrud::class,
            ],

        ];
    }

    public static function getCrudByIdentifier(string $identifier, ?array $values = null, ?array $errors = null): ?array
    {
        return self::list(
            values: $values,
            errors: $errors
        )[$identifier] ?? null;
    }
}
