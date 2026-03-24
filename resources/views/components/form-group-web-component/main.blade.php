 @props([
    'attrs' => null,
])

@php
    /** @var \ChatAgency\BackendComponents\Components\DefaultAttributeBag $attrs */

    $serverAttrs = [];
    $content = null;
    $slot = $slot ?? null; // Ensure compatibility with Blade's default slot
    
    if($attrs) {
        // returns all attributes, including theme classes
        $serverAttrs = $attrs->getAttributes();

        //returns contents array
        $content = $attrs->content;
        
    }
@endphp

<form-group {{ $attributes->merge($serverAttrs) }}>{{ $content }}{{ $slot }}</form-group> 