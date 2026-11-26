<?php

declare(strict_types=1);

namespace App\DesignSystem;

use Illuminate\Http\Request;
use Juaniquillo\BackendDesignSystem\Designs\Design;

/**
 * Shared design resolution for the gallery and the dashboard preview.
 *
 * The package binds one design, resolved from the configured default, so
 * switching means setting the default before the binding is first
 * resolved. Both routes go through here so they cannot drift apart.
 */
final class DesignSwitch
{
    /**
     * @return array{design: Design, designs: array<int, array{key: string, label: string}>, key: string}
     */
    public static function resolve(Request $request, string $fallback): array
    {
        $designs = collect(config('backend-design-system.designs', []))
            ->map(fn (array $definition, string $key): array => [
                'key' => $key,
                'label' => (string) ($definition['label'] ?? $key),
            ])
            ->values()
            ->all();

        $key = (string) $request->query('design', $fallback);

        if (! collect($designs)->contains('key', $key)) {
            abort(404);
        }

        config(['backend-design-system.default' => $key]);
        app()->forgetInstance(Design::class);

        return [
            'design' => app(Design::class),
            'designs' => $designs,
            'key' => $key,
        ];
    }
}
