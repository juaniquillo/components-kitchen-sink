<?php

declare(strict_types=1);

namespace App\Components\Groups\Themes;

use App\Components\ContainerOptions;
use App\Components\Contracts\Component;
use Juaniquillo\BackendComponents\Enums\ComponentEnum;
use Juaniquillo\BackendComponents\Factories\ComponentFactory;
use Juaniquillo\BackendComponents\Themes\LocalThemeManager;
use Juaniquillo\BaseBackendComponent\Contracts\BackendComponent;
use Juaniquillo\BaseBackendComponent\Contracts\ThemeManager;
use Juaniquillo\BaseBackendComponent\Themes\GenericThemeManager;

/**
 * The same components rendered against two theme stacks.
 *
 * The austere base stands on its own through GenericThemeManager, while
 * the polished app resolves through LocalThemeManager. Once the package
 * supports layered managers, a third box will render the stacked result.
 */
class ThemeLayers implements Component
{
    const NAME = 'Base vs Polished';

    public static function list(): array
    {
        return [
            'Austere base' => self::base(),
            'Polished app' => self::polished(),
        ];
    }

    public static function options(): ContainerOptions
    {
        return new ContainerOptions;
    }

    public static function base(): BackendComponent
    {
        return self::sample(new GenericThemeManager(base_path('resources/views/_themes/base')), 'success', 'info');
    }

    public static function polished(): BackendComponent
    {
        // The polished app pairs every variant with its dark mode twin,
        // which the austere base deliberately leaves out.
        return self::sample(new LocalThemeManager, ['success', 'success-dark'], ['info', 'info-dark']);
    }

    /**
     * @param  string|array<int, string>  $success
     * @param  string|array<int, string>  $info
     */
    private static function sample(ThemeManager $manager, string|array $success, string|array $info): BackendComponent
    {
        return ComponentFactory::make(ComponentEnum::DIV)
            ->setThemeManager($manager)
            ->setContents([
                ComponentFactory::make(ComponentEnum::PARAGRAPH)
                    ->setContent('A kitchen sink of components built with backend-component trees.')
                    ->setTheme('color', $success)
                    ->setThemeManager($manager),
                ComponentFactory::make(ComponentEnum::PARAGRAPH)
                    ->setContent('The same tree, a different theme stack.')
                    ->setTheme('color', $info)
                    ->setThemeManager($manager),
            ]);
    }
}
