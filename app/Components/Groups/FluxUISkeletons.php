<?php

declare(strict_types=1);

namespace App\Components\Groups;

use App\Components\Builders\FluxLocalThemeComponentBuilder;
use App\Components\ContainerOptions;
use App\Components\Contracts\Component;
use App\Components\ThirdParty\Flux\FluxComponentEnum;
use Juaniquillo\BackendComponents\Builders\ComponentBuilder;
use Juaniquillo\BackendComponents\Contracts\BackendComponent;
use Juaniquillo\BackendComponents\Enums\ComponentEnum;

class FluxUISkeletons implements Component
{
    const NAME = 'Flux UI Skeletons';

    public static function list(): array
    {
        return [
            self::container([
                self::basic(),
            ]),
            self::container([
                self::pulse(),
            ]),
            self::container([
                self::shimmer(),
            ]),
            self::container([
                self::textLines(),
            ]),
            self::container([
                self::cardSkeleton(),
            ]),
            // self::container([
            //     self::avatarWithText(),
            // ]),
        ];
    }

    public static function options(): ContainerOptions
    {
        return new ContainerOptions(
            disableFlex: true,
        );
    }

    /** @param array<int|string, BackendComponent> $contents */
    public static function container(array $contents): BackendComponent
    {
        return ComponentBuilder::make(ComponentEnum::DIV)
            ->setTheme('padding', 'xs')
            ->setContents($contents);
    }

    public static function basic(): BackendComponent
    {
        return FluxLocalThemeComponentBuilder::make(FluxComponentEnum::SKELETON)
            ->setTheme('skeleton', ['w-48', 'h-4']);
    }

    public static function pulse(): BackendComponent
    {
        return FluxLocalThemeComponentBuilder::make(FluxComponentEnum::SKELETON)
            ->setAttribute('animate', 'pulse')
            ->setTheme('skeleton', ['w-48', 'h-4']);
    }

    public static function shimmer(): BackendComponent
    {
        return FluxLocalThemeComponentBuilder::make(FluxComponentEnum::SKELETON)
            ->setAttribute('animate', 'shimmer')
            ->setTheme('skeleton', ['w-48', 'h-4']);
    }

    public static function textLines(): BackendComponent
    {
        return FluxLocalThemeComponentBuilder::make(FluxComponentEnum::SKELETON_GROUP)
            ->setContent(
                FluxLocalThemeComponentBuilder::make(FluxComponentEnum::SKELETON_LINE)
                    ->setAttribute('animate', 'pulse')
                    ->setAttribute('size', 'lg')
            )
            ->setContent(
                FluxLocalThemeComponentBuilder::make(FluxComponentEnum::SKELETON_LINE)
                    ->setAttribute('animate', 'pulse')
            )
            ->setContent(
                FluxLocalThemeComponentBuilder::make(FluxComponentEnum::SKELETON_LINE)
                    ->setAttribute('animate', 'pulse')
            )
            ->setContent(
                FluxLocalThemeComponentBuilder::make(FluxComponentEnum::SKELETON_LINE)
                    ->setAttribute('animate', 'pulse')
                    ->setTheme('skeleton', 'w-2/3')
            );
    }

    public static function cardSkeleton(): BackendComponent
    {
        return FluxLocalThemeComponentBuilder::make(FluxComponentEnum::CARD)
            ->setContent(
                FluxLocalThemeComponentBuilder::make(FluxComponentEnum::SKELETON_GROUP)
                    ->setContent(
                        FluxLocalThemeComponentBuilder::make(FluxComponentEnum::SKELETON)
                            ->setAttribute('animate', 'pulse')
                            ->setTheme('skeleton', ['w-full', 'h-40', 'rounded-lg'])
                    )
                    ->setContent(
                        FluxLocalThemeComponentBuilder::make(FluxComponentEnum::SKELETON)
                            ->setAttribute('animate', 'pulse')
                            ->setTheme('skeleton', ['mt-4', 'w-3/4', 'h-4'])
                    )
                    ->setContent(
                        FluxLocalThemeComponentBuilder::make(FluxComponentEnum::SKELETON)
                            ->setAttribute('animate', 'pulse')
                            ->setTheme('skeleton', ['mt-2', 'w-1/2', 'h-4'])
                    )
            );
    }

    public static function avatarWithText(): BackendComponent
    {
        return FluxLocalThemeComponentBuilder::make(FluxComponentEnum::SKELETON_GROUP)
            ->setContent(
                FluxLocalThemeComponentBuilder::make(FluxComponentEnum::SKELETON)
                    ->setAttribute('animate', 'pulse')
                    ->setTheme('skeleton', ['w-10', 'h-10', 'rounded-full'])
            )
            ->setContent(
                FluxLocalThemeComponentBuilder::make(FluxComponentEnum::SKELETON)
                    ->setAttribute('animate', 'pulse')
                    ->setTheme('skeleton', ['w-32', 'h-4'])
            )
            ->setContent(
                FluxLocalThemeComponentBuilder::make(FluxComponentEnum::SKELETON)
                    ->setAttribute('animate', 'pulse')
                    ->setTheme('skeleton', ['w-48', 'h-3'])
            );
    }
}
