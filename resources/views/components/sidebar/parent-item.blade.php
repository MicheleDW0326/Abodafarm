{{-- resources/views/components/sidebar/parent-item.blade.php --}}
{{-- Parent menu item with collapsible submenu and active state for any child page --}}
@props([
    'route' => null,
    'label' => '',
    'icon' => null,
    'childRoutes' => [],
    'childLabels' => [],
    'childIcons' => [],
    'isCollapsed' => false,
])

@php
    // Use Laravel's request()->routeIs() for accurate route detection
    $isActive = false;
    $activeChildIndex = -1;

    if (!empty($childRoutes)) {
        foreach ($childRoutes as $index => $childRoute) {
            if (request()->routeIs($childRoute)) {
                $isActive = true;
                $activeChildIndex = $index;
                break;
            }
        }
    }

    // Also check if parent route itself is active
    if (!$isActive && $route && request()->routeIs($route)) {
        $isActive = true;
    }

    // Convert route to pattern for Alpine.js compatibility
    $routePattern = $route ? '\\/' . str_replace('.', '\\/', $route) : '';
@endphp

@if($isCollapsed)
    {{-- Collapsed State: Parent menu with dropdown --}}
    <div x-data="{ open: {{ $isActive ? 'true' : 'false' }} }" class="relative">
        <button type="button"
            class="sidebar-menu-item group relative flex items-center justify-center rounded-xl w-11 h-11 mx-auto transition-all duration-200 ease-out
                   {{ $isActive
                       ? 'bg-emerald-500 text-white shadow-lg shadow-emerald-500/30 scale-105'
                       : 'bg-white/5 text-white/70 hover:bg-white/10 hover:text-white' }}"
            @click="open = !open">
            <span class="grid size-5 place-content-center transition-transform duration-200 ease-out
                         {{ $isActive ? 'scale-105' : 'group-hover:scale-110' }}">
                @if($icon)
                    @include('livewire.icons.' . str_replace('.', '/', $icon))
                @else
                    <svg class="size-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M4 6h16M4 12h16M4 18h16" stroke-linecap="round" />
                    </svg>
                @endif
            </span>

            {{-- Tooltip --}}
            <span class="pointer-events-none absolute left-full top-1/2 z-50 ml-2 -translate-y-1/2 translate-x-2 rounded-lg bg-slate-900 px-2.5 py-1.5 text-xs font-semibold whitespace-nowrap text-white opacity-0 shadow-xl ring-1 ring-white/10 transition-all duration-200 ease-out group-hover:translate-x-0 group-hover:opacity-100">
                {{ $label }}
            </span>
        </button>

        {{-- Submenu dropdown --}}
        <div x-show="open"
             x-cloak
             x-transition:enter="transition ease-out duration-200"
             x-transition:enter-start="opacity-0 translate-y-2"
             x-transition:enter-end="opacity-100 translate-y-0"
             x-transition:leave="transition ease-in duration-150"
             x-transition:leave-start="opacity-100 translate-y-0"
             x-transition:leave-end="opacity-0 translate-y-2"
             @click.away="open = false"
             class="absolute left-full top-0 ml-2 min-w-[180px] rounded-xl bg-slate-800 border border-white/10 shadow-xl py-1.5 z-50">
            <div class="px-3 py-1.5 text-xs font-semibold text-white/40 uppercase tracking-wider">
                {{ $label }}
            </div>
            @foreach($childRoutes as $index => $childRoute)
                @php $childLabel = $childLabels[$index] ?? 'Child ' . ($index + 1); @endphp
                @php $childIcon = isset($childIcons[$index]) ? $childIcons[$index] : null; @endphp
                @php $isChildActive = request()->routeIs($childRoute); @endphp
                <a href="{{ route($childRoute) }}"
                   wire:navigate
                   class="flex items-center gap-2.5 px-3 py-2 text-sm transition-all duration-150 ease-out
                          {{ $isChildActive
                              ? 'bg-emerald-500/15 text-emerald-300 font-medium'
                              : 'text-white/70 hover:bg-white/10 hover:text-white' }}">
                    @if($childIcon)
                        <span class="grid size-4 place-content-center {{ $isChildActive ? 'text-emerald-400' : 'text-white/50' }}">
                            @include('livewire.icons.' . str_replace('.', '/', $childIcon))
                        </span>
                    @endif
                    {{ $childLabel }}
                    @if($isChildActive)
                        <span class="ml-auto size-1.5 rounded-full bg-emerald-400"></span>
                    @endif
                </a>
            @endforeach
        </div>
    </div>
@else
    {{-- Expanded State: Parent with collapsible children --}}
    <div x-data="{ open: {{ $isActive ? 'true' : 'false' }} }" class="space-y-1">
        <button type="button"
            class="sidebar-parent-item group relative flex w-full items-center justify-start gap-3 rounded-xl px-3 py-2.5 text-sm font-medium transition-all duration-200 ease-out
                   {{ $isActive
                       ? 'bg-emerald-500/15 text-white shadow-sm shadow-emerald-500/10 border-l-[3px] border-emerald-400'
                       : 'text-white/70 border-l-[3px] border-transparent hover:bg-white/10 hover:text-white' }}"
            @click="open = !open">
            <span class="grid size-5 shrink-0 place-content-center transition-all duration-200 ease-out
                         {{ $isActive
                             ? 'text-emerald-400 scale-105'
                             : 'text-white/70 group-hover:scale-110' }}">
                @if($icon)
                    @include('livewire.icons.' . str_replace('.', '/', $icon))
                @else
                    <svg class="size-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M4 6h16M4 12h16M4 18h16" stroke-linecap="round" />
                    </svg>
                @endif
            </span>
            <span class="flex-1 text-left {{ $isActive ? 'font-semibold' : '' }}">{{ $label }}</span>
            <svg class="size-4 text-white/50 transition-transform duration-200 ease-out"
                 :class="open ? 'rotate-180' : ''"
                 viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <path d="m6 9 6 6 6-6" stroke-linecap="round" stroke-linejoin="round" />
            </svg>
        </button>

        <div x-show="open"
             x-collapse
             class="pl-4 space-y-0.5">
            @foreach($childRoutes as $index => $childRoute)
                @php $childLabel = $childLabels[$index] ?? 'Child ' . ($index + 1); @endphp
                @php $childIcon = isset($childIcons[$index]) ? $childIcons[$index] : null; @endphp
                @php $isChildActive = request()->routeIs($childRoute); @endphp
                <a href="{{ route($childRoute) }}"
                   wire:navigate
                   class="sidebar-child-item group flex items-center gap-3 rounded-lg px-3 py-2 text-sm transition-all duration-150 ease-out
                          {{ $isChildActive
                              ? 'bg-emerald-500/15 text-white font-medium border-l-[2px] border-emerald-400'
                              : 'text-white/60 border-l-[2px] border-transparent hover:bg-white/10 hover:text-white hover:border-white/20' }}">
                    @if($childIcon)
                        <span class="grid size-4 shrink-0 place-content-center transition-transform duration-150 ease-out
                                     {{ $isChildActive ? 'text-emerald-400 scale-105' : 'text-white/50 group-hover:scale-110' }}">
                            @include('livewire.icons.' . str_replace('.', '/', $childIcon))
                        </span>
                    @endif
                    <span>{{ $childLabel }}</span>
                    @if($isChildActive)
                        <span class="ml-auto size-1.5 rounded-full bg-emerald-400"></span>
                    @endif
                </a>
            @endforeach
        </div>
    </div>
@endif
