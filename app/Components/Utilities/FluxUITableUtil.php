<?php

declare(strict_types=1);

namespace App\Components\Utilities;

use BackedEnum;
use Juaniquillo\BackendComponents\Contracts\BackendComponent;
use Juaniquillo\BackendComponents\Contracts\ThemeManager;
use Juaniquillo\BackendComponents\Themes\LocalThemeManager;
use Juaniquillo\BackendComponents\Utils\CellBag;
use Juaniquillo\FluxBackendComponents\FluxBackendComponent;
use Juaniquillo\FluxBackendComponents\FluxComponentEnum;

use function Juaniquillo\BackendComponents\isCellBag;
use function Juaniquillo\BackendComponents\isComponent;

final class FluxUITableUtil
{
    /**
     * @var array<string, string|array<string|int, string>>
     */
    private array $tableThemes = [];

    /**
     * @var array<string, string|array<string|int, string>>
     */
    private array $thThemes = [
        'table' => [],
    ];

    /**
     * @var array<string, string|array<string|int, string>>
     */
    private array $trThemes = [
        'table' => [],
    ];

    /**
     * @var array<string, string|array<string|int, string>>
     */
    private array $tdThemes = [
        'table' => [],
    ];

    /**
     * @param  array<string|int, string|BackendComponent|CellBag|array{
     *   content: string|int|BackendComponent,
     *   theme?: array<string, string|array<string|int, string>>,
     *   attributes?: array<string, int|string|null>
     * }>  $head
     * @param  array<string|int, array<string|int, string|BackendComponent|CellBag|array{
     *   content: string|int|BackendComponent,
     *   theme?: array<string, string|array<string|int, string>>,
     *   attributes?: array<string, string|int|null>
     * }>>  $body
     */
    public function __construct(
        private array $head,
        private array $body,
        private ThemeManager $themeManager = new LocalThemeManager,
    ) {}

    /**
     * @param  array<string|int, string|BackendComponent|CellBag|array{
     *   content: string|int|BackendComponent,
     *   theme?: array<string, string|array<string|int, string>>,
     *   attributes?: array<string, string|int|null>
     * }>  $head
     * @param  array<string|int, array<string|int, string|BackendComponent|CellBag|array{
     *   content: string|int|BackendComponent,
     *   theme?: array<string, string|array<string|int, string>>,
     *   attributes?: array<string, string|int|null>
     * }>>  $body
     */
    public static function make(array $head, array $body): static
    {
        return new self($head, $body);
    }

    /**
     * @param  array<string, string|array<string|int, string>>  $themes
     */
    public function setTableThemes(array $themes): static
    {
        $this->tableThemes = $themes;

        return $this;
    }

    /**
     * @param  array<string, string|array<string|int, string>>  $themes
     */
    public function setThThemes(array $themes): static
    {
        $this->thThemes = $themes;

        return $this;
    }

    /**
     * @param  array<string, string|array<string|int, string>>  $themes
     */
    public function setTrThemes(array $themes): static
    {
        $this->trThemes = $themes;

        return $this;
    }

    /**
     * @param  array<string, string|array<string|int, string>>  $themes
     */
    public function setTdThemes(array $themes): static
    {
        $this->tdThemes = $themes;

        return $this;
    }

    public function getComponent(): BackendComponent
    {
        $contents = [];

        if (count($this->head)) {
            $contents[] = $this->head();
        }

        $contents[] = $this->body();

        return $this->composeComponent(FluxComponentEnum::TABLE, $contents, $this->tableThemes);
    }

    private function head(): FluxBackendComponent
    {
        $columns = [];

        foreach ($this->head as $value) {
            $columns[] = $this->composeComponent(
                name: FluxComponentEnum::TH,
                contents: $this->resolveContent($value),
                theme: $this->resolveTheme($this->thThemes, $value),
                attributes: $this->resolveAttributes($value),
            );
        }

        return $this->composeComponent(FluxComponentEnum::THEAD, [
            $this->composeComponent(FluxComponentEnum::TR, $columns),
        ]);
    }

    private function body(): FluxBackendComponent
    {
        $rows = [];

        foreach ($this->body as $row) {
            $rows[] = $this->composeComponent(
                FluxComponentEnum::TR,
                $this->rows($row),
                $this->trThemes,
            );
        }

        return $this->composeComponent(FluxComponentEnum::TBODY, $rows);
    }

    /**
     * @param  array<string|int, string|BackendComponent|CellBag|array{
     *   content: string|int|BackendComponent,
     *   theme?: array<string, string|array<string|int, string>>,
     *   attributes?: array<string, string|int|null>
     * }>  $rows
     * @return array<int, FluxBackendComponent>
     */
    private function rows(array $rows): array
    {
        $cells = [];

        foreach ($rows as $value) {
            $cells[] = $this->composeComponent(
                FluxComponentEnum::TD,
                contents: $this->resolveContent($value),
                theme: $this->resolveTheme($this->tdThemes, $value),
                attributes: $this->resolveAttributes($value),
            );
        }

        return $cells;
    }

    /**
     * @param string|BackendComponent|CellBag|array{
     *   content: string|int|BackendComponent,
     *   theme?: array<string, string|array<string|int, string>>,
     *   attributes?: array<string, int|string|null>
     * } $content
     */
    private function resolveContent(array|string|CellBag|BackendComponent $content): string|int|BackendComponent
    {
        if (isCellBag($content)) {
            return $content->content;
        }

        if (is_array($content)) {
            return $content['content'];
        }

        if (isComponent($content)) {
            return $content;
        }

        return $content;
    }

    /**
     * @param string|BackendComponent|CellBag|array{
     *   content: string|int|BackendComponent,
     *   theme?: array<string, string|array<string|int, string>>,
     *   attributes?: array<string, string|int|null>
     * } $content
     * @param  array<string, string|array<string|int, string>>  $theme
     * @return array<string, string|array<string|int, string>>
     */
    private function resolveTheme(array $theme, array|string|CellBag|BackendComponent $content): array
    {
        if (isCellBag($content) && $content->theme) {
            return $content->theme;
        }

        if (is_array($content) && isset($content['theme'])) {
            return $content['theme'];
        }

        return $theme;
    }

    /**
     * @param string|BackendComponent|CellBag|array{
     *   content: string|int|BackendComponent,
     *   theme?: array<string, string|array<string|int, string>>,
     *   attributes?: array<string, string|int|null>
     * } $content
     * @return array<string, int|string|null>
     */
    private function resolveAttributes(array|string|CellBag|BackendComponent $content): array
    {
        if (isCellBag($content) && $content->attributes) {
            return $content->attributes;
        }

        if (is_array($content) && isset($content['attributes'])) {
            return $content['attributes'];
        }

        return [];
    }

    /**
     * @param  int|string|BackendComponent|array<int|string, int|string|BackendComponent>  $contents
     * @param  array<string, string|array<string|int, string>>|null  $theme
     * @param  array<string, int|string|null>|null  $attributes
     */
    private function composeComponent(BackedEnum $name, int|array|string|BackendComponent $contents, ?array $theme = null, ?array $attributes = null): FluxBackendComponent
    {
        $contents = is_array($contents) ? $contents : [$contents];

        $component = (new FluxBackendComponent($name, $this->themeManager))
            ->setContents($contents);

        if ($theme) {
            $component->setThemes($theme);
        }

        if ($attributes) {
            $component->setAttributes($attributes);
        }

        return $component;
    }
}
