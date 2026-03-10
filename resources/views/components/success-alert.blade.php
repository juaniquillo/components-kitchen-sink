 @props([
    'component' => [],
])

@php
    use function Juaniquillo\BackendComponents\processThemes;
@endphp
 
 @if(session()->has('success_'.$component['identifier']))
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
        ]) }}">{{ session()->get('success_'.$component['identifier']) }}</div>
    </div>
@endif