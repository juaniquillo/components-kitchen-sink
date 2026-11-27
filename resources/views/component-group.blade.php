@php
    $pageTitle = "{$name} - Components";
    $componentGroup = $group['components'];
    $assets = $group['assets'] ?? [];
    
@endphp
<x-layouts.default :page-title="$pageTitle" :replace-assets="$assets">
    
    <div>
        
        <h1 class="text-4xl font-bold text-center">{{ $name }}</h1>

        {{ $links }}

        <div class="component-container mt-sm">
            @foreach ($componentGroup->list() as $components)
                 @php
                    /** @var \App\Components\Options\GroupOptions $options */
                    $options = $components->options();
                @endphp
                <div class="component-box-bg {{ $options->box->getCSS() }}">
                    <h2 class="component-box-h2">{{ $components::NAME }}</h2>

                   
                    <div class="{{ $options->container->getCSS() }}">

                        @foreach ($components->list() as $component)
                            {{ $component }}
                        @endforeach
                    
                    </div>
                </div>
            @endforeach
        </div>

    </div>

</x-layouts.default>        