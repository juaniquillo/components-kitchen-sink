<x-layouts.canvas
    page-title="Acme Studio · Design System"
    :replace-assets="['resources/css/design-system.css', 'resources/js/app.ts']"
    :body-class="$bodyClass"
>

    <x-example-header :design="$design" :designs="$designs" route="design-system.landing" />

    <div class="mx-auto max-w-6xl px-4 py-12">
        <div class="mx-auto max-w-3xl">{{ $hero }}</div>

        <div class="mx-auto mt-16 max-w-3xl">{{ $preview }}</div>

        <div class="mx-auto mt-16 max-w-3xl">{{ $proof }}</div>

        <div class="mt-16 grid gap-4 md:grid-cols-3">
            @foreach ($features as $feature)
                {{ $feature }}
            @endforeach
        </div>

        <div class="mx-auto mt-16 max-w-4xl">{{ $pricing }}</div>

        <div class="mx-auto mt-16 max-w-3xl">{{ $faq }}</div>

        <div class="mx-auto mt-16 max-w-xl">
            @if (session('landing-notice'))
                <p class="mb-4 rounded border border-emerald-200 bg-emerald-50 px-3 py-2 text-sm text-emerald-700 dark:border-emerald-900 dark:bg-emerald-500/10 dark:text-emerald-300" role="status">
                    {{ session('landing-notice') }}
                </p>
            @endif

            {{ $newsletter }}
        </div>

        <div class="mx-auto mt-16 max-w-3xl">{{ $footer }}</div>
    </div>

</x-layouts.canvas>
