<?php

declare(strict_types=1);

namespace App\Livewire\Builder;

/**
 * Where a refusal belongs.
 *
 * The drawer shows one panel at a time and the canvas has a banner of its
 * own, so a message is tagged with the place that asked for it and only that
 * place renders it. A notice that has no surface left on the page is cleared
 * by whoever takes the surface away.
 */
enum NoticeSurface: string
{
    case CANVAS = 'canvas';
    case NODE = 'node';
    case TABLE = 'table';
    case THEME = 'theme';
    case ATTRIBUTE = 'attribute';
}
