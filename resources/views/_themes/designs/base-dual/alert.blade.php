<?php

use App\DesignSystem\Theme\AlertVariant;

// Base dual: answering to the theme toggle.
return [
    AlertVariant::Info->value => 'border border-blue-300 bg-blue-50 text-blue-900 dark:border-blue-800 dark:bg-blue-950 dark:text-blue-200 px-4 py-3',
    AlertVariant::Success->value => 'border border-green-300 bg-green-50 text-green-900 dark:border-green-800 dark:bg-green-950 dark:text-green-200 px-4 py-3',
    AlertVariant::Warning->value => 'border border-yellow-300 bg-yellow-50 text-yellow-900 dark:border-yellow-800 dark:bg-yellow-950 dark:text-yellow-200 px-4 py-3',
    AlertVariant::Danger->value => 'border border-red-300 bg-red-50 text-red-900 dark:border-red-800 dark:bg-red-950 dark:text-red-200 px-4 py-3',
];
