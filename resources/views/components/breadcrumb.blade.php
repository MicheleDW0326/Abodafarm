{{--
    Breadcrumb Component - Automatic breadcrumb navigation

    Features:
    - Auto-detects current route
    - Mobile-friendly design
    - Schema.org JSON-LD support
    - Custom items support
    - Icon support

    Usage:
    <x-breadcrumb />

    With custom items:
    <x-breadcrumb :items="[
        ['name' => 'Home', 'url' => '/'],
        ['name' => 'Products', 'url' => '/products'],
        ['name' => 'Current Page']
    ]" />

    With Schema.org:
    <x-breadcrumb :show-schema="true" />
--}}

@props([
    'items' => null,        // Custom breadcrumb items array
    'showSchema' => true,  // Show JSON-LD schema
    'showIcons' => true,   // Show icons
    'maxItems' => 5,       // Max items to show on mobile
    'separator' => 'fa-chevron-right',
])

@php
    use App\Services\BreadcrumbService;

    // Generate breadcrumbs
    $breadcrumbService = app(BreadcrumbService::class);

    if ($items) {
        // Use custom items
        $breadcrumbs = $items;
    } else {
        // Auto-generate from route
        $breadcrumbs = $breadcrumbService->generate();
    }

    $count = count($breadcrumbs);

    // Generate JSON-LD schema
    $jsonLd = $showSchema ? $breadcrumbService->toJsonLd() : null;
@endphp

{{-- Breadcrumb Schema.org (hidden) --}}
@if($jsonLd)
<script type="application/ld+json">
{!! $jsonLd !!}
</script>
@endif

{{-- Breadcrumb Navigation --}}
<nav aria-label="Breadcrumb" class="breadcrumb-nav">
    <ol class="breadcrumb-list flex items-center gap-1.5 text-sm">
        @foreach($breadcrumbs as $index => $crumb)
            {{-- Separator --}}
            @if($index > 0)
                <li class="breadcrumb-separator flex items-center text-slate-400">
                    <i class="{{ $separator }} text-[10px]"></i>
                </li>
            @endif

            {{-- Breadcrumb Item --}}
            <li class="breadcrumb-item flex items-center">
                @if($loop->last || !$crumb['url'])
                    {{-- Current page (no link) --}}
                    <span class="breadcrumb-current flex items-center gap-1.5 font-medium text-slate-900"
                          @if($loop->last) aria-current="page" @endif>
                        @if($showIcons && $index === 0)
                            <i class="fa-solid fa-home text-emerald-600"></i>
                        @endif
                        <span class="hidden xs:inline">{{ $crumb['name'] }}</span>
                        <span class="xs:hidden">
                            @if($index === 0)
                                <i class="fa-solid fa-home text-emerald-600"></i>
                            @elseif($loop->last)
                                {{ Str::limit($crumb['name'], 15) }}
                            @else
                                <i class="fa-solid fa-ellipsis-h text-slate-400"></i>
                            @endif
                        </span>
                    </span>
                @else
                    {{-- Clickable breadcrumb --}}
                    <a href="{{ $crumb['url'] }}"
                       class="breadcrumb-link flex items-center gap-1.5 text-slate-600 transition-colors hover:text-emerald-600">
                        @if($showIcons && $index === 0)
                            <i class="fa-solid fa-home text-emerald-600"></i>
                        @endif
                        <span class="hidden sm:inline">{{ $crumb['name'] }}</span>
                        <span class="sm:hidden">
                            @if($index === 0)
                                <i class="fa-solid fa-home text-emerald-600"></i>
                            @else
                                {{ Str::limit($crumb['name'], 12) }}
                            @endif
                        </span>
                    </a>
                @endif
            </li>
        @endforeach
    </ol>
</nav>

<style>
    .breadcrumb-nav {
        contain: layout style;
    }

    .breadcrumb-list {
        list-style: none;
        margin: 0;
        padding: 0;
    }

    .breadcrumb-item {
        white-space: nowrap;
    }

    .breadcrumb-link {
        text-decoration: none;
    }

    .breadcrumb-link:hover {
        text-decoration: underline;
        text-underline-offset: 2px;
    }

    .breadcrumb-current {
        cursor: default;
    }

    .breadcrumb-separator {
        user-select: none;
    }

    /* Mobile optimization */
    @media (max-width: 480px) {
        .breadcrumb-list {
            font-size: 0.75rem;
            gap: 0.5rem;
        }

        .breadcrumb-link,
        .breadcrumb-current {
            gap: 0.25rem;
        }
    }
</style>
