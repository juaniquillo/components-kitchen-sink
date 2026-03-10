<?php

declare(strict_types=1);

namespace App\Cruds\Actions\Validation;

use Juaniquillo\CrudAssistant\Action;
use Juaniquillo\CrudAssistant\Contracts\ActionInterface;
use Juaniquillo\CrudAssistant\Contracts\DataContainerInterface;
use Juaniquillo\CrudAssistant\Contracts\InputInterface;

/**
 * Laravel validation messages action.
 */
class LaravelValidationMessagesAction extends Action implements ActionInterface
{
    /**
     * Execute action on input.
     *
     * @return DataContainerInterface
     */
    public function execute(InputInterface $input)
    {
        $recipe = $input->getRecipe(static::class);
        $output = $this->getOutput();

        if ($recipe) {
            $callback = $recipe->callback ?? null;
            if (\is_callable($callback)) {
                $callback($output, $input);
            } elseif (is_iterable($recipe)) {
                foreach ($recipe as $keyMessage => $message) {
                    $output->$keyMessage = $message;
                }
            }
        }

        return $output;
    }
}
