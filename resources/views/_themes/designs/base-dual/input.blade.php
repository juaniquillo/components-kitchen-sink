<?php

use App\DesignSystem\Theme\Look;
use App\DesignSystem\Theme\State;

// Base dual: answering to the theme toggle.
return [
    Look::Default->value => 'border border-zinc-400 bg-white px-3 py-2 w-full dark:border-zinc-600 dark:bg-zinc-900 dark:text-zinc-100',
    State::Disabled->value => 'opacity-50 cursor-not-allowed',
];
