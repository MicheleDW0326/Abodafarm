<meta charset="utf-8" />
<meta name="viewport" content="width=device-width, initial-scale=1.0" />

{{-- Search Engine Verification --}}
<x-webmaster-verification />

{{-- SEO Meta Tags (Auth pages are private - no structured data needed) --}}
<x-seo.meta
    :title="isset($seoTitle) ? $seoTitle : ($title ?? null)"
    :description="isset($seoDescription) ? $seoDescription : null"
    robots="noindex, nofollow"
/>

<link rel="icon" href="/icons/favicon.ico" sizes="any">
<link rel="icon" href="/icons/favicon.svg" type="image/svg+xml">
<link rel="icon" href="/icons/favicon.png" type="image/png">
<link rel="apple-touch-icon" href="/icons/apple-touch-icon.png">

<link rel="preconnect" href="https://fonts.bunny.net">
<link rel="preconnect" href="https://cdnjs.cloudflare.com" crossorigin>
<link href="https://fonts.bunny.net/css?family=instrument-sans:400,500,600" rel="stylesheet" />

{{-- Font Awesome Icons --}}
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" />

@vite(['resources/css/app.css', 'resources/js/app.js'])
@livewireStyles

{{-- Analytics & Tracking Scripts --}}
<x-analytics />
