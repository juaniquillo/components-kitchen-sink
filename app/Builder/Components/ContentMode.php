<?php

declare(strict_types=1);

namespace App\Builder\Components;

/**
 * What kind of content a component accepts.
 */
enum ContentMode: string
{
    /**
     * The component holds no content at all (void elements).
     */
    case NONE = 'none';

    /**
     * The component holds a single block of text.
     */
    case TEXT = 'text';

    /**
     * The component holds child components.
     */
    case CHILDREN = 'children';

    /**
     * The component holds text and child components.
     */
    case TEXT_AND_CHILDREN = 'text_and_children';
}
