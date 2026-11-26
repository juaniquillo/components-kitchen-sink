<?php

use Juaniquillo\BackendDesignSystem\Theme\AlertVariant;

// Classic dual: tinted surfaces with a severity rail on the left,
// paired for light and dark.
return [
    AlertVariant::Info->value => 'border border-sky-200 border-l-4 border-l-sky-600 bg-sky-50 text-sky-900 dark:border-sky-900 dark:border-l-sky-400 dark:bg-sky-950 dark:text-sky-100 px-4 py-3',
    AlertVariant::Success->value => 'border border-emerald-200 border-l-4 border-l-emerald-600 bg-emerald-50 text-emerald-900 dark:border-emerald-900 dark:border-l-emerald-400 dark:bg-emerald-950 dark:text-emerald-100 px-4 py-3',
    AlertVariant::Warning->value => 'border border-yellow-200 border-l-4 border-l-yellow-500 bg-yellow-50 text-yellow-900 dark:border-yellow-800 dark:border-l-yellow-400 dark:bg-yellow-400/10 dark:text-yellow-100 px-4 py-3',
    AlertVariant::Danger->value => 'border border-red-200 border-l-4 border-l-red-600 bg-red-50 text-red-900 dark:border-red-900 dark:border-l-red-400 dark:bg-red-950 dark:text-red-100 px-4 py-3',
];
