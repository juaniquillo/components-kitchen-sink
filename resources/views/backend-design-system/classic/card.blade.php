<?php

// Classic dual: hairline card with a sub-panel header, paired per mode.
// The container is a full-height column and the foot carries mt-auto,
// so cards sharing a grid row line their footers up with the tallest.
return [
    'container' => 'flex h-full flex-col border border-slate-200 bg-white shadow-sm dark:border-slate-700 dark:bg-slate-800',
    'title' => 'border-b border-slate-200 bg-slate-50 px-4 py-2 font-semibold text-slate-900 dark:border-slate-700 dark:bg-slate-700/60 dark:text-slate-100',
    'body' => 'px-4 py-3 text-sm text-slate-700 dark:text-slate-300',
    'foot' => 'mt-auto border-t border-slate-200 bg-slate-50 px-4 py-2 dark:border-slate-700 dark:bg-slate-700/60',
];
