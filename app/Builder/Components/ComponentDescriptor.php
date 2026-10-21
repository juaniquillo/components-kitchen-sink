<?php

declare(strict_types=1);

namespace App\Builder\Components;

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
