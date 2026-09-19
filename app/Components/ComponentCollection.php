<?php

declare(strict_types=1);

namespace App\Components;

use App\Components\Contracts\Component;

class ComponentCollection
{
    /** @var array<int, Component> */
    private array $collection = [];

    public static function make(): static
    {
        return new static;
    }

    public function addComponent(Component $component): static
    {
        $this->collection[] = $component;

        return $this;
    }

    /** @var array<int, Component> */
    public function addComponents(array $components): static
    {
        foreach ($components as $component) {
            $this->addComponent($component);
        }

        return $this;
    }

    /** @return array<int, Component> */
    public function list(): array
    {
        return $this->collection;
    }
}

// class ComponentCollection
// {
//     public static function list() : array
//     {
//         return [
//             MainPackage::NAME => [
//                 'group' => MainPackage::make(),
//             ],
//             LivewireFlux::NAME => [
//                 'group' => LivewireFlux::make(),
//             ],
//             BootstrapButton::NAME => [
//                 'group' => BootstrapButton::make(),
//             ],
//         ];
//     }

// }
