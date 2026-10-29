<aside aria-labelledby="builder-palette-heading" class="rounded-lg bg-white p-3 shadow-sm dark:bg-slate-800">
    <h2 id="builder-palette-heading" class="mb-2 text-xs font-semibold tracking-wide text-slate-500 uppercase dark:text-slate-400">Palette</h2>

    <div class="max-h-[65vh] space-y-3 overflow-y-auto pr-1">
        @foreach ($this->palette as $group => $descriptors)
            <div wire:key="palette-group-{{ $group }}">
                <h3 class="mb-1 text-[11px] font-semibold text-slate-500 dark:text-slate-400">{{ $group }}</h3>

                <div class="flex flex-wrap gap-1">
                    @foreach ($descriptors as $descriptor)
                        <button
                            type="button"
                            wire:key="palette-{{ $descriptor->type }}"
                            wire:click="add('{{ $descriptor->type }}')"
                            class="rounded border border-slate-200 px-2 py-1 text-xs text-slate-700 transition hover:border-indigo-400 hover:bg-indigo-50 hover:text-indigo-700 focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-indigo-500 dark:border-slate-600 dark:text-slate-200 dark:hover:bg-indigo-500/10"
                            title="{{ $descriptor->label }}"
                        >
                            {{ $descriptor->label }}
                        </button>
                    @endforeach
                </div>
            </div>
        @endforeach
    </div>
</aside>
