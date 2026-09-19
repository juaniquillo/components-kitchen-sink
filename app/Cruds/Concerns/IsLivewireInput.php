<?php

declare(strict_types=1);

namespace App\Cruds\Concerns;

use Juaniquillo\InputComponentAction\Bags\DefaultAttributeBag;
use Juaniquillo\InputComponentAction\Contracts\AttributeBag;
use Juaniquillo\InputComponentAction\Contracts\LabelAttributes;
use Juaniquillo\InputComponentAction\Recipes\InputComponentRecipe;

trait IsLivewireInput
{
    public static function manageLivewireRecipe(string $group, array|string $name): InputComponentRecipe
    {
        return new InputComponentRecipe(
            attributeBag: self::getLivewireAttributeBag($group, $name),
        );
    }

    public static function getLivewireAttributeBag(string $group, array|string $name, AttributeBag|LabelAttributes|null $bag = null): AttributeBag
    {
        $directiveAndId = self::getLivewireDirectiveAndId($group, $name);

        $bag = $bag ?? new DefaultAttributeBag;

        return $bag->setInputAttributes([
            ...$directiveAndId,
            ...self::getLivewireInputName($name, $group),
        ])
            ->setLabelAttributes([
                'for' => $directiveAndId['id'],
            ]);
    }

    public static function getLivewireAllAttributes(string $group, array|string $name): array
    {
        return [
            ...self::getLivewireInputName($name, $group),
            ...self::getLivewireDirectiveAndId($group, $name),
        ];
    }

    public static function getLivewireDirective(string $group, array|string $name): array
    {
        return [
            'wire:model' => self::getDotNotationName($group, $name),
        ];
    }

    public static function getLivewireId(string $group, array|string $name): array
    {
        return [
            'id' => self::getDotNotationName($group, $name),
        ];
    }

    public static function getLivewireDirectiveAndId(string $group, array|string $name): array
    {
        $dotNotationName = self::getDotNotationName($group, $name);

        return [
            'wire:model' => $dotNotationName,
            'id' => $dotNotationName,
        ];

    }

    public static function getLivewireInputName(string|array $name, ?string $group = null): array
    {
        $newName = '';

        if ($group && is_array($name)) {
            $names = '';
            foreach ($name as $groupItem) {
                $names .= '['.$groupItem.']';
            }

            $newName = $group.$names;
        }

        if ($group) {
            $newName = $group.'['.$name.']';
        }

        return [
            'name' => $newName ?? $name,
        ];
    }

    public static function getDotNotationName(string $group, array|string $name): string
    {
        if (is_array($name)) {
            $name = implode('.', $name);
        }

        return $group.'.'.$name;
    }
}
