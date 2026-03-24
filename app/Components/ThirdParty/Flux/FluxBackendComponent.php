<?php

namespace  App\Components\ThirdParty\Flux;

use BackedEnum;
use Illuminate\Contracts\Support\Htmlable;
use Illuminate\View\ComponentAttributeBag;
use Juaniquillo\BackendComponents\Concerns\HasPath;
use Juaniquillo\BackendComponents\Concerns\HasContent;
use Juaniquillo\BackendComponents\Contracts\AttributeBag;
use Juaniquillo\BackendComponents\Contracts\ThemeManager;
use Juaniquillo\BackendComponents\Contracts\PathComponent;
use Juaniquillo\BackendComponents\Contracts\BackendComponent;
use Juaniquillo\BackendComponents\Contracts\ContentComponent;
use Juaniquillo\BackendComponents\Themes\DefaultThemeManager;
use Juaniquillo\BackendComponents\Concerns\IsBackendComponent;
use Juaniquillo\BackendComponents\Components\DefaultAttributeBag;

use function Juaniquillo\BackendComponents\isBackedEnum;
use function Juaniquillo\BackendComponents\backendComponentNamespace;

final class FluxBackendComponent implements BackendComponent, ContentComponent, PathComponent, Htmlable
{
    use HasContent,
        HasPath,
        IsBackendComponent;

    public function __construct(
        private string|BackedEnum $name,
        private ThemeManager $themeManager = new DefaultThemeManager
    ) {}

    /**
     * Hardcode context
     */
    public function getContext(): string
    {
        return 'flux::';
    }

    public function getName(): string
    {
        $name = $this->name;

        if (isBackedEnum($name)) {
            return $name->value;
        }

        return $name;
    }

    public function getAttributeBag(): AttributeBag
    {
        return new DefaultAttributeBag(
            attributes: $this->getAttributes(),
            content:$this->processContent(),
            path: $this->getComponentPath(),
        );
    }

    public function toArray(): array
    {
        return [
            'name' => $this->getName(),
            'attributes' => $this->getAttributes(),
            'content' => $this->processContent()->toArray(),
            'path' => $this->getComponentPath(),
        ];
    }

    public function toHtml()
    {
        $path = $this->getComponentPath();
        $attributes = $this->getAttributeBag()->getAttributes();
        $attributeBag = new ComponentAttributeBag($attributes);
        $content = $this->processContent();
        
        return \view(backendComponentNamespace().'_utilities.resolve-third-party-component')
            ->with('path', $path)
            ->with('attributes', $attributeBag)
            ->with('content', $content)
            ->render();
    }
}
