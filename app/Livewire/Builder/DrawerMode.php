<?php

declare(strict_types=1);

namespace App\Livewire\Builder;

/**
 * What the builder drawer is showing.
 *
 * The drawer shows one thing at a time, and this is the one place that says
 * which. A closed drawer is null, so "open with nothing in it" is not a state
 * the component can hold, and a mode that works on a node keeps that node in
 * `$drawerTarget` instead of in a property of its own.
 */
enum DrawerMode: string
{
    case Node = 'node';
    case Table = 'table';
    case Themes = 'themes';
    case Saved = 'saved';
}
