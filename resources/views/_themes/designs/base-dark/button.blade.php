<?php

use App\DesignSystem\Theme\ButtonVariant;
use App\DesignSystem\Theme\Size;
use App\DesignSystem\Theme\State;

// Base dark: dark only.
return [
    ButtonVariant::Default->value => 'bg-zinc-900 text-zinc-100 border border-zinc-700 hover:bg-zinc-800',
    ButtonVariant::Primary->value => 'bg-zinc-100 text-black hover:bg-white',
    ButtonVariant::Secondary->value => 'bg-zinc-800 text-zinc-100 hover:bg-zinc-700',
    ButtonVariant::Danger->value => 'bg-red-900 text-white hover:bg-red-800',
    ButtonVariant::Ghost->value => 'text-zinc-100 hover:bg-zinc-800',
    Size::Sm->value => 'px-2 py-1 text-xs',
    Size::Md->value => 'px-3 py-2 text-sm',
    Size::Lg->value => 'px-4 py-2 text-base',
    State::Disabled->value => 'opacity-50 cursor-not-allowed',
];
