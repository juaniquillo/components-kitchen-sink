<div
    data-builder-canvas
    x-data="{
        shortcut(event) {
            if (! (event.ctrlKey || event.metaKey)) return;

            const key = event.key.toLowerCase();

            // A save from the keyboard has to work while the name of the
            // workspace is being typed too: naming it and pressing Ctrl+S is
            // the natural flow, and the browser would open its own save-page
            // dialog otherwise.
            if (key === 's' && ! event.shiftKey) {
                event.preventDefault();
                this.$wire.save();
                return;
            }

            // The keys the composition is undone and redone with belong to
            // the field the user is typing into otherwise, so the browser
            // keeps its own undo there.
            if (event.target.closest('input, textarea, select, [contenteditable]')) return;

            if (key === 'z' && ! event.shiftKey) {
                event.preventDefault();
                this.$wire.undo();
            } else if ((key === 'z' && event.shiftKey) || key === 'y') {
                event.preventDefault();
                this.$wire.redo();
            }
        }
    }"
    x-on:keydown.window="shortcut($event)"
>
    <header class="mb-4 flex flex-wrap items-end justify-between gap-3 rounded-lg bg-white p-3 shadow-sm dark:bg-slate-800">
        <label class="min-w-0 flex-1" for="builder-workspace-name">
            <span class="mb-1 block text-[11px] font-medium text-slate-500 dark:text-slate-400">Workspace</span>

            <input
                id="builder-workspace-name"
                type="text"
                wire:model.live.debounce.500ms="name"
                maxlength="120"
                class="w-full max-w-md rounded border border-slate-200 px-2 py-1 text-sm font-semibold dark:border-slate-600 dark:bg-slate-900"
            />
        </label>

        <div class="flex flex-wrap items-center gap-2">
            <p class="hidden text-[11px] text-slate-500 sm:block dark:text-slate-400">Changes save as you go.</p>

            <span class="contents" x-data="{ copied: false }">
                <x-bw-button
                    x-on:click="if (navigator.clipboard) navigator.clipboard.writeText(window.location.href).then(() => { copied = true; setTimeout(() => copied = false, 1500) })"
                    x-text="copied ? 'Copied ✓' : 'Copy link'"
                ></x-bw-button>

                <span class="sr-only" aria-live="polite" x-text="copied ? 'Link copied' : ''"></span>
            </span>

            <x-bw-button wire:click="newWorkspace">New</x-bw-button>

            <x-bw-button wire:click="resetCanvas" wire:confirm="The canvas will be cleared. The undo can bring it back. Are you sure?">
                Reset
            </x-bw-button>

            <x-bw-button variant="danger" wire:click="deleteWorkspace" wire:confirm="This workspace will be deleted for everyone holding the link. Are you sure?">
                Delete
            </x-bw-button>
        </div>
    </header>

    <div class="space-y-4">
        <div class="grid gap-4 md:grid-cols-[210px_minmax(0,1fr)]">
            @include('livewire.builder._palette')

            @include('livewire.builder._canvas')
        </div>

        @include('livewire.builder._code')
    </div>

    {{--
        The drawer is an overlay and not a panel of the page, so it is kept
        out of the stack: the space between the panels is a margin on every
        child but the first, and it would push the drawer down.
    --}}
    @include('livewire.builder._drawer')
</div>
