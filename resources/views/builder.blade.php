@php
    use App\Builder\Support\RuntimeTailwind;

    // The canvas is styled by the browser build of Tailwind, which compiles
    // the classes the user writes as they go. The custom theme and the dark
    // variant live in the stylesheet of the application, which the browser
    // build does not read, so they are taken from it and handed over.
    $assets = [
        'js' => ['https://cdn.tailwindcss.com']
    ];

    $tailwindDeclarations = RuntimeTailwind::declarations();
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

            /*
            | The chrome of a node
            | The chrome is always shown. A chrome that appears and
            | disappears changes the layout under the pointer, and near an
            | edge that changes which node is hovered, so hovering
            | alternates between two nodes while the parts of the canvas
            | push each other around. Always shown, the layout stays put,
            | and the hover target stays with it. Where a node's own
            | display would make the chrome a part of its row or grid — a
            | flex, a grid, columns — the renderer anchors the chrome to
            | the node's corner instead, so it never joins the flow it
            | decorates.
            */

            .bw-chrome {
                display: var(--bw-chrome-display, flex);
            }

            /*
            | The menu of things to add
            | The menu is a native popover: the browser takes it to the top
            | layer, so no toolbar of another node can paint over it, and
            | gives it the light dismisses, the escape key and one open menu
            | at a time. Out of the flow a popover has no containing block
            | to hang from, so anchor positioning ties it back to the
            | button that opened it.
            */

            [popover].bw-menu {
                position: fixed;
                inset: auto;
                margin: 0;
                top: calc(anchor(bottom) + 4px);
                right: anchor(right);
                position-try-fallbacks: flip-block, flip-inline;
            }

            /*
            | Preview dialog
            | The page is styled by the browser build of Tailwind, which does
            | not know about the backdrop of a dialog.
            */

            dialog::backdrop {
                background-color: rgb(15 23 42 / 0.5);
            }

        </style>

        <script>
            /*
            | The preview dialog is opened by the browser and not by the
            | server, so a livewire update takes the open attribute away from
            | it. The event tells the builder to open it again.
            */
            document.addEventListener('livewire:init', () => {
                Livewire.hook('morphed', () => {
                    window.dispatchEvent(new CustomEvent('builder:morphed'));
                });
            });
        </script>

        <script>
            /*
            | Warn before the page goes away with a composition that is not
            | saved. The builder says on its root element whether there is
            | work that would be lost, so there is no state to keep here.
            */
            window.addEventListener('beforeunload', (event) => {
                const canvas = document.querySelector('[data-builder-canvas]');

                if (! canvas || canvas.dataset.dirty !== 'true') {
                    return;
                }

                event.preventDefault();
                event.returnValue = '';
            });
        </script>
    </x-slot>

    <div class="mb-4 text-center">
        <h1 class="text-3xl font-bold">Component Builder</h1>

        <p class="mt-1 text-sm text-slate-600 dark:text-slate-400">
            Compose a component by dragging it together, then copy the generated PHP.
        </p>
    </div>

    <div class="mb-4">
        <livewire:builder.builder-canvas />
    </div>

</x-layouts.default>
