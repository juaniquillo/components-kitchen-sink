@php
    use App\Builder\Support\RuntimeTailwind;

    // The canvas is styled by the browser build of Tailwind, which compiles
    // the classes the user writes as they go. It is a script in the head of
    // the page, so the choice is kept in the session and the page is loaded
    // again when it changes.
    $runtimeTailwind = (bool) session('builder.tailwind_runtime', true);

    $assets = [
        'js' => ['https://cdn.tailwindcss.com']
    ];

    $tailwindDeclarations = $runtimeTailwind ? RuntimeTailwind::declarations() : '';
@endphp

<x-layouts.default page-title="Builder" :remoteAssets="$assets" :disableLocalAssets="true">
    <x-slot name="head">
        @if ($tailwindDeclarations !== '')
            <style type="text/tailwindcss">{!! $tailwindDeclarations !!}</style>
        @endif
        <style>
            /*
            | Builder canvas
            | Every node the builder renders carries a data-node-id, which lets the
            | nodes be outlined on hover and highlighted while selected.
            */

            [data-node-id] {
                outline: 1px dashed transparent;
                outline-offset: 2px;
                transition: outline-color 120ms ease;
            }

            [data-node-id]:hover {
                outline-color: rgb(148 163 184 / 0.9);
            }

            [data-selected='true'] {
                outline: 2px solid rgb(79 70 229);
                outline-offset: 2px;
            }

        </style>
    </x-slot>

    <div class="mb-4 text-center">
        <h1 class="text-3xl font-bold">Component Builder</h1>

        <p class="mt-1 text-sm text-slate-600 dark:text-slate-400">
            Compose a component by dragging it together, then copy the generated PHP.
        </p>
    </div>

    <livewire:builder.builder-canvas />

</x-layouts.default>
