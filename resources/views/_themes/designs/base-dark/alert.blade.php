<?php

use App\DesignSystem\Theme\AlertVariant;

// Base dark: dark only.
return [
    AlertVariant::Info->value => 'border border-blue-800 bg-blue-950 text-blue-200 px-4 py-3',
    AlertVariant::Success->value => 'border border-green-800 bg-green-950 text-green-200 px-4 py-3',
    AlertVariant::Warning->value => 'border border-yellow-800 bg-yellow-950 text-yellow-200 px-4 py-3',
    AlertVariant::Danger->value => 'border border-red-800 bg-red-950 text-red-200 px-4 py-3',
];
