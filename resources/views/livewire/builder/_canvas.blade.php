@php
    use function Juaniquillo\BackendComponents\processThemes;
@endphp
<section class="rounded-lg bg-white p-3 shadow-sm dark:bg-slate-800">
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

            <flux:modal.trigger name="builder-preview">
                <flux:button size="sm" variant="ghost">Preview</flux:button>
            </flux:modal.trigger>
        </div>
    </div>

    <div
        class="min-h-[420px] rounded border border-dashed border-slate-300 bg-slate-50 p-3 dark:border-slate-600 dark:bg-slate-900/40"
    >
        {!! $this->preview !!}
    </div>
</section>

<flux:modal name="builder-preview" class="{{ processThemes(['modal' => '2xl']) }}">
    <flux:heading size="sm">Preview</flux:heading>

    <div class="max-w-xl overflow-auto rounded-lg border border-slate-200 p-6 dark:border-slate-700">
        {!! $this->cleanPreview !!}
    </div>
</flux:modal>
