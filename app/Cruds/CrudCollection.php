<?php

namespace App\Cruds;

use App\Cruds\Squema\Flux\FluxCrud;
use App\Cruds\Squema\InputGroup\InputGroupCrud;
use App\Cruds\Squema\Simple\SimpleCrud;
use App\Cruds\Squema\Unstyled\UnstyledCrud;

class CrudCollection
{
    public static function list(?array $values = null, ?array $errors = null) : array
    {
        return [
            UnstyledCrud::IDENTIFIER => [
                'name' => UnstyledCrud::NAME,
                'description' => UnstyledCrud::description(),
                'component' => UnstyledCrud::build($values, $errors),
                'crud' => UnstyledCrud::class,
            ],
            SimpleCrud::IDENTIFIER => [
                'name' => SimpleCrud::NAME,
                'description' => SimpleCrud::description(),
                'component' => SimpleCrud::build($values, $errors),
                'crud' => SimpleCrud::class,
            ],
            FluxCrud::IDENTIFIER => [
                'name' => FluxCrud::NAME,
                'description' => FluxCrud::description(),
                'component' => FluxCrud::build($values, $errors),
                'crud' => FluxCrud::class,
            ],
            InputGroupCrud::IDENTIFIER => [
                'name' => InputGroupCrud::NAME,
                'description' => InputGroupCrud::description(),
                'component' => InputGroupCrud::build($values, $errors),
                'crud' => InputGroupCrud::class,
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
