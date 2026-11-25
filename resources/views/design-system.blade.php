<x-layouts.default
    page-title="Design System"
    :replace-assets="['resources/css/design-system.css', 'resources/js/app.js']"
>

    <div class="design-system">
        <div class="text-center">
        <h1 class="mt-sm text-4xl font-bold">Design System</h1>

        <div class="mt-sm flex flex-wrap justify-center gap-2">
            @foreach ($designs as $option)
                <a
                    href="{{ route('design-system', ['design' => $option['key']]) }}"
                    class="border px-3 py-1 text-sm {{ $option['key'] === $design->key ? 'border-zinc-900 bg-zinc-900 text-white dark:border-zinc-100 dark:bg-zinc-100 dark:text-black' : 'border-zinc-300 hover:bg-zinc-100 dark:border-zinc-600 dark:hover:bg-zinc-800' }}"
                >{{ $option['label'] }}</a>
            @endforeach
        </div>
    </div>

    <div class="component-container mt-sm">
        @foreach ($boxes as $box)
            <div class="component-box-bg {{ $box['options']->box->getCSS() }}">
                <h2 class="component-box-h2">{{ $box['title'] }}</h2>

                <div class="{{ $box['options']->container->getCSS() }}">
                    @foreach ($box['components'] as $component)
                        {{ $component }}
                    @endforeach
                </div>
            </div>
        @endforeach
    </div>

    </div>

</x-layouts.default>
