<?php

use Juaniquillo\BackendDesignSystem\Theme\BadgeVariant;

// Soft dual: padded chips with translucent tints on dark, paired per mode.
return [
    BadgeVariant::Default->value => 'inline-flex items-center px-2.5 py-1 text-xs font-medium bg-indigo-50 text-indigo-700 dark:bg-indigo-400/15 dark:text-indigo-200 dark:ring-1 dark:ring-indigo-400/30',
    BadgeVariant::Success->value => 'inline-flex items-center px-2.5 py-1 text-xs font-medium bg-emerald-50 text-emerald-700 dark:bg-emerald-400/15 dark:text-emerald-200 dark:ring-1 dark:ring-emerald-400/30',
    BadgeVariant::Info->value => 'inline-flex items-center px-2.5 py-1 text-xs font-medium bg-sky-50 text-sky-700 dark:bg-sky-400/15 dark:text-sky-200 dark:ring-1 dark:ring-sky-400/30',
    BadgeVariant::Warning->value => 'inline-flex items-center px-2.5 py-1 text-xs font-medium bg-amber-50 text-amber-800 dark:bg-amber-400/20 dark:text-amber-100 dark:ring-1 dark:ring-amber-400/40',
    BadgeVariant::Danger->value => 'inline-flex items-center px-2.5 py-1 text-xs font-medium bg-rose-50 text-rose-700 dark:bg-rose-400/15 dark:text-rose-200 dark:ring-1 dark:ring-rose-400/30',
];
