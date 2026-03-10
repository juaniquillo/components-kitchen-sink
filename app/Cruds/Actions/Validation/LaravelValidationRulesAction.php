<?php

declare(strict_types=1);

namespace App\Cruds\Actions\Validation;

use Juaniquillo\CrudAssistant\Action;
use Juaniquillo\CrudAssistant\Contracts\ActionInterface;
use Juaniquillo\CrudAssistant\Contracts\DataContainerInterface;
use Juaniquillo\CrudAssistant\Contracts\InputInterface;
use Juaniquillo\CrudAssistant\InputCollection;

/**
 * Laravel validation rules action.
 */
class LaravelValidationRulesAction extends Action implements ActionInterface
{
    public function __construct(
        protected array $requestArray = [],
        protected $model = null
    ) {}

    /**
     * Execute action on input.
     *
     * @return DataContainerInterface
     */
    public function execute(InputCollection|InputInterface|\IteratorAggregate $input)
    {
        $output = $this->getOutput();

        $name = $input->getName();

        /** @var LaravelValidationRulesRecipe $recipe */
        $recipe = $input->getRecipe(self::getIdentifier());
        $rules = $recipe->rules ?? null;

        if ($rules) {

            if (\is_callable($rules)) {
                $output->add(
                    $rules($input, $this->requestArray, $this->model)
                );
            } else {
                $output->$name = $rules;
            }
        }

        return $output;
    }
}
