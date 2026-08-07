{{-- resources/views/components/sidebar/item.blade.php --}}
{{-- Reusable sidebar item component with enhanced active state styling --}}
@props([
    'route' => null,
    'label' => '',
    'icon' => null,
    'isParent' => false,
    'childRoutes' => [],
    'isCollapsed' => false,
])

@php
    // Use Laravel's request()->routeIs() for accurate route detection
    $isActive = false;
    if ($route) {
        if ($isParent && !empty($childRoutes)) {
            // Parent menu: active if any child route is active
            foreach ($childRoutes as $childRoute) {
                if (request()->routeIs($childRoute)) {
                    $isActive = true;
                    break;
                }
            }
            // Also check the parent route itself
            if (!$isActive && request()->routeIs($route)) {
                $isActive = true;
            }
        } else {
            $isActive = request()->routeIs($route);
        }
    }

    // Convert route to pattern for Alpine.js compatibility
    $routePattern = $route ? '\\/' . str_replace('.', '\\/', $route) : '';
@endphp

@if($isCollapsed)
    {{-- Collapsed State: Icon in rounded square --}}
    <a href="{{ $route ? route($route) : '#' }}"
       wire:navigate
       class="sidebar-menu-item group relative flex items-center justify-center rounded-xl w-11 h-11 mx-auto transition-all duration-200 ease-out
              {{ $isActive
                  ? 'bg-emerald-500 text-white shadow-lg shadow-emerald-500/30 scale-105'
                  : 'bg-white/5 text-white/70 hover:bg-white/10 hover:text-white' }}">
        <span class="grid size-5 place-content-center transition-transform duration-200 ease-out
                     {{ $isActive ? 'scale-105' : 'group-hover:scale-110' }}">
            @if($icon)
                @include('livewire.icons.' . str_replace('.', '/', $icon))
            @else
                <svg class="size-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <circle cx="12" cy="12" r="4" />
                </svg>
            @endif
        </span>

        {{-- Tooltip --}}
        <span class="pointer-events-none absolute left-full top-1/2 z-50 ml-2 -translate-y-1/2 translate-x-2 rounded-lg bg-slate-900 px-2.5 py-1.5 text-xs font-semibold whitespace-nowrap text-white opacity-0 shadow-xl ring-1 ring-white/10 transition-all duration-200 ease-out group-hover:translate-x-0 group-hover:opacity-100">
            {{ $label }}
        </span>
    </a>
@else
    {{-- Expanded State: Full menu item with styling --}}
    <a href="{{ $route ? route($route) : '#' }}"
       wire:navigate
       class="sidebar-menu-item group relative flex items-center justify-start gap-3 rounded-xl px-3 py-2.5 text-sm font-medium transition-all duration-200 ease-out
              {{ $isActive
                  ? 'bg-emerald-500/15 text-white shadow-sm shadow-emerald-500/10 border-l-[3px] border-emerald-400'
                  : 'text-white/70 border-l-[3px] border-transparent hover:bg-white/10 hover:text-white' }}">
        <span class="grid size-5 shrink-0 place-content-center transition-all duration-200 ease-out
                     {{ $isActive
                         ? 'text-emerald-400 scale-105'
                         : 'text-white/70 group-hover:scale-110' }}">
            @if($icon)
                @include('livewire.icons.' . str_replace('.', '/', $icon))
            @else
                <svg class="size-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <circle cx="12" cy="12" r="4" />
                </svg>
            @endif
        </span>
        <span class="flex-1 inline-flex whitespace-nowrap {{ $isActive ? 'font-semibold' : '' }}">
            {{ $label }}
        </span>

        {{-- Active indicator dot (optional, for non-parent items) --}}
        @if($isActive && !$isParent)
            <span class="flex size-1.5 shrink-0 items-center justify-center">
                <span class="size-1.5 rounded-full bg-emerald-400"></span>
            </span>
        @endif
    </a>
@endif
