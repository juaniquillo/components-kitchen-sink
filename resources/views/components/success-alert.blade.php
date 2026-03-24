 @props([
    'identifier' => null,
])

@php
    use function Juaniquillo\BackendComponents\processThemes;
@endphp
 
 @if(session()->has('success_'.$identifier))
    <div class="max-w-6xl mx-auto mb-3">
        <div class="{{ processThemes([
            'padding' => 'xs', 
            'background' => [
                'success',
                'success-dark'
            ],
            'text' => 'center',
            'color' => [
                'success',
                'success-dark',
            ],
        ]) }}">{{ session()->get('success_'.$identifier) }}</div>
    </div>
@endif