<?php

use Juaniquillo\BackendDesignSystem\Theme\State;

// Soft dual: indigo trail, paired for light and dark.
return [
    'container' => 'text-sm',
    'list' => 'flex flex-wrap items-center',
    'item' => 'flex items-center',
    'link' => 'text-indigo-700 hover:text-indigo-900 dark:text-indigo-300 dark:hover:text-indigo-200',
    State::Current->value => 'font-medium text-zinc-900 dark:text-zinc-100',
    'separator' => 'mx-2 text-zinc-400 dark:text-zinc-600',
];
