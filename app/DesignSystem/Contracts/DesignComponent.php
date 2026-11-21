<?php

declare(strict_types=1);

namespace App\DesignSystem\Contracts;

use Illuminate\Contracts\Support\Htmlable;
use Juaniquillo\BaseBackendComponent\Contracts\BackendComponent;

/**
 * A design-system component: fixed named slots in, a plain backend
 * component out.
 *
 * A slot takes a string or any backend component, so composition stays
 * open. Whatever the slots do not cover happens on the returned
 * component, which carries the whole package API (attributes, themes,
 * Livewire). Interaction attributes for the primary element go through
 * attributes(), applied after structure and themes so they merge
 * instead of replacing.
 *
 * Field is the exception to "slots are taken as given": it owns the
 * label/control association, so it writes id, for, required and aria onto
 * whichever label and input it was handed. Only those attributes are
 * touched, never content or themes.
 */
interface DesignComponent extends Htmlable
{
    /**
     * @param  array<string, int|string>  $attributes
     */
    public function attributes(array $attributes): static;

    public function component(): BackendComponent;
}
