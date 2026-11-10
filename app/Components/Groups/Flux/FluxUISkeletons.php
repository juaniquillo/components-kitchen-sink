<?php

declare(strict_types=1);

namespace App\Components\Groups\Flux;

use App\Components\ContainerOptions;
use App\Components\Contracts\Component;
use Juaniquillo\BackendComponents\Contracts\BackendComponent;
use Juaniquillo\BackendComponents\Enums\ComponentEnum;
use Juaniquillo\BackendComponents\Factories\ComponentFactory;
use Juaniquillo\FluxBackendComponents\Factories\FluxLocalThemeComponentFactory;
use Juaniquillo\FluxBackendComponents\FluxComponentEnum;

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
        return ComponentFactory::make(ComponentEnum::DIV)
            ->setTheme('padding', 'xs')
            ->setContents($contents);
    }

    public static function basic(): BackendComponent
    {
        return FluxLocalThemeComponentFactory::make(FluxComponentEnum::SKELETON)
            ->setTheme('skeleton', ['w-48', 'h-4']);
    }

    public static function pulse(): BackendComponent
    {
        return FluxLocalThemeComponentFactory::make(FluxComponentEnum::SKELETON)
            ->setAttribute('animate', 'pulse')
            ->setTheme('skeleton', ['w-48', 'h-4']);
    }

    public static function shimmer(): BackendComponent
    {
        return FluxLocalThemeComponentFactory::make(FluxComponentEnum::SKELETON)
            ->setAttribute('animate', 'shimmer')
            ->setTheme('skeleton', ['w-48', 'h-4']);
    }

    public static function textLines(): BackendComponent
    {
        return FluxLocalThemeComponentFactory::make(FluxComponentEnum::SKELETON_GROUP)
            ->setContent(
                FluxLocalThemeComponentFactory::make(FluxComponentEnum::SKELETON_LINE)
                    ->setAttribute('animate', 'pulse')
                    ->setAttribute('size', 'lg')
            )
            ->setContent(
                FluxLocalThemeComponentFactory::make(FluxComponentEnum::SKELETON_LINE)
                    ->setAttribute('animate', 'pulse')
            )
            ->setContent(
                FluxLocalThemeComponentFactory::make(FluxComponentEnum::SKELETON_LINE)
                    ->setAttribute('animate', 'pulse')
            )
            ->setContent(
                FluxLocalThemeComponentFactory::make(FluxComponentEnum::SKELETON_LINE)
                    ->setAttribute('animate', 'pulse')
                    ->setTheme('skeleton', 'w-2/3')
            );
    }

    public static function cardSkeleton(): BackendComponent
    {
        return FluxLocalThemeComponentFactory::make(FluxComponentEnum::CARD)
            ->setContent(
                FluxLocalThemeComponentFactory::make(FluxComponentEnum::SKELETON_GROUP)
                    ->setContent(
                        FluxLocalThemeComponentFactory::make(FluxComponentEnum::SKELETON)
                            ->setAttribute('animate', 'pulse')
                            ->setTheme('skeleton', ['w-full', 'h-40', 'rounded-lg'])
                    )
                    ->setContent(
                        FluxLocalThemeComponentFactory::make(FluxComponentEnum::SKELETON)
                            ->setAttribute('animate', 'pulse')
                            ->setTheme('skeleton', ['mt-4', 'w-3/4', 'h-4'])
                    )
                    ->setContent(
                        FluxLocalThemeComponentFactory::make(FluxComponentEnum::SKELETON)
                            ->setAttribute('animate', 'pulse')
                            ->setTheme('skeleton', ['mt-2', 'w-1/2', 'h-4'])
                    )
            );
    }

    public static function avatarWithText(): BackendComponent
    {
        return FluxLocalThemeComponentFactory::make(FluxComponentEnum::SKELETON_GROUP)
            ->setContent(
                FluxLocalThemeComponentFactory::make(FluxComponentEnum::SKELETON)
                    ->setAttribute('animate', 'pulse')
                    ->setTheme('skeleton', ['w-10', 'h-10', 'rounded-full'])
            )
            ->setContent(
                FluxLocalThemeComponentFactory::make(FluxComponentEnum::SKELETON)
                    ->setAttribute('animate', 'pulse')
                    ->setTheme('skeleton', ['w-32', 'h-4'])
            )
            ->setContent(
                FluxLocalThemeComponentFactory::make(FluxComponentEnum::SKELETON)
                    ->setAttribute('animate', 'pulse')
                    ->setTheme('skeleton', ['w-48', 'h-3'])
            );
    }
}
