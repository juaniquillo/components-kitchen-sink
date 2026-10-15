@php
    use App\Menu\MainMenu;
    $homeLinks = MainMenu::makeLinks();
@endphp

<x-layouts.default page-title="Builder">
    
    <div>
        <h1 class="text-4xl font-bold text-center">Builder</h1>

        <p class="mt-6 text-center">Builder</p>
    </div>

</x-layouts.default>