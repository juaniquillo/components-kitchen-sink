<?php

declare(strict_types=1);

namespace App\DesignSystem\Boxes\Contracts;

use App\Components\Options\GroupOptions;
use App\DesignSystem\Examples\Example;
use Juaniquillo\BackendDesignSystem\Contracts\DesignComponent;
use Juaniquillo\BackendDesignSystem\Designs\Design;
use Juaniquillo\BaseBackendComponent\Contracts\BackendComponent;

interface Box
{
    public function options(): GroupOptions;

    /** @return array<int, DesignComponent|BackendComponent> */
    public function components(Design $design): array;

    /** @return array<int, Example> */
    public function examples(): array;
}
