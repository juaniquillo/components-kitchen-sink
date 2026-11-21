<?php

use App\DesignSystem\Theme\AlertVariant;

// Base design: light only.
return [
    AlertVariant::Info->value => 'border border-blue-300 bg-blue-50 text-blue-900 px-4 py-3',
    AlertVariant::Success->value => 'border border-green-300 bg-green-50 text-green-900 px-4 py-3',
    AlertVariant::Warning->value => 'border border-yellow-300 bg-yellow-50 text-yellow-900 px-4 py-3',
    AlertVariant::Danger->value => 'border border-red-300 bg-red-50 text-red-900 px-4 py-3',
];
