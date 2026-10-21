<div>
    <div class="space-y-4">
        <div class="grid gap-4 md:grid-cols-[210px_minmax(0,1fr)]">
            @include('livewire.builder._palette')

            @include('livewire.builder._canvas')
        </div>

        @include('livewire.builder._themes')

        @include('livewire.builder._code')

        @include('livewire.builder._saved')
    </div>

    {{--
        The drawer is an overlay and not a panel of the page, so it is kept
        out of the stack: the space between the panels is a margin on every
        child but the first, and it would push the drawer down.
    --}}
    @include('livewire.builder._drawer')
</div>
