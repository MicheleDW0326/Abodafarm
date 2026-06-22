<?php

namespace App\Livewire\Admin;

use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('layouts.main')]
#[Title('Manajemen Pengguna')]
class StaffManagement extends Component
{
    public string $searchQuery = '';
    public string $roleFilter = 'all';

    public ?int $editingUserId = null;
    public ?int $pendingDeleteUserId = null;
    public string $userName = '';
    public string $userEmail = '';
    public string $userPassword = '';
    public string $userRole = 'pelanggan';
    public bool $showUserModal = false;

    public function mount()
    {
        if (request()->has('role')) {
            $this->roleFilter = request()->query('role');
        }
    }

    public function getUsersProperty()
    {
        return User::query()
            ->when($this->searchQuery, function ($query) {
                $query->where('name', 'like', '%' . $this->searchQuery . '%')
                    ->orWhere('email', 'like', '%' . $this->searchQuery . '%');
            })
            ->when($this->roleFilter !== 'all', function ($query) {
                $query->role($this->roleFilter);
            })
            ->with('roles')
            ->orderBy('created_at', 'desc')
            ->get();
    }

    public function getRoleStatsProperty()
    {
        $roles = ['admin', 'staf', 'peternak', 'pelanggan'];
        $stats = [];

        foreach ($roles as $role) {
            $stats[$role] = User::role($role)->count();
        }

        return $stats;
    }

    public function openUserModal(?int $id = null)
    {
        if ($id) {
            $user = User::with('roles')->findOrFail($id);
            $this->editingUserId = $user->id;
            $this->userName = $user->name;
            $this->userEmail = $user->email;
            $this->userRole = $user->roles->first()?->name ?? 'pelanggan';
            $this->userPassword = '';
        } else {
            $this->resetUserForm();
        }
        $this->showUserModal = true;
    }

    public function closeUserModal()
    {
        $this->showUserModal = false;
        $this->resetUserForm();
    }

    public function saveUser()
    {
        $rules = [
            'userName' => 'required|string|max:255',
            'userEmail' => 'required|email|unique:users,email,' . ($this->editingUserId ?? 'NULL') . ',id',
            'userRole' => 'required|in:admin,staf,peternak,pelanggan',
        ];

        if (!$this->editingUserId) {
            $rules['userPassword'] = 'required|string|min:6';
        }

        $this->validate($rules);

        $data = [
            'name' => $this->userName,
            'email' => $this->userEmail,
        ];

        if ($this->userPassword) {
            $data['password'] = Hash::make($this->userPassword);
        }

        if ($this->editingUserId) {
            $user = User::findOrFail($this->editingUserId);
            $user->update($data);
            $user->syncRoles([$this->userRole]);
        } else {
            $user = User::create($data);
            $user->assignRole($this->userRole);
        }

        $this->dispatch('showAlert', ['type' => 'success', 'title' => 'Berhasil!', 'message' => 'User berhasil disimpan!']);
        $this->closeUserModal();
    }

    public function deleteUser(int $id)
    {
        if (auth()->id() === $id) {
            $this->dispatch('showAlert', ['type' => 'error', 'title' => 'Gagal!', 'message' => 'Tidak dapat menghapus akun sendiri!']);
            return;
        }

        User::findOrFail($id)->delete();
        $this->dispatch('showAlert', ['type' => 'success', 'title' => 'Berhasil!', 'message' => 'User berhasil dihapus!']);
    }

    public function confirmDeleteUser(int $id)
    {
        if (auth()->id() === $id) {
            $this->dispatch('showAlert', ['type' => 'error', 'title' => 'Gagal!', 'message' => 'Tidak dapat menghapus akun sendiri!']);
            return;
        }

        $this->pendingDeleteUserId = $id;
        $this->dispatch('showConfirm', [
            'title' => 'Konfirmasi Hapus',
            'message' => 'Apakah Anda yakin ingin menghapus user ini?',
            'confirmText' => 'Ya, Hapus',
            'confirmAction' => 'deleteUserConfirmed'
        ]);
    }

    public function deleteUserConfirmed()
    {
        if (!$this->pendingDeleteUserId) {
            return;
        }

        if (auth()->id() === $this->pendingDeleteUserId) {
            $this->dispatch('showAlert', ['type' => 'error', 'title' => 'Gagal!', 'message' => 'Tidak dapat menghapus akun sendiri!']);
            $this->pendingDeleteUserId = null;
            return;
        }

        User::findOrFail($this->pendingDeleteUserId)->delete();
        $this->dispatch('showAlert', ['type' => 'success', 'title' => 'Berhasil!', 'message' => 'User berhasil dihapus!']);
        $this->pendingDeleteUserId = null;
    }

    private function resetUserForm()
    {
        $this->editingUserId = null;
        $this->userName = '';
        $this->userEmail = '';
        $this->userPassword = '';
        $this->userRole = 'pelanggan';
    }

    public function render()
    {
        return view('livewire.admin.staff-management', [
            'users' => $this->users,
            'roleStats' => $this->roleStats,
        ]);
    }
}
