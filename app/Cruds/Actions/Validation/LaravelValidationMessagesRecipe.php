<?php

declare(strict_types=1);

namespace App\Cruds\Actions\Validation;

use Juaniquillo\CrudAssistant\Contracts\RecipeInterface;
use Juaniquillo\CrudAssistant\RecipeContainer;

class LaravelValidationMessagesRecipe extends RecipeContainer implements RecipeInterface
{
    /**
     * Recipe action
     *
     * @var string
     */
    protected $action = LaravelValidationMessagesAction::class;
}
