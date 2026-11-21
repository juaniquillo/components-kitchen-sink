<?php

declare(strict_types=1);

namespace Tests\Support;

use Juaniquillo\BaseBackendComponent\Concerns\IsThemeManager;
use Juaniquillo\BaseBackendComponent\Contracts\ThemeManager;

/**
 * The shape a packaged design ships: a manager that knows its own folder.
 */
final class AcmeThemeManager implements ThemeManager
{
    use IsThemeManager;

    public function __construct()
    {
        $this->setDefaultPath(resource_path('views/_themes/designs/base'));
        $this->setThemeExtension('.blade.php');
    }
}
