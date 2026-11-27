<x-layouts.canvas
    page-title="Dashboard · Design System"
    :replace-assets="['resources/css/design-system.css', 'resources/js/app.ts']"
    :body-class="$bodyClass"
>

    <div class="mx-auto max-w-6xl px-4 py-6">
        <div class="text-center">
            <h1 class="mt-sm text-4xl font-bold">Dashboard</h1>

            <div class="mt-sm">
                {{ \App\Menu\DesignSwitcher::make($designs, $design->key, 'design-system.dashboard') }}
            </div>

            <p class="mt-sm text-sm">
                <a
                    href="{{ route('design-system', ['design' => $design->key]) }}"
                    class="underline underline-offset-2"
                >← Back to the gallery</a>
            </p>
        </div>

        <div class="mt-sm">{{ $breadcrumb }}</div>

        <div class="mt-sm flex flex-wrap items-end justify-between gap-3">
            <div>
                {{ $title }}
                {{ $subtitle }}
            </div>
            {{ $actions }}
        </div>

        <div class="mt-sm">{{ $notice }}</div>

        <div class="mt-sm grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
            @foreach ($kpis as $kpi)
                {{ $kpi }}
            @endforeach
        </div>

        <div class="mt-sm">{{ $revenue }}</div>

        <div class="mt-sm grid gap-4 lg:grid-cols-3">
            <div class="lg:col-span-2">{{ $invoices }}</div>
            <div>{{ $invite }}</div>
        </div>
    </div>

</x-layouts.canvas>
