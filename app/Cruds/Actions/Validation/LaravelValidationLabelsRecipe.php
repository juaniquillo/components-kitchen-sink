<?php

declare(strict_types=1);

namespace App\Cruds\Actions\Validation;

use Closure;
use Juaniquillo\CrudAssistant\Concerns\IsRecipe;
use Juaniquillo\CrudAssistant\Contracts\RecipeInterface;

class LaravelValidationLabelsRecipe implements RecipeInterface
{
    use IsRecipe;

    /**
     * Overwrites label
     */
    public string $label;

    /**
     * Callback that overwrites output
     */
    public Closure $callback;

    /**
     * Recipe action
     *
     * @var string
     */
    protected $action = LaravelValidationLabelsAction::class;
}
