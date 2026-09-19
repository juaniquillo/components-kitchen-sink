<?php

declare(strict_types=1);

namespace App\Cruds\Themes;

use BackedEnum;
use Closure;
use Juaniquillo\CrudAssistant\Contracts\InputInterface;

class Inputs
{
    public static function wrapper(): Closure
    {
        return static function (InputInterface $input, BackedEnum $type): array {

            return [
                'inputs' => [
                    'wrapper',
                    'wrapper-dark',
                ],
            ];
        };
    }

    public static function inputs(): Closure
    {
        return static function (InputInterface $input, BackedEnum $type): array {

            return [
                'inputs' => [
                    'text',
                    'text-dark',
                ],
                'size' => 'w-full',
            ];
        };
    }

    public static function label(): Closure
    {
        return static function (InputInterface $input, BackedEnum $type): array {

            return [
                'margin' => 'bottom-xs',
                'display' => 'block',
            ];
        };
    }
}
