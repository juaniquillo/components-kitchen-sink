<?php

declare(strict_types=1);

namespace App\Cruds\Actions\Validation;

use Juaniquillo\CrudAssistant\Concerns\IsRecipe;
use Juaniquillo\CrudAssistant\Contracts\RecipeInterface;

class LaravelValidationMessagesRecipe implements RecipeInterface
{
    use IsRecipe;

    public function __construct()
    {
        $this->action = LaravelValidationMessagesAction::class;
    }
}
