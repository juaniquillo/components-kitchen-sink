@php
    use App\Emails\EmailCollection;
    use App\Menu\MainMenu;
    $homeLinks = MainMenu::makeLinks();
@endphp

<x-layouts.default page-title="Emails">

    <div>
        <h1 class="text-4xl font-bold text-center">Emails</h1>

        <div class="mt-8 space-y-4">
            {{ $modals }}
        </div>
    </div>

</x-layouts.default>
