@php
    use App\Menu\MainMenu;
    $mainMenu = MainMenu::makeNav();
@endphp

<!DOCTYPE html>
<html lang="en" class="scroll-smooth">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $pageTitle ?? 'Title' }}</title>

    @php
       $replaceAssets = $replaceAssets ?? [];

        if(count($replaceAssets) > 0) {
             $assets = $replaceAssets;
        } else {
            $additionalAssets = $additionalAssets ?? [];
            $assets = array_merge(['resources/css/app.css', 'resources/js/app.js'], $additionalAssets);
        }

    @endphp
    @vite($assets)

    @livewireStyles
    @fluxAppearance
</head>
<body class="font-sans antialiased bg-gray-200 dark:bg-slate-700 dark:text-white">

    <div class="max-w-7xl mx-auto p-4">
        <nav>
            {{ $mainMenu }}
        </nav>
        <main>
        
            {{ $slot }}
        </main>
    </div>
    
    @livewireScripts
    @fluxScripts
</body>
</html>