<?php

use Juaniquillo\BackendDesignSystem\Theme\Look;
use Juaniquillo\BackendDesignSystem\Theme\State;

// Soft dual: recessed fields that lift on focus, paired for light and dark.
return [
    Look::Default->value => 'w-full border border-zinc-300 bg-zinc-50 px-3 py-2 text-zinc-900 focus:border-indigo-500 focus:bg-white focus:outline-none focus:ring-2 focus:ring-indigo-200 dark:border-zinc-700 dark:bg-zinc-950 dark:text-zinc-100 dark:focus:border-indigo-400 dark:focus:bg-zinc-900 dark:focus:ring-indigo-900',
    State::Disabled->value => 'opacity-50 cursor-not-allowed',
];
