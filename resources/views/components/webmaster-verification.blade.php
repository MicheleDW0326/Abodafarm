{{-- Search Engine Verification Meta Tags --}}
@if(config('services.google.site_verification'))
    <meta name="google-site-verification" content="{{ config('services.google.site_verification') }}">
@endif

@if(config('services.bing.site_verification'))
    <meta name="msvalidate.01" content="{{ config('services.bing.site_verification') }}">
@endif

@if(config('services.yandex.site_verification'))
    <meta name="yandex-verification" content="{{ config('services.yandex.site_verification') }}">
@endif
