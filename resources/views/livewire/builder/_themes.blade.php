@use('App\Builder\Support\ThemeArchive')
@use('App\Livewire\Builder\NoticeSurface')

@php
    $themeMessage = $this->noticeOn(NoticeSurface::THEME);
    $groupCount = count($themes);
@endphp

{{--
    The groups are collapsed to begin with: a workspace with a full palette
    is a long list, and the header of a group already says everything that
    is needed to find it. The state is kept in Alpine, so opening a group
    only costs a click and survives the updates that saving a theme makes.

    The path of the file is the toggle. It is the thing that says where the
    group goes, and it is also the shortest way to put the whole group away.
--}}
<div
    x-data="{ expanded: {} }"
    x-on:theme-collapse-all.window="expanded = {}"
>
    <p class="hidden text-[11px] text-slate-500 sm:block dark:text-slate-400">
        A theme is a set of Tailwind classes you can use on any component.
    </p>

    <div class="mt-3 space-y-2">
        <label class="block">
            <span class="mb-1 block text-[11px] font-medium text-slate-500 dark:text-slate-400">Group</span>

            <input
                type="text"
                wire:model="themeGroup"
                placeholder="action"
                class="w-full rounded border border-slate-200 px-2 py-1 text-xs dark:border-slate-600 dark:bg-slate-900"
            />
        </label>

        <label class="block">
            <span class="mb-1 block text-[11px] font-medium text-slate-500 dark:text-slate-400">Variant</span>

            <input
                type="text"
                wire:model="themeVariant"
                placeholder="primary"
                class="w-full rounded border border-slate-200 px-2 py-1 text-xs dark:border-slate-600 dark:bg-slate-900"
            />
        </label>

        <label class="block">
            <span class="mb-1 block text-[11px] font-medium text-slate-500 dark:text-slate-400">Classes</span>

            <textarea
                rows="2"
                wire:model="themeClasses"
                placeholder="bg-indigo-600 text-white px-4 py-2"
                class="w-full rounded border border-slate-200 px-2 py-1 font-mono text-[11px] dark:border-slate-600 dark:bg-slate-900"
            ></textarea>
        </label>

        <x-bw-button variant="primary" size="md" wire:click="saveTheme" class="w-full">Save</x-bw-button>
    </div>

    @if ($themeMessage !== null)
        <p class="mt-2 text-[11px] text-red-600 dark:text-red-400" role="alert">{{ $themeMessage }}</p>
    @endif

    @if ($themes !== [])
        <div class="mt-5 flex items-center justify-between gap-2">
            <h3 class="text-[11px] font-semibold text-slate-500 dark:text-slate-400">
                Theme files ({{ $groupCount }})
            </h3>

            <div class="flex shrink-0 gap-1">
                <x-bw-button size="xs" x-on:click="$dispatch('theme-collapse-all')">Collapse all</x-bw-button>
                <x-bw-button size="xs" x-on:click="$dispatch('theme-expand-all')">Expand all</x-bw-button>
            </div>
        </div>

        <a
            href="{{ route('builder.export', ['workspace' => $workspace]) }}"
            download
            class="mt-2 block rounded border border-indigo-600 px-2 py-1 text-center text-xs font-medium text-indigo-700 transition hover:bg-indigo-50 focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-indigo-500 dark:border-indigo-500 dark:text-indigo-300 dark:hover:bg-indigo-500/10"
        >
            Download all themes (.zip)
        </a>

        <div class="mt-3">
            @foreach ($themes as $group => $variants)
                <div
                    wire:key="theme-panel-group-{{ $group }}"
                    class="border-b border-slate-100 py-1 last:border-b-0 dark:border-slate-700"
                    x-on:theme-expand-all.window="expanded['{{ $group }}'] = true"
                    {{-- A variant that was just saved would land in a group nobody can see. --}}
                    x-on:builder-theme-saved.window="if ($event.detail.group === '{{ $group }}') expanded['{{ $group }}'] = true"
                >
                    <h3>
                        <button
                            type="button"
                            x-on:click="expanded['{{ $group }}'] = ! expanded['{{ $group }}']"
                            x-bind:aria-expanded="expanded['{{ $group }}'] ? 'true' : 'false'"
                            aria-controls="theme-group-{{ $group }}"
                            class="flex w-full items-start gap-1 rounded px-1 py-1 text-left transition hover:bg-slate-50 focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-indigo-500 dark:hover:bg-slate-800"
                        >
                            <span
                                aria-hidden="true"
                                x-bind:style="expanded['{{ $group }}'] ? 'transform: rotate(90deg)' : ''"
                                class="mt-0.5 shrink-0 text-[9px] leading-4 text-slate-500 transition-transform dark:text-slate-400"
                            >▶</span>

                            <span class="min-w-0">
                                <span class="block text-[11px] font-medium text-slate-700 dark:text-slate-200">
                                    {{ $group }}
                                </span>

                                <span
                                    class="block truncate font-mono text-[10px] text-slate-500 dark:text-slate-400"
                                    title="{{ ThemeArchive::DIRECTORY }}/{{ $group }}.blade.php"
                                >
                                    → {{ ThemeArchive::DIRECTORY }}/{{ $group }}.blade.php
                                </span>
                            </span>
                        </button>
                    </h3>

                    <ul
                        id="theme-group-{{ $group }}"
                        x-show="expanded['{{ $group }}']"
                        x-cloak
                        class="divide-y divide-slate-100 dark:divide-slate-700"
                    >
                        @foreach ($variants as $variant => $classes)
                            <li wire:key="theme-panel-{{ $group }}-{{ $variant }}" class="flex items-center gap-2 py-1">
                                <span class="w-20 shrink-0 truncate text-[11px] font-medium text-slate-700 dark:text-slate-200">
                                    {{ $variant }}
                                </span>

                                <input
                                    type="text"
                                    value="{{ $classes }}"
                                    aria-label="Classes of {{ $group }} / {{ $variant }}"
                                    wire:change="updateThemeClasses('{{ $group }}', '{{ $variant }}', $event.target.value)"
                                    class="min-w-0 flex-1 rounded border border-slate-200 px-2 py-1 text-[11px] dark:border-slate-600 dark:bg-slate-900"
                                />

                                <span class="shrink-0 text-[10px] text-slate-500 dark:text-slate-400">
                                    used by {{ $this->themeUsage($group, $variant) }}
                                </span>

                                <x-bw-button variant="danger" size="xs"
                                    wire:click="deleteTheme('{{ $group }}', '{{ $variant }}')"
                                    :disabled="$this->themeUsage($group, $variant) > 0">
                                    Delete
                                </x-bw-button>
                            </li>
                        @endforeach
                    </ul>
                </div>
            @endforeach
        </div>
    @else
        <p class="mt-2 text-[11px] text-slate-500 dark:text-slate-400">
            No themes yet. Add one above and it can be used on any component.
        </p>
    @endif
</div>
