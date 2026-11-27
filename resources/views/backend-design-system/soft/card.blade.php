<?php

// Soft dual: a quiet card that lifts off the page, paired for light and dark.
// The container is a full-height column and the foot carries mt-auto,
// so cards sharing a grid row line their footers up with the tallest.
return [
    'container' => 'flex h-full flex-col overflow-hidden rounded-lg border border-zinc-200 bg-white shadow-sm dark:border-zinc-800 dark:bg-zinc-900',
    'title' => 'border-b border-zinc-100 px-4 py-2 font-semibold text-zinc-900 dark:border-zinc-800 dark:text-zinc-100',
    'body' => 'px-4 py-3 text-sm text-zinc-600 dark:text-zinc-400',
    'foot' => 'mt-auto border-t border-zinc-100 px-4 py-2 dark:border-zinc-800',
];
