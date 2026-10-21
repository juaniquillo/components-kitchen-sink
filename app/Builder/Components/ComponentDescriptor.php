<?php

declare(strict_types=1);

namespace App\Builder\Components;

use App\Builder\Contracts\BuilderNode;

/**
 * Describes a component offered by the builder palette: how it is labelled,
 * what it may contain and which attributes it tends to carry.
 */
final readonly class ComponentDescriptor
{
    /**
     * @param  string  $type  The ComponentEnum value, e.g. "headers.h1".
     * @param  string  $label  Human readable name shown in the palette.
     * @param  string  $group  Palette group the component is listed under.
     * @param  ContentMode  $contentMode  What kind of content it accepts.
     * @param  array<int, string>  $allowedChildren  Component types it may contain, or ["*"] for any.
     * @param  array<int, string>  $suggestedAttributes  Attribute names the inspector offers as a starting point.
     */
    public function __construct(
        public string $type,
        public string $label,
        public string $group,
        public ContentMode $contentMode,
        public array $allowedChildren,
        public array $suggestedAttributes,
    ) {}

    /**
     * Whether this component may hold the given child type.
     */
    public function allowsChild(string $childType): bool
    {
        return in_array($childType, $this->allowedChildren, true);
    }

    /**
     * Whether this component may hold the given item.
     *
     * An item is either a component or a text segment, and the two are not
     * the same question: a component has to be listed in the allowed
     * children, while a text segment is content, so it only asks whether the
     * component takes text at all. This is the one place the distinction is
     * made, so the tree, the canvas, the drawer and the saved files cannot
     * answer it differently.
     */
    public function allowsChildType(string $childType): bool
    {
        if ($childType === BuilderNode::TYPE_TEXT) {
            return $this->acceptsText();
        }

        return $this->allowsChild($childType);
    }

    /**
     * Whether this component accepts text content.
     */
    public function acceptsText(): bool
    {
        return $this->contentMode === ContentMode::Text
            || $this->contentMode === ContentMode::TextAndChildren;
    }

    /**
     * Whether this component accepts child components.
     */
    public function acceptsChildren(): bool
    {
        return $this->contentMode === ContentMode::Children
            || $this->contentMode === ContentMode::TextAndChildren;
    }

    /**
     * Whether this component holds content of any kind.
     *
     * A void element holds nothing at all, so it can take neither text nor
     * children.
     */
    public function acceptsContent(): bool
    {
        return $this->acceptsText() || $this->acceptsChildren();
    }
}
