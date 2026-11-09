<?php

declare(strict_types=1);

namespace App\Cruds\Actions\Validation;

use Closure;
use Juaniquillo\CrudAssistant\Concerns\IsRecipe;
use Juaniquillo\CrudAssistant\Contracts\RecipeInterface;

final class LaravelValidationRulesRecipe implements RecipeInterface
{
    use IsRecipe;

    public function __construct(
        public readonly array|Closure|null $rules
    ) {
        $this->action = LaravelValidationRulesAction::class;
    }
}
