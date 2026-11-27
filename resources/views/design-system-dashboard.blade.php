<x-layouts.canvas
    page-title="Dashboard · Design System"
    :replace-assets="['resources/css/design-system.css', 'resources/js/app.ts']"
    :body-class="$bodyClass"
>

    <x-example-header :design="$design" :designs="$designs" route="design-system.dashboard" />

    <div class="px-4 py-6" x-data="{}">
        <div class="mt-sm flex max-w-6xl items-center justify-between gap-3 lg:hidden">
            {{ $brand }}

            <button
                type="button"
                class="theme-switch__button dashboard-menu-button"
                aria-label="Menu"
                aria-haspopup="dialog"
                aria-controls="dashboard-menu"
                x-on:click="$refs.menuPanel.showModal()"
            >
                <span class="nav-icon nav-icon--menu" aria-hidden="true"></span>
            </button>
        </div>

        <div class="mt-sm grid max-w-6xl gap-6 lg:grid-cols-[12rem_minmax(0,1fr)]">
            <aside class="hidden lg:block">{{ $sidebar }}</aside>

            <div class="min-w-0">
                <div class="mt-sm">{{ $breadcrumb }}</div>

                <div class="mt-sm flex flex-wrap items-end justify-between gap-3">
                    <div>
                        {{ $title }}
                        {{ $subtitle }}
                    </div>
                    {{ $actions }}
                </div>

                <div class="mt-sm">{{ $notice }}</div>

                @if (session('dashboard-notice'))
                    <p class="mt-sm rounded border border-emerald-200 bg-emerald-50 px-3 py-2 text-sm text-emerald-700 dark:border-emerald-900 dark:bg-emerald-500/10 dark:text-emerald-300" role="status">
                        {{ session('dashboard-notice') }}
                    </p>
                @endif

                <div class="mt-sm grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
                    @foreach ($kpis as $kpi)
                        {{ $kpi }}
                    @endforeach
                </div>

                <div class="mt-sm grid gap-4 lg:grid-cols-3">
                    <div class="min-w-0 lg:col-span-2">{{ $revenue }}</div>
                    <div class="min-w-0">{{ $channels }}</div>
                </div>

                <div class="mt-sm grid gap-4 lg:grid-cols-3">
                    <div class="min-w-0 lg:col-span-2">{{ $invoices }}</div>
                    <div class="min-w-0">{{ $invite }}</div>
                </div>
            </div>
        </div>

        {{ $sheet }}
    </div>

    {{ $dialog }}

</x-layouts.canvas>
