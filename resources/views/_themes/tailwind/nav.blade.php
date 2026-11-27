<?php

// Site chrome: plain class names resolved by shared.css, not Tailwind
// utilities. The chrome loads on pages whose bundle replaces app.css
// (Bootstrap, Slate), where layered utilities lose to unlayered page
// rules. Links know their state when they render (current); the theme
// toggle flips aria-pressed client-side after the page loads.
return [
    'group' => 'nav-row',
    'group-center' => 'nav-row nav-row--center',
    'item' => 'nav-item',
    'current' => 'nav-item',
    'item-sm' => 'nav-item nav-item--sm',
    'current-sm' => 'nav-item nav-item--sm',
    'toggle-group' => 'theme-switch',
    'toggle-button' => 'theme-switch__button',
    'icon-label' => 'nav-icon-label',
    'icon-sun' => 'nav-icon nav-icon--sun',
    'icon-moon' => 'nav-icon nav-icon--moon',
    'icon-auto' => 'nav-icon nav-icon--auto',
    'icon-close' => 'nav-icon nav-icon--close',
    'icon-overview' => 'nav-icon nav-icon--overview',
    'icon-invoices' => 'nav-icon nav-icon--invoices',
    'icon-clients' => 'nav-icon nav-icon--clients',
    'icon-settings' => 'nav-icon nav-icon--settings',
];
