<?php

use Juaniquillo\BackendDesignSystem\Theme\AlertVariant;

// Soft dual: tinted surfaces with a hairline border. On dark the tint is
// translucent and the border carries the hue, so warning stays warm
// without turning into the same brown as danger.
return [
    AlertVariant::Info->value => 'border border-sky-200 bg-sky-50 text-sky-900 dark:border-sky-500/50 dark:bg-sky-400/10 dark:text-sky-100 px-4 py-3',
    AlertVariant::Success->value => 'border border-emerald-200 bg-emerald-50 text-emerald-900 dark:border-emerald-500/50 dark:bg-emerald-400/10 dark:text-emerald-100 px-4 py-3',
    AlertVariant::Warning->value => 'border border-amber-200 bg-amber-50 text-amber-900 dark:border-amber-400/60 dark:bg-amber-400/15 dark:text-amber-50 px-4 py-3',
    AlertVariant::Danger->value => 'border border-rose-200 bg-rose-50 text-rose-900 dark:border-rose-500/50 dark:bg-rose-400/10 dark:text-rose-100 px-4 py-3',
];
