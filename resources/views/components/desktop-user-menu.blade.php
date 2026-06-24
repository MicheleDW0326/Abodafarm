<details {{ $attributes->merge(['class' => 'relative']) }}>
    <summary class="list-none cursor-pointer rounded-2xl border border-white/10 bg-white/5 px-4 py-2 text-sm font-medium text-white transition hover:bg-white/10">
        <span class="flex items-center gap-3">
            <span class="grid size-9 place-content-center rounded-full bg-sky-400/15 text-xs font-bold text-sky-200 ring-1 ring-inset ring-sky-400/20">
                {{ auth()->user()->initials() }}
            </span>
            <span class="text-left">
                <span class="block truncate">{{ auth()->user()->name }}</span>
                <span class="block text-xs text-white/50">{{ auth()->user()->email }}</span>
            </span>
        </span>
    </summary>

    <div class="absolute right-0 z-30 mt-3 w-64 overflow-hidden rounded-3xl border border-white/10 bg-zinc-900 p-2 shadow-2xl shadow-black/30">
        <div class="px-3 py-3">
            <p class="text-sm font-semibold text-white">{{ auth()->user()->name }}</p>
            <p class="text-xs text-zinc-400">{{ auth()->user()->email }}</p>
        </div>

        @if(auth()->user()->hasRole('pelanggan'))
        <a href="{{ route('pelanggan.dashboard') }}" wire:navigate class="flex items-center gap-2 rounded-2xl px-3 py-2 text-sm text-zinc-200 transition hover:bg-white/5">
            <svg class="size-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <circle cx="12" cy="8" r="5"/>
                <path d="M20 21a8 8 0 0 0-16 0"/>
            </svg>
            Akun Saya
        </a>
        @endif

        <a href="{{ route('profile.edit') }}" wire:navigate class="block rounded-2xl px-3 py-2 text-sm text-zinc-200 transition hover:bg-white/5">
            {{ __('Settings') }}
        </a>

        <form method="POST" action="{{ route('logout') }}">
            @csrf
            <button type="submit" data-test="logout-button" class="block w-full rounded-2xl px-3 py-2 text-left text-sm text-red-300 transition hover:bg-red-500/10">
                {{ __('Log out') }}
            </button>
        </form>
    </div>
</details>
