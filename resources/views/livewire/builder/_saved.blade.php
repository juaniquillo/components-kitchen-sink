<section class="rounded-lg bg-white p-3 shadow-sm dark:bg-slate-800">
    <h2 class="mb-2 text-xs font-semibold tracking-wide text-slate-500 uppercase">Saved compositions</h2>

    <div class="flex flex-wrap items-end gap-2">
        <label class="flex-1">
            <span class="mb-1 block text-[11px] font-medium text-slate-500">Name</span>
            <input
                type="text"
                wire:model.live.debounce.300ms="compositionName"
                placeholder="Hero section"
                class="w-full rounded border border-slate-200 px-2 py-1 text-xs dark:border-slate-600 dark:bg-slate-900"
            />
        </label>

        <button
            type="button"
            wire:click="save"
            class="rounded bg-indigo-600 px-3 py-1.5 text-xs text-white transition hover:bg-indigo-500"
        >
            Save
        </button>
    </div>

    @if ($this->saved !== [])
        <ul class="mt-3 divide-y divide-slate-100 dark:divide-slate-700">
            @foreach ($this->saved as $composition)
                <li class="flex items-center justify-between gap-2 py-2">
                    <div class="min-w-0">
                        <p class="truncate text-xs font-medium text-slate-700 dark:text-slate-200">
                            {{ $composition->name }}
                        </p>
                        <p class="text-[11px] text-slate-400">
                            {{ $composition->savedAt }} &middot; {{ $composition->bytes }} bytes
                        </p>
                    </div>

                    <div class="flex shrink-0 gap-1">
                        <button type="button" wire:click="load('{{ $composition->slug }}')"
                            class="rounded border border-slate-200 px-2 py-1 text-xs hover:bg-slate-50 dark:border-slate-600">
                            Load
                        </button>
                        <button type="button" wire:click="delete('{{ $composition->slug }}')"
                            class="rounded border border-red-200 px-2 py-1 text-xs text-red-600 hover:bg-red-50 dark:border-red-900">
                            Delete
                        </button>
                    </div>
                </li>
            @endforeach
        </ul>
    @else
        <p class="mt-3 text-xs text-slate-400">
            Nothing saved yet. Compositions are written to
            <code class="text-[11px]">storage/app/private/builder</code>.
        </p>
    @endif
</section>
