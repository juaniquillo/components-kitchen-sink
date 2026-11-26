<?php

use Juaniquillo\BackendDesignSystem\Theme\State;

// Classic dual: quiet trail, paired for light and dark.
return [
    'container' => 'text-sm',
    'list' => 'flex flex-wrap items-center',
    'item' => 'flex items-center',
    'link' => 'text-slate-500 hover:text-slate-900 dark:text-slate-400 dark:hover:text-slate-100',
    State::Current->value => 'font-medium text-slate-900 dark:text-slate-100',
    'separator' => 'mx-2 text-slate-400 dark:text-slate-600',
];
