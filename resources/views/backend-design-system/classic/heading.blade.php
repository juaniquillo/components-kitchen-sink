<?php

use Juaniquillo\BackendDesignSystem\Theme\HeadingLevel;

// Classic dual: quiet ink headings, paired per mode.
return [
    HeadingLevel::H1->value => 'text-3xl font-bold text-slate-900 dark:text-slate-100',
    HeadingLevel::H2->value => 'text-2xl font-bold text-slate-900 dark:text-slate-100',
    HeadingLevel::H3->value => 'text-xl font-semibold text-slate-900 dark:text-slate-100',
    HeadingLevel::H4->value => 'text-lg font-semibold text-slate-900 dark:text-slate-100',
    HeadingLevel::H5->value => 'text-base font-semibold text-slate-900 dark:text-slate-100',
    HeadingLevel::H6->value => 'text-sm font-semibold text-slate-900 dark:text-slate-100',
];
