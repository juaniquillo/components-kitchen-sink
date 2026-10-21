@php
    $node = $this->configuring;
    $descriptor = $this->configuringDescriptor;
@endphp

<aside
    class="fixed inset-y-0 right-0 z-40 w-96 overflow-y-auto border-l border-slate-200 bg-white p-4 shadow-xl transition-transform duration-200 dark:border-slate-700 dark:bg-slate-900 {{ $configuringId !== null ? 'translate-x-0' : 'translate-x-full' }}"
    aria-label="Component configuration"
>
    <div class="mb-4 flex items-center justify-between">
        <h2 class="text-xs font-semibold tracking-wide text-slate-500 uppercase">Configure</h2>

        <button
            type="button"
            wire:click="closeConfig"
            class="rounded border border-slate-200 px-2 py-1 text-xs text-slate-500 hover:bg-slate-50 dark:border-slate-600"
        >
            Close
        </button>
    </div>

    @if ($node === null || $descriptor === null)
        <p class="text-xs text-slate-400">Select a node and press its configure button.</p>
    @else
        <div class="space-y-5">
            <div class="flex items-center justify-between">
                <span class="rounded bg-indigo-100 px-2 py-0.5 text-xs font-medium text-indigo-700 dark:bg-indigo-500/20 dark:text-indigo-300">
                    {{ $descriptor->label }}
                </span>

                <span class="text-[11px] text-slate-400">{{ $node['id'] }}</span>
            </div>

            <div class="flex flex-wrap gap-1">
                <button type="button" wire:click="moveUp('{{ $node['id'] }}')"
                    class="rounded border border-slate-200 px-2 py-1 text-xs hover:bg-slate-50 dark:border-slate-600">Up</button>
                <button type="button" wire:click="moveDown('{{ $node['id'] }}')"
                    class="rounded border border-slate-200 px-2 py-1 text-xs hover:bg-slate-50 dark:border-slate-600">Down</button>
                <button type="button" wire:click="duplicate('{{ $node['id'] }}')"
                    class="rounded border border-slate-200 px-2 py-1 text-xs hover:bg-slate-50 dark:border-slate-600">Duplicate</button>
            </div>

            @if ($descriptor->acceptsChildren())
                <div>
                    <label class="mb-1 block text-[11px] font-medium text-slate-500">Text</label>

                    @if ($this->textSegments !== [])
                        <ul class="space-y-1">
                            @foreach ($this->textSegments as $segment)
                                <li class="flex items-center gap-1">
                                    <input
                                        type="text"
                                        value="{{ $segment['text'] }}"
                                        wire:input.debounce.500ms="updateText('{{ $segment['id'] }}', $event.target.value)"
                                        class="w-full rounded border border-slate-200 px-2 py-1 text-xs dark:border-slate-600 dark:bg-slate-900"
                                    />
                                    <button
                                        type="button"
                                        wire:click="removeText('{{ $segment['id'] }}')"
                                        class="shrink-0 rounded border border-red-200 px-1.5 py-0.5 text-[10px] text-red-600 hover:bg-red-50 dark:border-red-900"
                                    >
                                        Delete
                                    </button>
                                </li>
                            @endforeach
                        </ul>
                    @else
                        <p class="text-[11px] text-slate-400">
                            No text yet. Use the + on the node to add some.
                        </p>
                    @endif
                </div>
            @endif

            <div>
                <label class="mb-1 block text-[11px] font-medium text-slate-500">Themes</label>

                @if ($this->configuringThemes !== [])
                    <ul class="mb-2 space-y-1">
                        @foreach ($this->configuringThemes as $theme)
                            <li class="flex items-center justify-between gap-2 text-[11px]">
                                <span class="min-w-0 truncate">
                                    <span class="font-medium text-slate-700 dark:text-slate-200">
                                        {{ $theme['group'] }} / {{ $theme['variant'] }}
                                    </span>

                                    @if ($theme['missing'])
                                        <span class="text-red-500">(missing)</span>
                                    @endif
                                </span>

                                <button
                                    type="button"
                                    wire:click="removeNodeTheme('{{ $node['id'] }}', '{{ $theme['group'] }}', '{{ $theme['variant'] }}')"
                                    class="shrink-0 rounded border border-red-200 px-1.5 py-0.5 text-[10px] text-red-600 hover:bg-red-50 dark:border-red-900"
                                >
                                    Remove
                                </button>
                            </li>
                        @endforeach
                    </ul>
                @endif

                @if ($themes !== [])
                    @foreach ($themes as $group => $variants)
                        <div class="mb-2 last:mb-0">
                            <p class="mb-1 text-[10px] text-slate-400">{{ $group }}</p>

                            <div class="flex flex-wrap gap-1">
                                @foreach ($variants as $variant => $classes)
                                    <button
                                        type="button"
                                        wire:click="toggleTheme('{{ $node['id'] }}', '{{ $group }}', '{{ $variant }}')"
                                        title="{{ $classes }}"
                                        @class([
                                            'rounded border px-2 py-0.5 text-[10px]',
                                            'border-indigo-500 bg-indigo-50 text-indigo-700 dark:bg-indigo-500/20 dark:text-indigo-300' => $this->nodeUsesTheme($node['id'], $group, $variant),
                                            'border-slate-200 text-slate-500 hover:bg-slate-50 dark:border-slate-600 dark:text-slate-300' => ! $this->nodeUsesTheme($node['id'], $group, $variant),
                                        ])
                                    >
                                        {{ $variant }}
                                    </button>
                                @endforeach
                            </div>
                        </div>
                    @endforeach
                @else
                    <p class="text-[11px] text-slate-400">
                        No themes yet. Add one in the themes panel.
                    </p>
                @endif
            </div>

            <div>
                <label class="mb-1 block text-[11px] font-medium text-slate-500">Attributes</label>

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

                    <button
                        type="button"
                        wire:click="addAttribute"
                        class="shrink-0 rounded border border-slate-200 px-2 py-1 text-xs text-slate-600 hover:bg-slate-50 dark:border-slate-600 dark:text-slate-300"
                    >
                        {{ $editingAttribute !== null ? 'Update' : 'Add' }}
                    </button>
                </div>

                @if ($editingAttribute !== null)
                    <button
                        type="button"
                        wire:click="cancelAttribute"
                        class="mt-1 text-[11px] text-slate-400 underline"
                    >
                        Cancel
                    </button>
                @endif

                <datalist id="attribute-suggestions-{{ $node['id'] }}">
                    @foreach (array_unique([...$descriptor->suggestedAttributes, ...array_keys($node['attrs'])]) as $suggestion)
                        <option value="{{ $suggestion }}"></option>
                    @endforeach
                </datalist>

                @if ($node['attrs'] !== [])
                    <ul class="mt-2 divide-y divide-slate-100 dark:divide-slate-700">
                        @foreach ($node['attrs'] as $name => $value)
                            <li class="flex items-center justify-between gap-2 py-1">
                                <span class="min-w-0 truncate text-[11px]">
                                    <span class="font-medium text-slate-700 dark:text-slate-200">{{ $name }}</span>
                                    <span class="text-slate-400">= &quot;{{ $value }}&quot;</span>
                                </span>

                                <span class="flex shrink-0 gap-1">
                                    <button
                                        type="button"
                                        wire:click="editAttribute('{{ $name }}')"
                                        class="rounded border border-slate-200 px-1.5 py-0.5 text-[10px] text-slate-500 hover:bg-slate-50 dark:border-slate-600"
                                    >
                                        Edit
                                    </button>
                                    <button
                                        type="button"
                                        wire:click="deleteAttribute('{{ $name }}')"
                                        class="rounded border border-red-200 px-1.5 py-0.5 text-[10px] text-red-600 hover:bg-red-50 dark:border-red-900"
                                    >
                                        Delete
                                    </button>
                                </span>
                            </li>
                        @endforeach
                    </ul>
                @else
                    <p class="mt-2 text-[11px] text-slate-400">No attributes yet.</p>
                @endif
            </div>
        </div>
    @endif
</aside>
