<?php

declare(strict_types=1);

namespace App\Cruds\Actions\Validation;

use Closure;
use Juaniquillo\CrudAssistant\Concerns\IsRecipe;
use Juaniquillo\CrudAssistant\Contracts\RecipeInterface;

final class LaravelValidationRulesRecipe implements RecipeInterface
{
    use IsRecipe;

    /**
     * Recipe action
     *
     * @var string
     */
    protected $action = LaravelValidationRulesAction::class;

    public function __construct(
        public readonly array|Closure|null $rules
    ) {}
}
