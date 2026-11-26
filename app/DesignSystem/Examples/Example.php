<?php

declare(strict_types=1);

namespace App\DesignSystem\Examples;

use Closure;
use Juaniquillo\BackendDesignSystem\Contracts\DesignComponent;
use Juaniquillo\BackendDesignSystem\Designs\Design;
use Juaniquillo\BaseBackendComponent\Contracts\BackendComponent;

/**
 * One in-context composition of components, as used in a real project.
 *
 * Examples are design-agnostic: they receive the active design when
 * rendered, so every look shows the same composition in its own theme.
 */
final class Example
{
    /**
     * @param  Closure(Design): (DesignComponent|BackendComponent)  $build
     */
    public function __construct(
        public readonly string $label,
        public readonly string $description,
        private readonly Closure $build,
        public readonly ?string $canvas = null,
    ) {}

    public function component(Design $design): DesignComponent|BackendComponent
    {
        return ($this->build)($design);
    }
}
