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
       $additionalAssets = $additionalAssets ?? [];

        if(count($replaceAssets) > 0) {
             $assets = $replaceAssets;
        } else {
            $assets = array_merge(['resources/css/app.css', 'resources/js/app.js'], $additionalAssets);
        }

        // A remote asset has no entry in the Vite manifest, so it is loaded
        // as it is instead of going through Vite.
        $localAssets = [];
        $remoteAssets = $remoteAssets  ?? [];

    @endphp

    @if (!($disableLocalAssets ?? false))
        @vite($assets)
    @endif

    @php
        $cssLink = $remoteAssets['css'] ?? [];
        $jsScript = $remoteAssets['js'] ?? [];
    @endphp

    @foreach ($cssLink as $link)
        <link rel="stylesheet" href="{{ $link }} data-navigate-track="reload"">
    @endforeach
        
    @foreach ($jsScript as $script)
        <script src="{{ $script }}"></script>
    @endforeach
        
    

    @isset($head)
        {{ $head }}
    @endisset

    @livewireStyles
    @fluxAppearance
</head>
<body class="font-sans antialiased bg-gray-200 dark:bg-slate-700 dark:text-white">

    <div class="max-w-7xl mx-auto p-sm">
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