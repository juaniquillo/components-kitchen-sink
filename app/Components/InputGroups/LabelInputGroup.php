<?php

namespace App\Components\InputGroups;

use Juaniquillo\BackendComponents\Contracts\BackendComponent;
use Juaniquillo\BackendComponents\Contracts\ContentComponent;
use Juaniquillo\InputComponentAction\Concerns\HasLabel;
use Juaniquillo\InputComponentAction\Concerns\HasWrapper;
use Juaniquillo\InputComponentAction\Concerns\IsInputGroup;
use Juaniquillo\InputComponentAction\Contracts\InputGroup;
use Juaniquillo\InputComponentAction\Utilities\Support;

class LabelInputGroup implements InputGroup
{
    use HasWrapper,
        HasLabel,
        IsInputGroup;

    public function getGroup(): BackendComponent|ContentComponent
    {
        $wrapper = $this->getWrapperComponent() ?? Support::getCollectionWrapper();
        $label = $this->getLabelComponent();
        $input = $this->getInputComponent();

        $components = [];

        if ($label) {
            $components['label'] = $label;
        }

        $components['input'] = $input;

        return $wrapper->setContents($components);
    }
}
