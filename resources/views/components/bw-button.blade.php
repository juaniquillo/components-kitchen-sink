@props([
    'variant' => 'default',
    'size' => 'sm',
    'type' => 'button',
    'disabled' => false,
])

@php
    // The small buttons of the builder are the same shape everywhere, so the
    // shape lives here: a variant is the intent of the button and a size is
    // how much room it takes. Everything else — labels, wire: targets, Alpine
    // bindings — is passed through to the element.
    $styles = [
        'default' => 'border-slate-200 text-slate-600 hover:bg-slate-50 dark:border-slate-600 dark:text-slate-300 dark:hover:bg-slate-600',
        'primary' => 'border-indigo-600 bg-indigo-600 text-white hover:bg-indigo-700 dark:border-indigo-500 dark:bg-indigo-600 dark:hover:bg-indigo-700',
        'danger' => 'border-red-200 text-red-600 hover:bg-red-50 dark:border-red-900 dark:text-red-400 dark:hover:bg-red-500/10',
    ];

    $sizes = [
        'xs' => 'px-1.5 py-0.5 text-[10px]',
        'sm' => 'px-2 py-1 text-xs',
        'md' => 'px-3 py-1.5 text-xs',
    ];
@endphp

<button
    {{ $attributes->merge(['type' => $type])->class([
        'rounded border transition disabled:opacity-40',
        'focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-indigo-500',
        $styles[$variant] ?? $styles['default'],
        $sizes[$size] ?? $sizes['sm'],
    ]) }}
    @disabled($disabled)
>{{ $slot }}</button>
