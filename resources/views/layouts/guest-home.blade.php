<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <title>{{ filled($title ?? null) ? $title . ' - ' . config('app.name', 'Laravel') : config('app.name', 'Laravel') }}
    </title>

    <link rel="icon" href="/icons/IconTitle.png" type="image/png">
    <link rel="apple-touch-icon" href="/icons/IconTitle.png">

    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=outfit:400,500,600,700" rel="stylesheet" />

    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @livewireStyles

    @if(config('googlemaps.api_key'))
        <script src="https://maps.googleapis.com/maps/api/js?key={{ config('googlemaps.api_key') }}&libraries=places&loading=async" async defer></script>
    @endif
</head>

<body class="font-['Outfit'] antialiased">
    <!-- Loading Overlay -->
    <x-loading-overlay />

    <!-- Splash Screen -->
    @livewire('splash-screen')

    {{ $slot }}

    <!-- Help Center Modal -->
    <x-help-center />

    @livewireScripts
</body>

</html>
