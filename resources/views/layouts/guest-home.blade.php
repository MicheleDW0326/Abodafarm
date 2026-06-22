<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <title>{{ filled($title ?? null) ? $title . ' - ' . config('app.name', 'Laravel') : config('app.name', 'Laravel') }}
    </title>

    <link rel="icon" href="/icons/favicon.ico" sizes="any">
    <link rel="icon" href="/icons/favicon.svg" type="image/svg+xml">
    <link rel="icon" href="/icons/icon.png" type="image/png">
    <link rel="apple-touch-icon" href="/icons/apple-touch-icon.png">

    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=outfit:400,500,600,700" rel="stylesheet" />

    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @livewireStyles
</head>

<body class="font-['Outfit'] antialiased">
    <!-- Loading Overlay -->
    <x-loading-overlay />

    {{ $slot }}

    @livewireScripts
</body>

</html>
