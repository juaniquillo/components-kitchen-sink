<?php

use App\DesignSystem\Theme\ButtonVariant;
use App\DesignSystem\Theme\Size;
use App\DesignSystem\Theme\State;

// Base design: light only.
return [
    ButtonVariant::Default->value => 'bg-white text-black border border-zinc-400 hover:bg-zinc-100',
    ButtonVariant::Primary->value => 'bg-zinc-900 text-white hover:bg-zinc-700',
    ButtonVariant::Secondary->value => 'bg-zinc-200 text-black hover:bg-zinc-300',
    ButtonVariant::Danger->value => 'bg-red-700 text-white hover:bg-red-800',
    ButtonVariant::Ghost->value => 'text-black hover:bg-zinc-100',
    Size::Sm->value => 'px-2 py-1 text-xs',
    Size::Md->value => 'px-3 py-2 text-sm',
    Size::Lg->value => 'px-4 py-2 text-base',
    State::Disabled->value => 'opacity-50 cursor-not-allowed',
];
