@use('App\Livewire\Builder\NoticeSurface')

@php
    $canvasNotice = $this->noticeOn(NoticeSurface::CANVAS) ?? $this->noticeOn(NoticeSurface::TABLE);
@endphp

<section
    id="builder-canvas-panel"
    tabindex="-1"
    aria-labelledby="builder-canvas-heading"
    class="rounded-lg bg-white p-3 shadow-sm focus:outline-none dark:bg-slate-800"
    x-data="{ previewOpen: false }"
    x-bind:inert="previewOpen"
    x-on:builder:morphed.window="if (previewOpen && ! $refs.preview.open) $refs.preview.showModal()"
>
    @if ($canvasNotice !== null)
        <p class="mb-2 rounded border border-red-200 bg-red-50 px-2 py-1 text-[11px] text-red-600 dark:border-red-900 dark:bg-red-500/10 dark:text-red-300" role="alert">
            {{ $canvasNotice }}
        </p>
    @endif

    <div class="mb-2 flex items-center justify-between gap-2">
        <h2 id="builder-canvas-heading" class="text-xs font-semibold tracking-wide text-slate-500 uppercase dark:text-slate-400">Canvas</h2>

        <div class="flex items-center gap-2">
            <p class="hidden text-[11px] text-slate-500 sm:block dark:text-slate-400">
                Drag the handle to move a node. Click to select.
            </p>

            <x-bw-button wire:click="undo" :disabled="! $this->canUndo" title="Undo (Ctrl+Z)" aria-label="Undo">↺</x-bw-button>

            <x-bw-button wire:click="redo" :disabled="! $this->canRedo" title="Redo (Ctrl+Shift+Z)" aria-label="Redo">↻</x-bw-button>

            <x-bw-button wire:click="configureSelected" :disabled="$selectedId === null">
                Configure selected
            </x-bw-button>

            <x-bw-button wire:click="openThemes">Themes</x-bw-button>

            <x-bw-button x-on:click="previewOpen = true; $refs.preview.showModal(); $wire.call('openPreview')">
                Preview
            </x-bw-button>
        </div>
    </div>

    <div
        class="relative min-h-[420px] rounded border border-dashed border-slate-300 bg-slate-50 p-3 dark:border-slate-600 dark:bg-slate-900/40"
    >
        @if ($this->canvasEmpty())
            <p class="pointer-events-none absolute inset-0 flex items-center justify-center p-6 text-center text-sm text-slate-500 dark:text-slate-400">
                The canvas is empty. Add a component from the palette on the left.
            </p>
        @endif

        {!! $this->preview !!}
    </div>

    {{--
        The save confirmation is a native popover: the browser owns the top
        layer, the show and hide states, and the close button below, and the
        only script is the timer that dismisses it again. The workspace is
        written on its own after every change, so the toast is the answer to
        a save from the keyboard rather than the only time anything is saved.

        The colours and the spacing are classes on the element; what stays
        here is what a class cannot say: the popover's own hide-and-show
        states, the discrete transition between them, and the anchor.
    --}}
    <style>
        #builder-canvas-panel {
            anchor-name: --bw-canvas;
        }

        #builder-saved-toast {
            position: fixed;
            inset: auto 1rem 1rem auto;
            margin: 0;
            opacity: 0;
            transform: translateY(0.5rem);
            transition:
                opacity 0.2s ease,
                transform 0.2s ease,
                display 0.2s allow-discrete;
        }

        #builder-saved-toast:popover-open {
            display: flex;
            opacity: 1;
            transform: none;
        }

        @starting-style {
            #builder-saved-toast:popover-open {
                opacity: 0;
                transform: translateY(0.5rem);
            }
        }

        @supports (top: anchor(--bw-canvas top)) {
            #builder-saved-toast {
                inset: auto;
                top: calc(anchor(--bw-canvas top) + 3rem);
                right: calc(anchor(--bw-canvas right) + 0.75rem);
            }
        }

        @media (prefers-reduced-motion: reduce) {
            #builder-saved-toast {
                transition: none;
                transform: none;
            }
        }
    </style>

    <output
        id="builder-saved-toast"
        popover="manual"
        class="items-center gap-2 rounded-lg border border-indigo-200 bg-indigo-50 px-3 py-2 text-xs text-indigo-700 shadow-lg dark:border-indigo-500/40 dark:bg-indigo-500/15 dark:text-indigo-200"
        x-data="{ timer: null }"
        x-on:builder-saved.window="clearTimeout(timer); if (! $el.matches(':popover-open')) $el.showPopover(); timer = setTimeout(() => { if ($el.matches(':popover-open')) $el.hidePopover(); }, 2000)"
    >Saved ✓<button
            type="button"
            popovertarget="builder-saved-toast"
            popovertargetaction="hide"
            aria-label="Dismiss"
            class="rounded px-1 leading-none hover:bg-indigo-200 dark:hover:bg-indigo-500/30 focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-indigo-500"
        >×</button></output>

    {{--
        The dialog is a plain one: the page does not load the stylesheet of
        the application, so the components of the interface are not available
        here. A native dialog brings the top layer, the focus trap and the
        escape key with it.

        The browser owns the open state, and a livewire update takes the open
        attribute away from the dialog, so it is opened again when the page
        says the dom was written. The cancel event is the one of the escape
        key, so the state is not lost when the attribute is taken away.
    --}}
    <dialog
        x-ref="preview"
        aria-labelledby="builder-preview-title"
        x-on:cancel="previewOpen = false; $wire.call('closePreview')"
        x-on:click="if ($event.target === $el) { previewOpen = false; $refs.preview.close(); $wire.call('closePreview'); }"
        class="m-auto w-[calc(100%_-_2rem)] max-w-2xl rounded-lg bg-white p-0 shadow-xl dark:bg-slate-800"
    >
        @if ($this->previewRequested)
            <div class="flex max-h-[80vh] flex-col">
                <div class="flex items-center justify-between border-b border-slate-200 p-4 dark:border-slate-700">
                    <h3 id="builder-preview-title" class="text-sm font-semibold text-slate-700 dark:text-slate-200">Preview</h3>

                    <x-bw-button x-on:click="previewOpen = false; $refs.preview.close(); $wire.call('closePreview')">
                        Close
                    </x-bw-button>
                </div>

                <div class="overflow-auto p-6">
                    {!! $this->cleanPreview !!}
                </div>
            </div>
        @endif
    </dialog>
</section>
