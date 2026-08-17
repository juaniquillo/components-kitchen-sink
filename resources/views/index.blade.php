@php
    use App\Menu\MainMenu;
    $homeLinks = MainMenu::makeLinks();
@endphp

<x-layouts.default page-title="Home">
    
    <div>
        <h1 class="text-4xl font-bold text-center">Home</h1>

        {{ $homeLinks }}
    </div>

</x-layouts.default>        