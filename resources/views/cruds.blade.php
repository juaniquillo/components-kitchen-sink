<x-layouts.default>
    
    <div>
        <h1 class="text-4xl font-bold text-center">Cruds</h1>

            
            <div class="mt-5 flex flex-wrap gap-3">
                @foreach ($cruds as $name => $component)
                        
                    <div id="{{ $name }}" class="flex-1 p-3 bg-gray-300 dark:bg-gray-800/40 rounded-md">
                        <div class="p-4">
                            
                            <x-success-alert :component="$component" />
                            
                            <h2 class="text-3xl mb-4">{{ $component['name'] }}</h2>

                            <div>
                                {{ $component['component'] }}
                            </div>
                        </div>
                    </div>
                    
                @endforeach
       
            </div>

</x-layouts.default> 