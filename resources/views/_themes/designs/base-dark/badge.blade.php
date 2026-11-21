<?php

use App\DesignSystem\Theme\BadgeVariant;

// Base dark: dark only.
return [
    BadgeVariant::Default->value => 'bg-zinc-800 text-zinc-100',
    BadgeVariant::Success->value => 'bg-green-950 text-green-200',
    BadgeVariant::Info->value => 'bg-blue-950 text-blue-200',
    BadgeVariant::Warning->value => 'bg-yellow-950 text-yellow-200',
    BadgeVariant::Danger->value => 'bg-red-950 text-red-200',
];
