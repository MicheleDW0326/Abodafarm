{{-- Google Analytics 4 --}}
@if(config('services.google.analytics_id') && config('services.google.analytics_id') !== '')
<!-- Google Analytics -->
<script async src="https://www.googletagmanager.com/gtag/js?id={{ config('services.google.analytics_id') }}"></script>
<script>
    window.dataLayer = window.dataLayer || [];
    function gtag(){dataLayer.push(arguments);}
    gtag('js', new Date());
    gtag('config', '{{ config('services.google.analytics_id') }}');
</script>
@endif

{{-- Google Tag Manager --}}
@if(config('services.google.tag_manager_id') && config('services.google.tag_manager_id') !== '')
<!-- Google Tag Manager -->
<script>(function(w,d,s,l,i){w[l]=w[l]||[];w[l].push({'gtm.start':
new Date().getTime(),event:'gtm.js'});var f=d.getElementsByTagName(s)[0],
j=d.createElement(s),dl=l!='dataLayer'?'&l='+l:'';j.async=true;j.src=
'https://www.googletagmanager.com/gtm.js?id='+i+dl;f.parentNode.insertBefore(j,f);
})(window,document,'script','dataLayer','{{ config('services.google.tag_manager_id') }}');</script>
@endif

{{-- Microsoft Clarity --}}
@if(config('services.clarity.project_id') && config('services.clarity.project_id') !== '')
<!-- Microsoft Clarity -->
<script type="text/javascript">
    (function(c,l,a,r,i,t,y){
        c[a]=c[a]||function(){(c[a].q=c[a].q||[]).push(arguments)};
        t=l.createElement(r);t.async=1;t.src="https://www.clarity.ms/tag/"+i;
        y=l.getElementsByTagName(r)[0];y.parentNode.insertBefore(t,y);
    })(window, document, "clarity", "script", "{{ config('services.clarity.project_id') }}");
</script>
@endif

{{-- Facebook Pixel --}}
@if(config('services.facebook.pixel_id') && config('services.facebook.pixel_id') !== '')
<!-- Facebook Pixel -->
<script>
!function(f,b,e,v,n,t,s)
{if(f.fbq)return;n=f.fbq=function(){n.callMethod?
n.callMethod.apply(n,arguments):n.queue.push(arguments)};
if(!f._fbq)f._fbq=n;n.push=n;n.loaded=!0;n.version='2.0';
n.queue=[];t=b.createElement(e);t.async=!0;
t.src=v;s=b.getElementsByTagName(e)[0];
s.parentNode.insertBefore(t,s)}(window, document,'script',
'https://connect.facebook.net/en_US/fbevents.js');
fbq('init', '{{ config('services.facebook.pixel_id') }}');
@if(config('services.facebook.pixel_events_enabled'))
fbq('track', 'PageView');
@endif
</script>
<noscript>
<img height="1" width="1" style="display:none"
src="https://www.facebook.com/tr?id={{ config('services.facebook.pixel_id') }}&ev=PageView&noscript=1"/>
</noscript>
@endif
