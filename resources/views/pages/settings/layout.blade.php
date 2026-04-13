<div class="grid gap-6 lg:grid-cols-[240px_minmax(0,1fr)]">
    <nav aria-label="{{ __('Settings') }}" class="rounded-3xl border border-white/10 bg-white/5 p-2 text-sm font-medium text-zinc-300">
        <a href="{{ route('profile.edit') }}" wire:navigate class="block rounded-2xl px-4 py-3 transition {{ request()->routeIs('profile.edit') ? 'bg-sky-500/15 text-sky-200 ring-1 ring-inset ring-sky-400/30' : 'hover:bg-white/5 hover:text-white' }}">{{ __('Profile') }}</a>
        <a href="{{ route('security.edit') }}" wire:navigate class="block rounded-2xl px-4 py-3 transition {{ request()->routeIs('security.edit') ? 'bg-sky-500/15 text-sky-200 ring-1 ring-inset ring-sky-400/30' : 'hover:bg-white/5 hover:text-white' }}">{{ __('Security') }}</a>
        <a href="{{ route('appearance.edit') }}" wire:navigate class="block rounded-2xl px-4 py-3 transition {{ request()->routeIs('appearance.edit') ? 'bg-sky-500/15 text-sky-200 ring-1 ring-inset ring-sky-400/30' : 'hover:bg-white/5 hover:text-white' }}">{{ __('Appearance') }}</a>
    </nav>

    <div class="rounded-3xl border border-white/10 bg-white/5 p-6 shadow-xl shadow-black/10">
        <div class="border-b border-white/10 pb-5">
            <h2 class="text-2xl font-black tracking-tight text-white">{{ $heading ?? '' }}</h2>
            <p class="mt-2 text-sm leading-6 text-zinc-400">{{ $subheading ?? '' }}</p>
        </div>

        <div class="mt-6 space-y-5">
            @if (session('status'))
                <div class="rounded-2xl border border-emerald-400/20 bg-emerald-400/10 px-4 py-3 text-sm text-emerald-200">
                    {{ session('status') }}
                </div>
            @endif

            {{ $slot }}
        </div>
    </div>
</div>
