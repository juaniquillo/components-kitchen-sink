<x-layouts.default>
    
    <div>
        <h1 class="text-4xl font-bold text-center">Components</h1>

        @foreach ($components as $name => $component)
            <h2 class="text-3xl mt-6">{{ ucfirst($name) }}</h2>
            
            <div class="mt-5 flex flex-wrap gap-2">
            @foreach ($component['group'] as $title => $item)
                
                <div class="flex-1 p-3 bg-gray-300 dark:bg-gray-800/40 rounded-md">
                    {{ $item }}
                </div>

            @endforeach
            </div>
        @endforeach
    </div>

</x-layouts.default> 