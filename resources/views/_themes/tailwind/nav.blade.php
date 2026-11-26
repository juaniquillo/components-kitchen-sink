<?php

// Site chrome: one pill in two sizes. Links know their state when they
// render (current), so only the theme toggle needs an attribute variant:
// its aria-pressed flips client-side after the page loads.
return [
    'group' => 'flex flex-wrap gap-2',
    'group-center' => 'flex flex-wrap justify-center gap-2',
    'item' => 'inline-flex items-center whitespace-nowrap rounded-full border border-zinc-300 px-3.5 py-1.5 text-sm font-medium text-zinc-600 no-underline hover:border-zinc-400 hover:text-zinc-900 dark:border-zinc-600 dark:text-zinc-400 dark:hover:border-zinc-500 dark:hover:text-zinc-100',
    'current' => 'inline-flex items-center whitespace-nowrap rounded-full border border-zinc-900 bg-zinc-900 px-3.5 py-1.5 text-sm font-medium text-white no-underline dark:border-zinc-100 dark:bg-zinc-100 dark:text-black',
    'item-sm' => 'inline-flex items-center whitespace-nowrap rounded-full border border-zinc-300 px-3 py-1 text-xs font-medium text-zinc-600 no-underline hover:border-zinc-400 hover:text-zinc-900 dark:border-zinc-600 dark:text-zinc-400 dark:hover:border-zinc-500 dark:hover:text-zinc-100',
    'current-sm' => 'inline-flex items-center whitespace-nowrap rounded-full border border-zinc-900 bg-zinc-900 px-3 py-1 text-xs font-medium text-white no-underline dark:border-zinc-100 dark:bg-zinc-100 dark:text-black',
    'toggle' => 'inline-flex items-center whitespace-nowrap rounded-full border border-zinc-300 bg-transparent px-3 py-1 text-xs font-medium text-zinc-600 hover:border-zinc-400 hover:text-zinc-900 aria-pressed:border-zinc-900 aria-pressed:bg-zinc-900 aria-pressed:text-white dark:border-zinc-600 dark:text-zinc-400 dark:hover:border-zinc-500 dark:hover:text-zinc-100 dark:aria-pressed:border-zinc-100 dark:aria-pressed:bg-zinc-100 dark:aria-pressed:text-black',
];
