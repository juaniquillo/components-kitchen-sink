<x-layouts.default page-title="Cruds" :additional-assets="['resources/js/form-group.js']">
    
    <div>
        <h1 class="text-4xl font-bold text-center">Cruds</h1>

            
            <div class="mt-6 flex flex-wrap gap-3 md:flex-row flex-col">
                @foreach ($cruds as $identifier => $component)
                        
                    <div id="{{ $identifier }}" class="flex-1 md:min-w-lg p-3 bg-gray-300 dark:bg-gray-800/40 rounded-md">
                        <div class="p-4">
                            
                            <x-success-alert :identifier="$identifier" />
                            
                            <h2 class="text-3xl mb-5">{{ $component['name'] }}</h2>

                            <div>
                                {{ $component['component'] }}
                            </div>
                        </div>
                    </div>
                    
                @endforeach
       
            </div>

</x-layouts.default> 