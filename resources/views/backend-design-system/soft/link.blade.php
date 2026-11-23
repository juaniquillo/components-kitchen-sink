<?php

use Juaniquillo\BackendDesignSystem\Theme\Look;
use Juaniquillo\BackendDesignSystem\Theme\State;

// Soft dual: indigo inline links with a current state, paired for both modes.
return [
    Look::Default->value => 'text-indigo-700 underline underline-offset-2 hover:text-indigo-900 dark:text-indigo-300 dark:hover:text-indigo-200',
    State::Current->value => 'font-semibold text-zinc-900 no-underline dark:text-zinc-100',
];
