<div class="space-y-6">
    {{-- Header --}}
    <div class="flex items-center justify-between">
        <div>
            <p class="text-sm text-slate-500">Kelola semua pengguna dan role (admin, staf, peternak, pelanggan)</p>
        </div>
        <button wire:click="openUserModal()" class="flex items-center gap-2 rounded-lg bg-emerald-600 px-4 py-2.5 text-sm font-semibold text-white transition-all hover:bg-emerald-700">
            <i class="fa-solid fa-plus"></i>Tambah User
        </button>
    </div>

    {{-- Role Stats --}}
    <div class="grid grid-cols-2 gap-4 lg:grid-cols-5">
        <button wire:click="$set('roleFilter', 'all')" class="rounded-lg border bg-white p-4 text-left transition-all hover:border-emerald-500 {{ $roleFilter === 'all' ? 'border-emerald-500 bg-emerald-50' : 'border-slate-200' }}">
            <p class="text-2xl font-bold text-slate-900">{{ collect($roleStats)->sum() }}</p>
            <p class="text-sm text-slate-500">Semua User</p>
        </button>
        <button wire:click="$set('roleFilter', 'admin')" class="rounded-lg border bg-white p-4 text-left transition-all hover:border-emerald-500 {{ $roleFilter === 'admin' ? 'border-emerald-500 bg-emerald-50' : 'border-slate-200' }}">
            <p class="text-2xl font-bold text-red-600">{{ $roleStats['admin'] }}</p>
            <p class="text-sm text-slate-500">Admin</p>
        </button>
        <button wire:click="$set('roleFilter', 'staf')" class="rounded-lg border bg-white p-4 text-left transition-all hover:border-emerald-500 {{ $roleFilter === 'staf' ? 'border-emerald-500 bg-emerald-50' : 'border-slate-200' }}">
            <p class="text-2xl font-bold text-blue-600">{{ $roleStats['staf'] }}</p>
            <p class="text-sm text-slate-500">Staf</p>
        </button>
        <button wire:click="$set('roleFilter', 'peternak')" class="rounded-lg border bg-white p-4 text-left transition-all hover:border-emerald-500 {{ $roleFilter === 'peternak' ? 'border-emerald-500 bg-emerald-50' : 'border-slate-200' }}">
            <p class="text-2xl font-bold text-amber-600">{{ $roleStats['peternak'] }}</p>
            <p class="text-sm text-slate-500">Peternak</p>
        </button>
        <button wire:click="$set('roleFilter', 'pelanggan')" class="rounded-lg border bg-white p-4 text-left transition-all hover:border-emerald-500 {{ $roleFilter === 'pelanggan' ? 'border-emerald-500 bg-emerald-50' : 'border-slate-200' }}">
            <p class="text-2xl font-bold text-emerald-600">{{ $roleStats['pelanggan'] }}</p>
            <p class="text-sm text-slate-500">Pelanggan</p>
        </button>
    </div>

    {{-- Search --}}
    <x-ui.search.input
        wire:model.live.debounce.300ms="searchQuery"
        placeholder="Cari user..."
    />

    {{-- Users Table --}}
    <div class="rounded-xl border border-slate-200 bg-white overflow-hidden shadow-sm">
        <table class="w-full text-sm">
            <thead class="bg-slate-50">
                <tr>
                    <th class="px-4 py-3 text-left font-semibold text-slate-900">User</th>
                    <th class="px-4 py-3 text-left font-semibold text-slate-900">Email</th>
                    <th class="px-4 py-3 text-left font-semibold text-slate-900">Role</th>
                    <th class="px-4 py-3 text-left font-semibold text-slate-900">Bergabung</th>
                    <th class="px-4 py-3 text-center font-semibold text-slate-900">Aksi</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
                @forelse($users as $user)
                    <tr class="hover:bg-slate-50">
                        <td class="px-4 py-3">
                            <div class="flex items-center gap-3">
                                <div class="flex size-10 items-center justify-center rounded-full bg-emerald-100 text-sm font-bold text-emerald-700">
                                    {{ substr($user->name, 0, 2) }}
                                </div>
                                <span class="font-medium text-slate-900">{{ $user->name }}</span>
                            </div>
                        </td>
                        <td class="px-4 py-3 text-slate-600">{{ $user->email }}</td>
                        <td class="px-4 py-3">
                            @if($user->roles->isNotEmpty())
                                @foreach($user->roles as $role)
                                    <span class="inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-medium
                                        @if($role->name === 'admin') bg-red-100 text-red-700
                                        @elseif($role->name === 'staf') bg-blue-100 text-blue-700
                                        @elseif($role->name === 'peternak') bg-amber-100 text-amber-700
                                        @else bg-emerald-100 text-emerald-700 @endif">
                                        {{ ucfirst($role->name) }}
                                    </span>
                                @endforeach
                            @else
                                <span class="text-slate-400">-</span>
                            @endif
                        </td>
                        <td class="px-4 py-3 text-slate-500">{{ $user->created_at->format('d M Y') }}</td>
                        <td class="px-4 py-3 text-center">
                            <div class="flex items-center justify-center gap-1">
                                <button wire:click="openUserModal({{ $user->id }})" class="rounded-lg p-2 text-slate-400 hover:bg-slate-100 hover:text-blue-600">
                                    <i class="fa-solid fa-edit"></i>
                                </button>
                                @if(auth()->id() !== $user->id)
                                    <button wire:click="confirmDeleteUser({{ $user->id }})" class="rounded-lg p-2 text-slate-400 hover:bg-red-100 hover:text-red-600">
                                        <i class="fa-solid fa-trash"></i>
                                    </button>
                                @endif
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="px-6 py-12">
                            <x-ui.empty-state
                                icon="fa-users"
                                title="Belum ada user"
                                description="Tambahkan user baru untuk mulai mengelola"
                            >
                                <x-slot:action>
                                    <x-ui.btn.primary wire:click="openUserModal()">
                                        <i class="fa-solid fa-plus mr-1"></i> Tambah User
                                    </x-ui.btn.primary>
                                </x-slot:action>
                            </x-ui.empty-state>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    {{-- User Modal --}}
    @if($showUserModal)
    <div class="fixed inset-0 z-50 flex items-center justify-center bg-slate-900/50 p-4">
        <div class="w-full max-w-md rounded-xl bg-white shadow-xl overflow-hidden" wire:click.stop>
            <div class="flex items-center justify-between border-b border-slate-200 px-6 py-4">
                <h3 class="text-lg font-semibold">{{ $editingUserId ? 'Edit' : 'Tambah' }} User</h3>
                <button type="button" wire:click="closeUserModal" class="text-slate-400 hover:text-slate-600">
                    <i class="fa-solid fa-times"></i>
                </button>
            </div>
            <form wire:submit.prevent="saveUser" class="space-y-4 px-6 py-4">
                <x-ui.form.input
                    label="Nama *"
                    wire:model="userName"
                    placeholder="Nama lengkap"
                    :error="$errors->first('userName')"
                />
                <x-ui.form.input
                    label="Email *"
                    type="email"
                    wire:model="userEmail"
                    placeholder="email@example.com"
                    :error="$errors->first('userEmail')"
                />
                <x-ui.form.input
                    label="Password {{ $editingUserId ? '' : '*' }}"
                    type="password"
                    wire:model="userPassword"
                    placeholder="••••••••"
                    :error="$errors->first('userPassword')"
                    hint="Kosongkan jika tidak ingin mengubah password"
                />
                <x-ui.form.select
                    label="Role *"
                    wire:model="userRole"
                    :options="[
                        ['value' => 'admin', 'label' => 'Admin'],
                        ['value' => 'staf', 'label' => 'Staf'],
                        ['value' => 'peternak', 'label' => 'Peternak'],
                        ['value' => 'pelanggan', 'label' => 'Pelanggan'],
                    ]"
                    :error="$errors->first('userRole')"
                />
                <div class="flex gap-3 pt-4 border-t">
                    <x-ui.btn.cancel wire:click="closeUserModal" class="flex-1">
                        Batal
                    </x-ui.btn.cancel>
                    <x-ui.btn.primary type="submit" class="flex-1">
                        Simpan
                    </x-ui.btn.primary>
                </div>
            </form>
        </div>
    </div>
    @endif
</div>