<?php

use Juaniquillo\BackendDesignSystem\Theme\Look;
use Juaniquillo\BackendDesignSystem\Theme\State;

// Classic dual: navy inline links with a current state, paired per mode.
return [
    Look::Default->value => 'text-blue-700 underline underline-offset-2 hover:text-blue-900 dark:text-blue-300 dark:hover:text-blue-200',
    State::Current->value => 'font-semibold text-slate-900 no-underline dark:text-slate-100',
];
