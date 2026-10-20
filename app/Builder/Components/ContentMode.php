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
    case None = 'none';

    /**
     * The component holds a single block of text.
     */
    case Text = 'text';

    /**
     * The component holds child components.
     */
    case Children = 'children';

    /**
     * The component holds text and child components.
     */
    case TextAndChildren = 'text_and_children';
}
