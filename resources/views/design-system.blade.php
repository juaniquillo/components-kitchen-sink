<x-layouts.default
    page-title="Design System"
    :replace-assets="['resources/css/design-system.css', 'resources/js/app.ts']"
>

    <div class="design-system">
        <div class="text-center">
        <h1 class="mt-sm text-4xl font-bold">Design System</h1>

        <div class="mt-sm">
            {{ \App\Menu\DesignSwitcher::make($designs, $design->key, 'design-system') }}
        </div>

        <p class="mt-sm text-sm">
            <a
                href="{{ route('design-system.dashboard', ['design' => $design->key]) }}"
                class="underline underline-offset-2"
            >Preview the classic dashboard →</a>
        </p>
    </div>

    <div class="component-container mt-sm">
        @foreach ($boxes as $box)
            @php($options = $box->options())
            <div class="component-box-bg {{ $options->box->getCSS() }}">
                <h2 class="component-box-h2">{{ $box::NAME }}</h2>

                <div class="{{ $options->container->getCSS() }}">
                    @foreach ($box->components($design) as $component)
                        {{ $component }}
                    @endforeach
                </div>
            </div>
        @endforeach
    </div>

    <div class="mt-sm">
        <h2 class="component-box-h2">In a real project</h2>

        <p class="mt-sm text-sm">The same components, composed the way a project uses them.</p>

        <div class="component-container mt-sm">
            @foreach ($boxes as $box)
                @foreach ($box->examples() as $example)
                    <div class="{{ $example->canvas ?? \App\DesignSystem\DesignCanvas::example($design->key) }} p-sm">
                        <p class="text-xs font-semibold uppercase tracking-wide">{{ $box::NAME }}</p>
                        <h3 class="mt-sm text-lg font-bold">{{ $example->label }}</h3>
                        <p class="mt-sm text-sm">{{ $example->description }}</p>
                        <div class="mt-sm">{{ $example->component($design) }}</div>
                    </div>
                @endforeach
            @endforeach
        </div>
    </div>

    </div>

</x-layouts.default>
