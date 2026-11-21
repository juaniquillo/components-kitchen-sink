<?php

use App\DesignSystem\Theme\Look;
use App\DesignSystem\Theme\State;

// Base dark: dark only.
return [
    Look::Default->value => 'border border-zinc-600 bg-zinc-900 text-zinc-100 px-3 py-2 w-full min-h-24',
    State::Disabled->value => 'opacity-50 cursor-not-allowed',
];
