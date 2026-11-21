<?php

use App\DesignSystem\Theme\BadgeVariant;

// Base dual: answering to the theme toggle.
return [
    BadgeVariant::Default->value => 'bg-zinc-200 text-black dark:bg-zinc-800 dark:text-zinc-100',
    BadgeVariant::Success->value => 'bg-green-100 text-green-900 dark:bg-green-950 dark:text-green-200',
    BadgeVariant::Info->value => 'bg-blue-100 text-blue-900 dark:bg-blue-950 dark:text-blue-200',
    BadgeVariant::Warning->value => 'bg-yellow-100 text-yellow-900 dark:bg-yellow-950 dark:text-yellow-200',
    BadgeVariant::Danger->value => 'bg-red-100 text-red-900 dark:bg-red-950 dark:text-red-200',
];
