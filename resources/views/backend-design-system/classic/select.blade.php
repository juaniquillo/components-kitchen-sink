<?php

use Juaniquillo\BackendDesignSystem\Theme\Look;
use Juaniquillo\BackendDesignSystem\Theme\State;

// Classic dual: white controls with a blue focus ring, paired per mode.
return [
    Look::Default->value => 'w-full border border-slate-300 bg-white px-3 py-2 text-slate-900 focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-200 dark:border-slate-600 dark:bg-slate-900 dark:text-slate-100 dark:focus:border-blue-400 dark:focus:ring-blue-900',
    State::Disabled->value => 'opacity-50 cursor-not-allowed',
];
