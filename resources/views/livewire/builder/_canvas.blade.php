<section
    id="builder-canvas-panel"
    class="rounded-lg bg-white p-3 shadow-sm dark:bg-slate-800"
    x-data="{ previewOpen: false }"
    x-bind:inert="previewOpen"
    x-on:builder:morphed.window="if (previewOpen && ! $refs.preview.open) $refs.preview.showModal()"
>
    @if ($tableNotice !== null)
        <p class="mb-2 rounded border border-red-200 bg-red-50 px-2 py-1 text-[11px] text-red-600 dark:border-red-900 dark:bg-red-500/10 dark:text-red-300" role="alert">
            {{ $tableNotice }}
        </p>
    @endif

    <div class="mb-2 flex items-center justify-between gap-2">
        <h2 class="text-xs font-semibold tracking-wide text-slate-500 uppercase">Canvas</h2>

        <div class="flex items-center gap-2">
            <p class="hidden text-[11px] text-slate-400 sm:block">
                Drag the handle to move a node. Click to select.
            </p>

            <button
                type="button"
                wire:click="undo"
                @disabled($this->undoStack === [])
                title="Undo (Ctrl+Z)"
                aria-label="Undo"
                class="rounded border border-slate-200 px-2 py-1 text-xs text-slate-600 hover:bg-slate-50 disabled:opacity-40 dark:border-slate-600 dark:text-slate-300 dark:hover:bg-slate-600"
            >
                ↺
            </button>

            <button
                type="button"
                wire:click="redo"
                @disabled($this->redoStack === [])
                title="Redo (Ctrl+Shift+Z)"
                aria-label="Redo"
                class="rounded border border-slate-200 px-2 py-1 text-xs text-slate-600 hover:bg-slate-50 disabled:opacity-40 dark:border-slate-600 dark:text-slate-300 dark:hover:bg-slate-600"
            >
                ↻
            </button>

            <button
                type="button"
                wire:click="configureSelected"
                @disabled($selectedId === null)
                class="rounded border border-slate-200 px-2 py-1 text-xs text-slate-600 hover:bg-slate-50 disabled:opacity-40 dark:border-slate-600 dark:text-slate-300 dark:hover:bg-slate-600"
            >
                Configure selected
            </button>

            <button
                type="button"
                wire:click="openThemes"
                class="rounded border border-slate-200 px-2 py-1 text-xs text-slate-600 hover:bg-slate-50 dark:border-slate-600 dark:text-slate-300 dark:hover:bg-slate-600"
            >
                Themes
            </button>

            <button
                type="button"
                wire:click="openSaved"
                class="rounded border border-slate-200 px-2 py-1 text-xs text-slate-600 hover:bg-slate-50 dark:border-slate-600 dark:text-slate-300 dark:hover:bg-slate-600"
            >
                Saved
            </button>

            <button
                type="button"
                x-on:click="previewOpen = true; $refs.preview.showModal(); $wire.call('openPreview')"
                class="rounded border border-slate-200 px-2 py-1 text-xs text-slate-600 hover:bg-slate-50 dark:border-slate-600 dark:text-slate-300 dark:hover:bg-slate-600"
            >
                Preview
            </button>
        </div>
    </div>

    <div
        class="relative min-h-[420px] rounded border border-dashed border-slate-300 bg-slate-50 p-3 dark:border-slate-600 dark:bg-slate-900/40"
    >
        @if ($this->canvasEmpty())
            <p class="pointer-events-none absolute inset-0 flex items-center justify-center p-6 text-center text-sm text-slate-400">
                The canvas is empty. Add a component from the palette on the left.
            </p>
        @endif

        {!! $this->preview !!}
    </div>

    {{--
        The save confirmation is a native popover: the browser owns the top
        layer, the show and hide states, and the close button below, and the
        only script is the timer that dismisses it again.
    --}}
    <style>
        #builder-canvas-panel {
            anchor-name: --bw-canvas;
        }

        #builder-saved-toast {
            position: fixed;
            inset: auto 1rem 1rem auto;
            margin: 0;
            align-items: center;
            gap: 0.5rem;
            border-radius: 0.5rem;
            border: 1px solid #c7d2fe;
            background: #eef2ff;
            color: #4338ca;
            padding: 0.5rem 0.75rem;
            font-size: 0.75rem;
            box-shadow: 0 4px 12px rgb(15 23 42 / 0.15);
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

        #builder-saved-toast button {
            border-radius: 0.25rem;
            padding: 0 0.25rem;
            line-height: 1;
            color: inherit;
        }

        #builder-saved-toast button:hover {
            background: #c7d2fe;
        }
    </style>

    <output
        id="builder-saved-toast"
        popover="manual"
        x-data="{ timer: null }"
        x-on:builder-saved.window="clearTimeout(timer); if (! $el.matches(':popover-open')) $el.showPopover(); timer = setTimeout(() => { if ($el.matches(':popover-open')) $el.hidePopover(); }, 2000)"
    >Saved ✓<button type="button" popovertarget="builder-saved-toast" popovertargetaction="hide" aria-label="Dismiss">×</button></output>

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
        x-on:cancel="previewOpen = false; $wire.call('closePreview')"
        x-on:click="if ($event.target === $el) { previewOpen = false; $refs.preview.close(); $wire.call('closePreview'); }"
        class="m-auto w-[calc(100%_-_2rem)] max-w-2xl rounded-lg bg-white p-0 shadow-xl dark:bg-slate-800"
    >
        @if ($this->previewRequested)
            <div class="flex max-h-[80vh] flex-col">
                <div class="flex items-center justify-between border-b border-slate-200 p-4 dark:border-slate-700">
                    <h3 class="text-sm font-semibold text-slate-700 dark:text-slate-200">Preview</h3>

                    <button
                        type="button"
                        x-on:click="previewOpen = false; $refs.preview.close(); $wire.call('closePreview')"
                        class="rounded border border-slate-200 px-2 py-1 text-xs text-slate-600 hover:bg-slate-50 dark:border-slate-600 dark:text-slate-300 dark:hover:bg-slate-600"
                    >
                        Close
                    </button>
                </div>

                <div class="overflow-auto p-6">
                    {!! $this->cleanPreview !!}
                </div>
            </div>
        @endif
    </dialog>
</section>
