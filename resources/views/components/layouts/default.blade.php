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
       // The local assets are the ones Vite knows about: the paths in its
       // manifest. A remote asset is a full url and goes through
       // :remoteAssets instead, so the two are never mixed up.
       $replaceAssets = $replaceAssets ?? [];
       $additionalAssets = $additionalAssets ?? [];

        if(count($replaceAssets) > 0) {
             $assets = $replaceAssets;
        } else {
            $assets = array_merge(['resources/css/app.css', 'resources/js/app.ts'], $additionalAssets);
        }

        // The toggle ships its own script, so it works on pages that
        // replace the default assets with their own bundle. Its styles
        // live in shared.css, outside any cascade layer.
        $assets = array_merge($assets, ['resources/js/appearance.ts']);

        // A remote asset has no entry in the Vite manifest, so it is loaded
        // as it is instead of going through Vite.
        $remoteAssets = $remoteAssets ?? [];

    @endphp

    @if (!($disableLocalAssets ?? false))
        @vite($assets)
    @endif

    @foreach ($remoteAssets['css'] ?? [] as $link)
        <link rel="stylesheet" href="{{ $link }}" data-navigate-track="reload">
    @endforeach

    @foreach ($remoteAssets['js'] ?? [] as $script)
        <script src="{{ $script }}"></script>
    @endforeach

    @isset($head)
        {{ $head }}
    @endisset

    @livewireStyles
    @fluxAppearance
    <script>
        // Bootstrap 5.3 follows the same mode without waiting for the module.
        document.documentElement.dataset.bsTheme = document.documentElement.classList.contains('dark') ? 'dark' : 'light';
    </script>
</head>
<body class="site-body">

    <div class="main-wrapper">
        <nav class="main-nav">
            {{ $mainMenu }}
            @if (!($disableLocalAssets ?? false))
                {{ \App\Menu\AppearanceToggle::make() }}
            @endif
        </nav>
        <main class="mt-sm">
            {{ $slot }}
        </main>
    </div>
    
    @livewireScripts
    @fluxScripts
</body>
</html>