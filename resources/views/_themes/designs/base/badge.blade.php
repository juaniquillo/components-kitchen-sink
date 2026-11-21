<?php

use App\DesignSystem\Theme\BadgeVariant;

// Base design: light only.
return [
    BadgeVariant::Default->value => 'bg-zinc-200 text-black',
    BadgeVariant::Success->value => 'bg-green-100 text-green-900',
    BadgeVariant::Info->value => 'bg-blue-100 text-blue-900',
    BadgeVariant::Warning->value => 'bg-yellow-100 text-yellow-900',
    BadgeVariant::Danger->value => 'bg-red-100 text-red-900',
];
