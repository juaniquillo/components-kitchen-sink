<?php

declare(strict_types=1);

namespace App\Cruds\Actions\Validation;

use Juaniquillo\CrudAssistant\Action;
use Juaniquillo\CrudAssistant\Contracts\ActionInterface;
use Juaniquillo\CrudAssistant\Contracts\InputCollectionInterface;
use Juaniquillo\CrudAssistant\Contracts\InputInterface;

/**
 * Laravel validation labels action.
 */
class LaravelValidationLabelsAction extends Action implements ActionInterface
{
    /**
     * Execute action on input.
     */
    public function execute(InputCollectionInterface|InputInterface $input): void
    {
        $output = $this->getOutput();

        $name = $input->getName();
        $recipe = $input->getRecipe(static::getIdentifier());
        $label = $recipe instanceof LaravelValidationLabelsRecipe ? ($recipe->label ?? $input->getLabel()) : $input->getLabel();

        if ($recipe) {
            $callback = $recipe->callback ?? null;
            if (\is_callable($callback)) {
                $callback($output, $input);

                return;
            }
        }

        $output->$name = $label;
    }
}
