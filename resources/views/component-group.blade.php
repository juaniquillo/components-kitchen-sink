@php
    $pageTitle = "{$name} - Components";
    $componentGroup = $group['components'];
    $assets = $group['assets'] ?? [];
    
@endphp
<x-layouts.default :page-title="$pageTitle" :replace-assets="$assets">
    
    <div>
        
        <h1 class="mt-sm text-4xl font-bold text-center">{{ $name }}</h1>

        {{ $links }}

        <div class="component-container mt-sm">
            @foreach ($componentGroup->list() as $components)
                @php
                    $options = $components->options();
                    $disableFlex = $options->disableFlex;
                    $flexColumn = $options->flexColumn;
                    $flexGap = $options->flexGap;
                    
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