@php
    use App\Builder\Support\RuntimeTailwind;

    // The canvas is styled by the browser build of Tailwind, which compiles
    // the classes the user writes as they go. It is a script in the head of
    // the page, so the choice is kept in the session and the page is loaded
    // again when it changes.
    $runtimeTailwind = (bool) session('builder.tailwind_runtime', true);

    $tailwindAssets = $runtimeTailwind
        ? ['https://cdn.jsdelivr.net/npm/@tailwindcss/browser@4']
        : [];

    $tailwindDeclarations = $runtimeTailwind ? RuntimeTailwind::declarations() : '';
@endphp

<x-layouts.default page-title="Builder" :additionalAssets="$tailwindAssets">
    <x-slot name="head">
        @if ($tailwindDeclarations !== '')
            <style type="text/tailwindcss">{!! $tailwindDeclarations !!}</style>
        @endif
    </x-slot>

    <div class="mb-4 text-center">
        <h1 class="text-3xl font-bold">Component Builder</h1>

        <p class="mt-1 text-sm text-slate-500">
            Compose a component by dragging it together, then copy the generated PHP.
        </p>
    </div>

    <livewire:builder.builder-canvas />

</x-layouts.default>
