<div class="space-y-4">
    <div class="grid gap-4 md:grid-cols-[210px_minmax(0,1fr)]">
        @include('livewire.builder._palette')

        @include('livewire.builder._canvas')
    </div>

    @include('livewire.builder._themes')

    @include('livewire.builder._code')

    @include('livewire.builder._saved')

    @include('livewire.builder._drawer')
</div>
