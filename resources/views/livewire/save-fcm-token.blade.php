<div>
    {{-- Hidden component that listens for FCM token events --}}
    @if(auth()->check())
        <div x-data="{
            initFcm() {
                if (typeof window.initializeFcm === 'function') {
                    window.initializeFcm();
                }
            }
        }" x-init="initFcm()"></div>
    @endif
</div>
