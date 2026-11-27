<x-layouts.canvas
    page-title="Dashboard · Design System"
    :replace-assets="['resources/css/design-system.css', 'resources/js/app.ts']"
    :body-class="$bodyClass"
>

    <div class="px-4 py-6" x-data="{}">
        <div class="flex max-w-6xl flex-wrap items-center justify-between gap-3">
            <div class="flex flex-wrap items-center gap-3">
                {{ \Juaniquillo\BackendDesignSystem\Components\Link::make($design)->label('← Gallery')->href(route('design-system', ['design' => $design->key])) }}

                <span aria-hidden="true" class="h-5 w-px bg-current opacity-20"></span>

                <span class="text-xs font-semibold uppercase tracking-wide opacity-70">Design</span>
                {{ \App\Menu\DesignSwitcher::make($designs, $design->key, 'design-system.dashboard', center: false) }}
            </div>

            <div class="flex flex-wrap items-center gap-3">
                <span class="text-xs font-semibold uppercase tracking-wide opacity-70">Theme</span>
                {{ \App\Menu\AppearanceToggle::make() }}
            </div>
        </div>

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

                <div class="mt-sm">{{ $revenue }}</div>

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
