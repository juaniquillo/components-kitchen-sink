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
        $remoteAssets = [];

        foreach ($assets as $asset) {
            if (preg_match('#^(https?:)?//#', $asset) === 1) {
                $remoteAssets[] = $asset;
            } else {
                $localAssets[] = $asset;
            }
        }

    @endphp

    @if (count($localAssets) > 0)
        @vite($localAssets)
    @endif

    @foreach ($remoteAssets as $asset)
        @if (str_ends_with(parse_url($asset, PHP_URL_PATH) ?: '', '.css'))
            <link rel="stylesheet" href="{{ $asset }}">
        @else
            <script src="{{ $asset }}"></script>
        @endif
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