<div
    data-builder-canvas
    data-dirty="{{ $this->dirty() ? 'true' : 'false' }}"
    x-data="{
        shortcut(event) {
            if (! (event.ctrlKey || event.metaKey)) return;

            const key = event.key.toLowerCase();

            // A save from the keyboard has to work while the name of the
            // composition is being typed too: naming the composition and
            // pressing Ctrl+S is the natural flow, and the browser would
            // open its own save-page dialog otherwise.
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
