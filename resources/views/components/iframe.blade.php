@props([
    'attrs' => null,
])

<?php
    /** @var \Juaniquillo\BackendComponents\Components\DefaultAttributeBag $attrs */
?>

@php
    $serverAttrs = [];
    $content = null;
    $slot = $slot ?? null;
    
    if($attrs) {

        $serverAttrs = $attrs->getAttributes();
        $content = $attrs->content;
        
    }

@endphp

<iframe {{ $attributes->merge($serverAttrs) }}></iframe>
