@props(['design' => null, 'designs' => [], 'route' => 'design-system'])

{{-- The chrome every example page shares: gallery link, design switcher,
    theme switch. The bar is sticky and translucent; the page content keeps
    its own container, so the chrome spans the full width. --}}
<header class="example-header">
    <div class="example-header__inner">
        <div class="flex flex-wrap items-center gap-3">
            {{ \Juaniquillo\BackendDesignSystem\Components\Link::make($design)->label('← Gallery')->href(route('design-system', ['design' => $design->key])) }}

            <span aria-hidden="true" class="h-5 w-px bg-current opacity-20"></span>

            <span class="text-xs font-semibold uppercase tracking-wide opacity-70">Design</span>
            {{ \App\Menu\DesignSwitcher::make($designs, $design->key, $route, center: false) }}
        </div>

        <div class="flex flex-wrap items-center gap-3">
            <span class="text-xs font-semibold uppercase tracking-wide opacity-70">Theme</span>
            {{ \App\Menu\AppearanceToggle::make() }}
        </div>
    </div>
</header>
