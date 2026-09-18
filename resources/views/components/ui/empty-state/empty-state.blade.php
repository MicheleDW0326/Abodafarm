{{--
    Empty State Component

    Usage:
    <x-ui.empty-state
        icon="fa-box"
        title="Belum ada produk"
        description="Tambahkan produk pertama Anda"
    >
        <x-slot name="action">
            <x-ui.btn.primary wire:click="openModal">
                Tambah Produk
            </x-ui.btn.primary>
        </x-slot>
    </x-ui.empty-state>
--}}

@props([
    'icon' => 'fa-inbox',
    'title' => 'Tidak ada data',
    'description' => null,
    'action' => null,
])

<div class="flex flex-col items-center justify-center py-12 text-center">
    {{-- Icon --}}
    <div class="mb-4 inline-flex h-16 w-16 items-center justify-center rounded-full bg-slate-100">
        <i class="fa-solid {{ $icon }} text-2xl text-slate-400"></i>
    </div>

    {{-- Title --}}
    <h3 class="text-lg font-semibold text-slate-900">{{ $title }}</h3>

    {{-- Description --}}
    @if($description)
        <p class="mt-1 max-w-sm text-sm text-slate-500">{{ $description }}</p>
    @endif

    {{-- Action Slot --}}
    @if(isset($action) || trim($action ?? ''))
        <div class="mt-6">
            {{ $action ?? '' }}
        </div>
    @endif
</div>
