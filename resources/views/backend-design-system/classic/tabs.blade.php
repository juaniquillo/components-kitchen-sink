<?php

use Juaniquillo\BackendDesignSystem\Theme\State;

// Classic dual: underline tabs with a navy current, paired per mode.
return [
    'container' => 'border-b border-slate-200 text-sm dark:border-slate-800',
    'list' => 'flex flex-wrap gap-1',
    'item' => 'flex',
    'link' => 'border-b-2 border-transparent px-3 py-2 text-slate-600 hover:border-slate-300 hover:text-slate-900 dark:text-slate-400 dark:hover:border-slate-600 dark:hover:text-slate-100',
    State::Current->value => 'border-b-2 border-slate-900 px-3 py-2 font-medium text-slate-900 dark:border-slate-100 dark:text-slate-100',
];
