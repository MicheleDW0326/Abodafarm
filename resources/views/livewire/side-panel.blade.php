<div x-data="{ open: @entangle('open') }">
    <div x-show="open" x-transition.opacity class="fixed inset-0 z-50 flex">
        <div class="ml-auto h-full w-full max-w-sm overflow-y-auto bg-white p-4 shadow-2xl">
            <div class="flex items-center justify-between">
                <h4 class="text-sm font-semibold">Chat & Bantuan</h4>
                <button @click="$wire.toggle()" class="rounded-lg border border-slate-200 px-2 py-1">Tutup</button>
            </div>

            <div class="mt-4 flex h-[60vh] flex-col gap-3 overflow-y-auto">
                <div class="flex items-start gap-3">
                    <div class="text-xs bg-slate-100 rounded-lg p-2">Halo! Ada yang bisa dibantu?</div>
                </div>
            </div>

            <div class="mt-4 sticky bottom-0 bg-white pt-3">
                <form wire:submit.prevent="sendMessage" class="flex gap-2">
                    <input wire:model.defer="message" type="text" placeholder="Tulis pesan..."
                        class="flex-1 rounded-lg border border-slate-200 px-3 py-2 text-sm" />
                    <button type="submit"
                        class="rounded-lg bg-emerald-600 px-3 py-2 text-sm font-semibold text-white">Kirim</button>
                </form>
            </div>
        </div>
    </div>
</div>
