{{-- Monetag Push Notifications & Ads Integration --}}
@if(config('monetag.enable_push'))
<script>
    // Register Monetag Service Worker
    if ('serviceWorker' in navigator) {
        window.addEventListener('load', function() {
            navigator.serviceWorker.register('/sw.js', { scope: '/' })
                .then(function(registration) {
                    console.log('Monetag SW registered:', registration.scope);
                })
                .catch(function(error) {
                    console.log('Monetag SW registration failed:', error);
                });
        });
    }
</script>
@endif

@if(config('monetag.enable_ipn'))
{{-- Monetag In-Page Push Script (optional - uncomment if needed) --}}
{{-- 
<script src="https://{{ config('monetag.domain') }}/pfe/current/tag.min.js?z={{ config('monetag.zone_id') }}" data-cfasync="false" async></script>
--}}
@endif
