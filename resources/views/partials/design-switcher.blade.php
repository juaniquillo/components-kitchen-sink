@php
    // The design chips both the gallery and the dashboard share. The route
    // name decides where a chip points, so each page keeps its own place.
    $switchRoute = $switchRoute ?? 'design-system';
@endphp

<div class="mt-sm flex flex-wrap justify-center gap-2">
    @foreach ($designs as $option)
        <a
            href="{{ route($switchRoute, ['design' => $option['key']]) }}"
            class="border px-3 py-1 text-sm {{ $option['key'] === $design->key ? 'border-zinc-900 bg-zinc-900 text-white dark:border-zinc-100 dark:bg-zinc-100 dark:text-black' : 'border-zinc-300 hover:bg-zinc-100 dark:border-zinc-600 dark:hover:bg-zinc-800' }}"
        >{{ $option['label'] }}</a>
    @endforeach
</div>
