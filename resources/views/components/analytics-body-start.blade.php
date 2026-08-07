{{-- Google Tag Manager (noscript - placed right after opening body tag) --}}
@if(config('services.google.tag_manager_id') && config('services.google.tag_manager_id') !== '')
<!-- Google Tag Manager (noscript) -->
<iframe src="https://www.googletagmanager.com/ns.html?id={{ config('services.google.tag_manager_id') }}"
height="0" width="0" style="display:none;visibility:hidden"></iframe>
@endif
