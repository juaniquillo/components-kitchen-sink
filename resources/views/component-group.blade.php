@php
    $pageTitle = "{$name} Components";
    /* @var array<string, array{name: string, components: ComponentCollection, assets: array<string>}> $group */
    $componentGroup = $group['components'];
    $assets = $group['assets'] ?? [];
@endphp
<x-layouts.default :page-title="$pageTitle" :replace-assets="$assets">
    
    <div>
        <h1 class="text-4xl font-bold text-center">Components</h1>

        <div class="mt-5 flex gap-2 justify-center">
            @foreach ($componentGroup->list() as $components)
                <div class="component-box-bg p-3">
                    <h2 class="component-box-h2">{{ $components::NAME }}</h2>
                    
                    <div class="mt-4">

                        @foreach ($components->list() as $component)
                            {{ $component }}
                        @endforeach
                    
                    </div>
                </div>
            @endforeach
        </div>

    </div>

</x-layouts.default>        