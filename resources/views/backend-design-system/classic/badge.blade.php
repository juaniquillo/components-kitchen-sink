<?php

use Juaniquillo\BackendDesignSystem\Theme\BadgeVariant;

// Classic dual: small bordered chips with tinted backgrounds, paired per mode.
return [
    BadgeVariant::Default->value => 'inline-flex items-center border border-slate-200 bg-slate-100 px-2 py-0.5 text-xs font-medium text-slate-700 dark:border-slate-700 dark:bg-slate-800 dark:text-slate-300',
    BadgeVariant::Success->value => 'inline-flex items-center border border-emerald-200 bg-emerald-50 px-2 py-0.5 text-xs font-medium text-emerald-700 dark:border-emerald-900 dark:bg-emerald-950 dark:text-emerald-300',
    BadgeVariant::Info->value => 'inline-flex items-center border border-sky-200 bg-sky-50 px-2 py-0.5 text-xs font-medium text-sky-700 dark:border-sky-900 dark:bg-sky-950 dark:text-sky-300',
    BadgeVariant::Warning->value => 'inline-flex items-center border border-yellow-200 bg-yellow-50 px-2 py-0.5 text-xs font-medium text-yellow-800 dark:border-yellow-800 dark:bg-yellow-400/10 dark:text-yellow-200',
    BadgeVariant::Danger->value => 'inline-flex items-center border border-red-200 bg-red-50 px-2 py-0.5 text-xs font-medium text-red-700 dark:border-red-900 dark:bg-red-950 dark:text-red-300',
];
