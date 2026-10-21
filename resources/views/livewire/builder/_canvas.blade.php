<section
    class="rounded-lg bg-white p-3 shadow-sm dark:bg-slate-800"
    x-data="{ previewOpen: false }"
    x-on:builder:morphed.window="if (previewOpen && ! $refs.preview.open) $refs.preview.showModal()"
>
    <div class="mb-2 flex items-center justify-between gap-2">
        <h2 class="text-xs font-semibold tracking-wide text-slate-500 uppercase">Canvas</h2>

        <div class="flex items-center gap-2">
            <p class="hidden text-[11px] text-slate-400 sm:block">
                Drag the handle to move a node. Click to select.
            </p>

            <button
                type="button"
                wire:click="toggleTailwind"
                class="rounded border border-slate-200 px-2 py-1 text-xs text-slate-600 hover:bg-slate-50 dark:border-slate-600 dark:text-slate-300 dark:hover:bg-slate-600"
            >
                {{ $this->tailwindRuntime ? 'Runtime Tailwind: on' : 'Runtime Tailwind: off' }}
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
                x-on:click="previewOpen = true; $refs.preview.showModal()"
                class="rounded border border-slate-200 px-2 py-1 text-xs text-slate-600 hover:bg-slate-50 dark:border-slate-600 dark:text-slate-300 dark:hover:bg-slate-600"
            >
                Preview
            </button>
        </div>
    </div>

    <div
        class="min-h-[420px] rounded border border-dashed border-slate-300 bg-slate-50 p-3 dark:border-slate-600 dark:bg-slate-900/40"
    >
        {!! $this->preview !!}
    </div>

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
        x-on:cancel="previewOpen = false"
        x-on:click="if ($event.target === $el) { previewOpen = false; $refs.preview.close(); }"
        class="m-auto w-[calc(100%_-_2rem)] max-w-2xl rounded-lg bg-white p-0 shadow-xl dark:bg-slate-800"
    >
        <div class="flex max-h-[80vh] flex-col">
            <div class="flex items-center justify-between border-b border-slate-200 p-4 dark:border-slate-700">
                <h3 class="text-sm font-semibold text-slate-700 dark:text-slate-200">Preview</h3>

                <button
                    type="button"
                    x-on:click="previewOpen = false; $refs.preview.close()"
                    class="rounded border border-slate-200 px-2 py-1 text-xs text-slate-600 hover:bg-slate-50 dark:border-slate-600 dark:text-slate-300 dark:hover:bg-slate-600"
                >
                    Close
                </button>
            </div>

            <div class="overflow-auto p-6">
                {!! $this->cleanPreview !!}
            </div>
        </div>
    </dialog>
</section>
