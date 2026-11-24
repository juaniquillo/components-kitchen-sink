<?php

use Juaniquillo\BackendDesignSystem\Theme\State;

// Soft dual: indigo tabs, paired for light and dark.
return [
    'container' => 'border-b border-zinc-200 text-sm dark:border-zinc-800',
    'list' => 'flex flex-wrap gap-1',
    'item' => 'flex',
    'link' => 'border-b-2 border-transparent px-3 py-2 text-zinc-600 hover:border-indigo-300 hover:text-indigo-700 dark:text-zinc-400 dark:hover:border-indigo-700 dark:hover:text-indigo-300',
    State::Current->value => 'border-b-2 border-indigo-600 px-3 py-2 font-medium text-indigo-700 dark:border-indigo-400 dark:text-indigo-300',
];
