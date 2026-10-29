@use('App\Livewire\Builder\DrawerMode')
@use('App\Livewire\Builder\NoticeSurface')

@php
    $node = $this->configuring;
    $descriptor = $this->configuringDescriptor;
    $open = $drawer !== null;
@endphp

<aside
    data-builder-drawer
    data-open="{{ $open ? 'true' : 'false' }}"
    class="fixed inset-y-0 right-0 z-40 w-96 overflow-y-auto border-l border-slate-200 bg-white p-4 shadow-xl transition-transform duration-200 motion-reduce:transition-none dark:border-slate-700 dark:bg-slate-900 {{ $open ? 'translate-x-0' : 'translate-x-full' }}"
    aria-label="Builder drawer"
    @unless ($open) inert @endunless
    x-data="{ close() { if (this.$el.dataset.open === 'true' && ! document.querySelector('dialog[open]') && ! document.querySelector(':popover-open')) this.$wire.closeConfig() } }"
    x-on:keydown.escape.window="close()"
    x-on:builder-drawer-opened.window="$nextTick(() => $refs.close?.focus())"
    x-on:builder-drawer-closed.window="$nextTick(() => document.getElementById('builder-canvas-panel')?.focus())"
>
    <div class="mb-4 flex items-center justify-between">
        <h2 class="text-xs font-semibold tracking-wide text-slate-500 uppercase dark:text-slate-400">
            {{ match ($drawer) {
                DrawerMode::TABLE => 'Add table',
                DrawerMode::THEMES => 'Themes',
                default => 'Configure',
            } }}
        </h2>

        <x-bw-button x-ref="close" wire:click="closeConfig">Close</x-bw-button>
    </div>

    @if ($drawer === DrawerMode::TABLE)
        <div class="space-y-5">
            <p class="text-[11px] text-slate-500 dark:text-slate-400">
                A skeleton with a head row and the body rows goes under the
                node <span class="font-medium text-slate-600 dark:text-slate-300">{{ $drawerTarget }}</span>.
                Every cell holds a dash until something is typed into it.
            </p>

            <div class="grid grid-cols-2 gap-2">
                <label>
                    <span class="mb-1 block text-[11px] font-medium text-slate-500 dark:text-slate-400">Columns</span>

                    <input
                        type="number"
                        min="1"
                        max="99"
                        wire:model.number="newTableColumns"
                        class="w-full rounded border border-slate-200 px-2 py-1 text-xs dark:border-slate-600 dark:bg-slate-900"
                    />
                </label>

                <label>
                    <span class="mb-1 block text-[11px] font-medium text-slate-500 dark:text-slate-400">Rows</span>

                    <input
                        type="number"
                        min="1"
                        max="99"
                        wire:model.number="newTableRows"
                        class="w-full rounded border border-slate-200 px-2 py-1 text-xs dark:border-slate-600 dark:bg-slate-900"
                    />
                </label>
            </div>

            @if ($tableMessage = $this->noticeOn(NoticeSurface::TABLE))
                <p class="text-[11px] text-red-500" role="alert">{{ $tableMessage }}</p>
            @endif

            <div class="flex gap-2">
                <x-bw-button variant="primary" size="md" wire:click="scaffoldTable">Add table</x-bw-button>

                <x-bw-button wire:click="closeConfig">Cancel</x-bw-button>
            </div>
        </div>
    @elseif ($drawer === DrawerMode::THEMES)
        @include('livewire.builder._themes')
    @elseif ($node === null || $descriptor === null)
        <p class="text-xs text-slate-400">Select a node and press its configure button.</p>
    @else
        <div class="space-y-5">
            <div class="flex items-center justify-between">
                <span class="rounded bg-indigo-100 px-2 py-0.5 text-xs font-medium text-indigo-700 dark:bg-indigo-500/20 dark:text-indigo-300">
                    {{ $descriptor->label }}
                </span>

                <span class="text-[11px] text-slate-500 dark:text-slate-400">{{ $node['id'] }}</span>
            </div>

            <div class="flex flex-wrap gap-1">
                <x-bw-button wire:click="moveUp('{{ $node['id'] }}')">Up</x-bw-button>
                <x-bw-button wire:click="moveDown('{{ $node['id'] }}')">Down</x-bw-button>
                <x-bw-button wire:click="duplicate('{{ $node['id'] }}')">Duplicate</x-bw-button>
            </div>

            @if ($nodeNotice = $this->noticeOn(NoticeSurface::NODE))
                <p class="text-[11px] text-red-600 dark:text-red-400" role="alert">{{ $nodeNotice }}</p>
            @endif

            @if ($descriptor->acceptsText())
                <div>
                    <label class="mb-1 block text-[11px] font-medium text-slate-500 dark:text-slate-400">Text</label>

                    @if ($this->textSegments !== [])
                        <ul class="space-y-1">
                            @foreach ($this->textSegments as $segment)
                                <li wire:key="segment-{{ $segment['id'] }}" class="flex items-center gap-1">
                                    <input
                                        type="text"
                                        value="{{ $segment['text'] }}"
                                        wire:input.debounce.500ms="updateText('{{ $segment['id'] }}', $event.target.value)"
                                        class="w-full rounded border border-slate-200 px-2 py-1 text-xs dark:border-slate-600 dark:bg-slate-900"
                                    />
                                    <x-bw-button variant="danger" size="xs" wire:click="removeText('{{ $segment['id'] }}')">
                                        Delete
                                    </x-bw-button>
                                </li>
                            @endforeach
                        </ul>
                    @else
                        <p class="mb-1 text-[11px] text-slate-500 dark:text-slate-400">
                            No text yet.
                        </p>
                    @endif

                    <div class="mt-1 flex items-end gap-1">
                        <label class="flex-1">
                            <span class="sr-only">New text</span>
                            <input
                                type="text"
                                wire:model="textDraft"
                                wire:keydown.enter="addTextToConfiguring"
                                placeholder="Add text"
                                class="w-full rounded border border-slate-200 px-2 py-1 text-xs dark:border-slate-600 dark:bg-slate-900"
                            />
                        </label>

                        <x-bw-button wire:click="addTextToConfiguring">Add</x-bw-button>
                    </div>
                </div>
            @endif

            <div>
                <label class="mb-1 block text-[11px] font-medium text-slate-500 dark:text-slate-400">Attributes</label>

                <div class="flex items-end gap-1">
                    <label class="flex-1">
                        <span class="sr-only">Attribute name</span>
                        <input
                            type="text"
                            list="attribute-suggestions-{{ $node['id'] }}"
                            wire:model="attributeName"
                            wire:keydown.enter="addAttribute"
                            placeholder="name"
                            class="w-full rounded border border-slate-200 px-2 py-1 text-xs dark:border-slate-600 dark:bg-slate-900"
                        />
                    </label>

                    <label class="flex-1">
                        <span class="sr-only">Attribute value</span>
                        <input
                            type="text"
                            wire:model="attributeValue"
                            wire:keydown.enter="addAttribute"
                            placeholder="value"
                            class="w-full rounded border border-slate-200 px-2 py-1 text-xs dark:border-slate-600 dark:bg-slate-900"
                        />
                    </label>

                    <x-bw-button wire:click="addAttribute">
                        {{ $editingAttribute !== null ? 'Update' : 'Add' }}
                    </x-bw-button>
                </div>

                @if ($editingAttribute !== null)
                    <button
                        type="button"
                        wire:click="cancelAttribute"
                        class="mt-1 rounded text-[11px] text-slate-500 underline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-indigo-500 dark:text-slate-400"
                    >
                        Cancel
                    </button>
                @endif

                @if ($attributeMessage = $this->noticeOn(NoticeSurface::ATTRIBUTE))
                    <p class="mt-1 text-[11px] text-red-600 dark:text-red-400" role="alert">{{ $attributeMessage }}</p>
                @endif

                <datalist id="attribute-suggestions-{{ $node['id'] }}">
                    @foreach (array_unique([...$descriptor->suggestedAttributes, ...array_keys($node['attrs'])]) as $suggestion)
                        <option value="{{ $suggestion }}"></option>
                    @endforeach
                </datalist>

                @if ($node['attrs'] !== [])
                    <ul class="mt-2 divide-y divide-slate-100 dark:divide-slate-700">
                        @foreach ($node['attrs'] as $name => $value)
                            <li wire:key="attribute-{{ $name }}" class="flex items-center justify-between gap-2 py-1">
                                <span class="min-w-0 truncate text-[11px]">
                                    <span class="font-medium text-slate-700 dark:text-slate-200">{{ $name }}</span>
                                    <span class="text-slate-500 dark:text-slate-400">= &quot;{{ $value }}&quot;</span>
                                </span>

                                <span class="flex shrink-0 gap-1">
                                    <x-bw-button size="xs" wire:click="editAttribute('{{ $name }}')">Edit</x-bw-button>
                                    <x-bw-button variant="danger" size="xs" wire:click="deleteAttribute('{{ $name }}')">Delete</x-bw-button>
                                </span>
                            </li>
                        @endforeach
                    </ul>
                @else
                    <p class="mt-2 text-[11px] text-slate-500 dark:text-slate-400">No attributes yet.</p>
                @endif
            </div>

            <div>
                <label class="mb-1 block text-[11px] font-medium text-slate-500 dark:text-slate-400">Themes</label>

                @if ($this->configuringThemes !== [])
                    <ul class="mb-2 space-y-1">
                        @foreach ($this->configuringThemes as $theme)
                            <li wire:key="configuring-theme-{{ $theme['group'] }}-{{ $theme['variant'] }}" class="flex items-center justify-between gap-2 text-[11px]">
                                <span class="min-w-0 truncate">
                                    <span class="font-medium text-slate-700 dark:text-slate-200">
                                        {{ $theme['group'] }} / {{ $theme['variant'] }}
                                    </span>

                                    @if ($theme['missing'])
                                        <span class="text-red-600 dark:text-red-400">(missing)</span>
                                    @endif
                                </span>

                                <x-bw-button variant="danger" size="xs"
                                    wire:click="removeNodeTheme('{{ $node['id'] }}', '{{ $theme['group'] }}', '{{ $theme['variant'] }}')">
                                    Remove
                                </x-bw-button>
                            </li>
                        @endforeach
                    </ul>
                @endif

                @if ($themes !== [])
                    @foreach ($themes as $group => $variants)
                        <div wire:key="theme-group-{{ $group }}" class="mb-2 last:mb-0">
                            <p class="mb-1 text-[10px] text-slate-500 dark:text-slate-400">{{ $group }}</p>

                            <div class="flex flex-wrap gap-1">
                                @foreach ($variants as $variant => $classes)
                                    <button
                                        type="button"
                                        wire:key="chip-{{ $group }}-{{ $variant }}"
                                        wire:click="toggleTheme('{{ $node['id'] }}', '{{ $group }}', '{{ $variant }}')"
                                        title="{{ $classes }}"
                                        aria-pressed="{{ $this->nodeUsesTheme($node['id'], $group, $variant) ? 'true' : 'false' }}"
                                        @class([
                                            'rounded border px-2 py-0.5 text-[10px] focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-indigo-500',
                                            'border-indigo-500 bg-indigo-50 text-indigo-700 dark:bg-indigo-500/20 dark:text-indigo-300' => $this->nodeUsesTheme($node['id'], $group, $variant),
                                            'border-slate-200 text-slate-600 hover:bg-slate-50 dark:border-slate-600 dark:text-slate-300' => ! $this->nodeUsesTheme($node['id'], $group, $variant),
                                        ])
                                    >
                                        {{ $variant }}
                                    </button>
                                @endforeach
                            </div>
                        </div>
                    @endforeach
                @else
                    <p class="text-[11px] text-slate-500 dark:text-slate-400">
                        No themes yet. Add one in the themes panel.
                    </p>
                @endif
            </div>
        </div>
    @endif
</aside>
