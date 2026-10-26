<p class="hidden text-[11px] text-slate-400 sm:block">
    A theme is a set of Tailwind classes you can use on any component.
</p>

<div class="flex flex-wrap items-end gap-2">
    <label class="flex-1">
        <span class="sr-only">Group</span>
        <input
            type="text"
            wire:model="themeGroup"
            placeholder="group (action)"
            class="w-full rounded border border-slate-200 px-2 py-1 text-xs dark:border-slate-600 dark:bg-slate-900"
        />
    </label>

    <label class="flex-1">
        <span class="sr-only">Variant</span>
        <input
            type="text"
            wire:model="themeVariant"
            placeholder="variant (primary)"
            class="w-full rounded border border-slate-200 px-2 py-1 text-xs dark:border-slate-600 dark:bg-slate-900"
        />
    </label>

    <label class="flex-[2]">
        <span class="sr-only">Classes</span>
        <input
            type="text"
            wire:model="themeClasses"
            placeholder="classes (bg-indigo-600 text-white px-4 py-2)"
            class="w-full rounded border border-slate-200 px-2 py-1 text-xs dark:border-slate-600 dark:bg-slate-900"
        />
    </label>

    <button
        type="button"
        wire:click="saveTheme"
        class="shrink-0 rounded border border-slate-200 px-2 py-1 text-xs text-slate-600 hover:bg-slate-50 dark:border-slate-600 dark:text-slate-300"
    >
        Save
    </button>
</div>

@if ($themeNotice !== null)
    <p class="mt-2 text-[11px] text-red-500" role="alert">{{ $themeNotice }}</p>
@endif

@if ($themes !== [])
    <div class="mt-3 space-y-3">
        @foreach ($themes as $group => $variants)
            <div>
                <h3 class="mb-1 text-[11px] font-medium text-slate-500">
                    {{ $group }}
                    <span class="font-normal text-slate-400">
                        → resources/views/_themes/tailwind/{{ $group }}.blade.php
                    </span>
                </h3>

                <ul class="divide-y divide-slate-100 dark:divide-slate-700">
                    @foreach ($variants as $variant => $classes)
                        <li class="flex items-center gap-2 py-1">
                            <span class="w-24 shrink-0 truncate text-[11px] font-medium text-slate-700 dark:text-slate-200">
                                {{ $variant }}
                            </span>

                            <input
                                type="text"
                                value="{{ $classes }}"
                                wire:change="updateThemeClasses('{{ $group }}', '{{ $variant }}', $event.target.value)"
                                class="min-w-0 flex-1 rounded border border-slate-200 px-2 py-1 text-[11px] dark:border-slate-600 dark:bg-slate-900"
                            />

                            <span class="shrink-0 text-[10px] text-slate-400">
                                used by {{ $this->themeUsage($group, $variant) }}
                            </span>

                            <button
                                type="button"
                                wire:click="deleteTheme('{{ $group }}', '{{ $variant }}')"
                                @disabled($this->themeUsage($group, $variant) > 0)
                                class="shrink-0 rounded border border-red-200 px-1.5 py-0.5 text-[10px] text-red-600 hover:bg-red-50 disabled:opacity-40 dark:border-red-900"
                            >
                                Delete
                            </button>
                        </li>
                    @endforeach
                </ul>
            </div>
        @endforeach
    </div>
@else
    <p class="mt-2 text-[11px] text-slate-400">
        No themes yet. Add one above and it can be used on any component.
    </p>
@endif
