<?php

declare(strict_types=1);

namespace App\Builder\Scaffolds;

/**
 * The kind of input a scaffold form asks for.
 */
enum ScaffoldFieldType: string
{
    case NUMBER = 'number';
    case TEXT = 'text';
    case CHECKBOX = 'checkbox';
}
