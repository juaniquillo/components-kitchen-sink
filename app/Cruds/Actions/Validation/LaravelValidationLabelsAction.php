<?php

declare(strict_types=1);

namespace App\Cruds\Actions\Validation;

use Juaniquillo\CrudAssistant\Action;
use Juaniquillo\CrudAssistant\Contracts\ActionInterface;
use Juaniquillo\CrudAssistant\Contracts\DataContainerInterface;
use Juaniquillo\CrudAssistant\Contracts\InputInterface;
use Juaniquillo\CrudAssistant\InputCollection;

/**
 * Laravel validation labels action.
 */
class LaravelValidationLabelsAction extends Action implements ActionInterface
{
    /**
     * Execute action on input.
     *
     * @return DataContainerInterface
     */
    public function execute(InputCollection|InputInterface|\IteratorAggregate $input)
    {
        $output = $this->getOutput();

        $name = $input->getName();
        $recipe = $input->getRecipe(static::class);
        $label = $recipe->label ?? $input->getLabel();

        if ($recipe) {
            $callback = $recipe->callback ?? null;
            if (\is_callable($callback)) {
                $callback($output, $input);

                return $output;
            }
        }

        $output->$name = $label;

        return $output;
    }
}
