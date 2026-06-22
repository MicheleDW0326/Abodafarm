<div class="space-y-6">
    <div>
        <h2 class="text-xl font-bold text-slate-900">Pengiriman</h2>
        <p class="mt-1 text-sm text-slate-500">Kelola pengiriman ternak Anda</p>
    </div>

    <div class="grid grid-cols-2 gap-4 lg:grid-cols-3">
        <article class="rounded-xl border border-slate-100 bg-white p-4">
            <p class="text-sm text-slate-500">Dalam Pengiriman</p>
            <p class="mt-1 text-2xl font-bold text-purple-600">{{ $stats['in_transit'] }}</p>
        </article>
        <article class="rounded-xl border border-slate-100 bg-white p-4">
            <p class="text-sm text-slate-500">Siap Kirim</p>
            <p class="mt-1 text-2xl font-bold text-blue-600">{{ $stats['processing'] }}</p>
        </article>
    </div>

    <div class="overflow-hidden rounded-2xl border border-slate-100 bg-white">
        <div class="overflow-x-auto">
            <table class="min-w-full">
                <thead class="bg-slate-50">
                    <tr class="text-left text-xs uppercase tracking-wide text-slate-500">
                        <th class="px-4 py-3 font-semibold">Kode</th>
                        <th class="px-4 py-3 font-semibold">Ternak</th>
                        <th class="px-4 py-3 font-semibold">Pembeli</th>
                        <th class="px-4 py-3 font-semibold">Alamat</th>
                        <th class="px-4 py-3 font-semibold">Status</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-50">
                    @forelse($shipments as $shipment)
                        <tr class="transition hover:bg-slate-50">
                            <td class="px-4 py-3">
                                <span class="font-semibold text-slate-900">{{ $shipment->order_number }}</span>
                            </td>
                            <td class="px-4 py-3 text-sm text-slate-900">
                                {{ $shipment->items->pluck('product_name')->filter()->implode(', ') }}
                            </td>
                            <td class="px-4 py-3 text-sm text-slate-900">{{ $shipment->customer_name }}</td>
                            <td class="px-4 py-3 text-sm text-slate-600">{{ Str::limit($shipment->customer_address, 30) ?? '-' }}</td>
                            <td class="px-4 py-3">
                                @php
                                    $statusClass = match($shipment->status) {
                                        'processing' => 'bg-blue-50 text-blue-700',
                                        'shipped' => 'bg-purple-50 text-purple-700',
                                        default => 'bg-slate-50 text-slate-700',
                                    };
                                    $statusLabel = match($shipment->status) {
                                        'processing' => 'Siap Kirim',
                                        'shipped' => 'Dikirim',
                                        default => $shipment->status,
                                    };
                                @endphp
                                <span class="rounded-full px-2.5 py-1 text-xs font-semibold {{ $statusClass }}">
                                    {{ $statusLabel }}
                                </span>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-4 py-12 text-center">
                                <i class="fa-solid fa-truck text-5xl text-slate-200"></i>
                                <p class="mt-4 text-slate-500">Belum ada pengiriman</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
