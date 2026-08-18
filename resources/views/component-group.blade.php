@php
    $pageTitle = "{$name} - Components";
    $componentGroup = $group['components'];
    $assets = $group['assets'] ?? [];
    
@endphp
<x-layouts.default :page-title="$pageTitle" :replace-assets="$assets">
    
    <div>
        <div class="text-center ">
            <a class="text-blue-500 underline" href="{{ route('components') }}">Back to components</a>
        </div>

        <h1 class="mt-sm text-4xl font-bold text-center">{{ $name }}</h1>

        <div class="component-container mt-sm">
            @foreach ($componentGroup->list() as $components)
                @php
                    $options = $components->options();
                    $disableFlex = $options['disable-flex'] ?? false;
                    $flexColumn = $options['flex-column'] ?? false;
                    $flexGap = $options['flex-gap'] ?? 'flex-gap-sm';
                    
                @endphp
                <div class="component-box-bg p-sm ">
                    <h2 class="component-box-h2">{{ $components::NAME }}</h2>
                    
                    <div class="mt-sm @if(!$disableFlex)flex {{ $flexGap }} flex-wrap justify-center items-center @endif @if($flexColumn) flex-columns @endif">

                        @foreach ($components->list() as $component)
                            {{ $component }}
                        @endforeach
                    
                    </div>
                </div>
            @endforeach
        </div>

    </div>

</x-layouts.default>        