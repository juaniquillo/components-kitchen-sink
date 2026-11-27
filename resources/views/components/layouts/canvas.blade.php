@php
    $pageTitle = $pageTitle ?? 'Title';
@endphp

<!DOCTYPE html>
<html lang="en" class="scroll-smooth">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $pageTitle }}</title>

    @php
        // Same asset contract as the default layout, without the site nav:
        // the page renders like an app of its own instead of a page inside
        // the kitchen sink.
        $replaceAssets = $replaceAssets ?? [];
        $additionalAssets = $additionalAssets ?? [];

        if(count($replaceAssets) > 0) {
             $assets = $replaceAssets;
        } else {
            $assets = array_merge(['resources/css/app.css', 'resources/js/app.ts'], $additionalAssets);
        }

        // The site chrome ships its own stylesheet before the page bundle,
        // so the page utilities win any same-specificity tie. The script
        // stays last; it only wires the toggle.
        $assets = array_merge(['resources/css/navigation.css'], $assets, ['resources/js/appearance.ts']);

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
<body class="font-sans antialiased {{ $bodyClass ?? 'bg-zinc-100 dark:bg-zinc-900' }} dark:text-white">

    <main>
        @if (!($disableLocalAssets ?? false))
            <div class="canvas-appearance">
                {{ \App\Menu\AppearanceToggle::make() }}
            </div>
        @endif
        {{ $slot }}
    </main>

    @livewireScripts
    @fluxScripts
</body>
</html>
