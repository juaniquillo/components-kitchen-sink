<?php

declare(strict_types=1);

namespace App\Builder\Support;

/**
 * The tag a node chrome uses.
 *
 * A chrome has to live inside the node it belongs to, so its tag depends
 * on the node's content model: a div where anything goes, a span where
 * only phrasing fits, and the table tags where only they fit. The set is
 * closed — every other tag would be hoisted out by the browser's parser.
 */
enum ChromeTag: string
{
    case DIV = 'div';
    case SPAN = 'span';
    case LI = 'li';
    case TD = 'td';
    case TR = 'tr';
    case CAPTION = 'caption';
}
