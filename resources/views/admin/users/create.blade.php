@extends('layouts.admin')

@section('title', 'Tambah Pengguna')
@section('page-title', 'Tambah Pengguna')
@section('page-subtitle', 'Buat akun pengguna baru')

@section('content')
<div class="max-w-xl">
    <div class="pc-card p-6">
        <form action="{{ route('admin.users.store') }}" method="POST" class="space-y-4">
            @csrf

            <div>
                <label for="name" class="block text-sm font-semibold mb-1">Nama</label>
                <input type="text" name="name" id="name" value="{{ old('name') }}"
                       class="pc-input w-full" required autofocus>
                @error('name') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
            </div>

            <div>
                <label for="email" class="block text-sm font-semibold mb-1">Email</label>
                <input type="email" name="email" id="email" value="{{ old('email') }}"
                       class="pc-input w-full" required>
                @error('email') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
            </div>

            <div>
                <label for="password" class="block text-sm font-semibold mb-1">Password</label>
                <input type="password" name="password" id="password"
                       class="pc-input w-full" required>
                @error('password') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
            </div>

            <div>
                <label for="password_confirmation" class="block text-sm font-semibold mb-1">Konfirmasi Password</label>
                <input type="password" name="password_confirmation" id="password_confirmation"
                       class="pc-input w-full" required>
            </div>

            <div>
                <label for="role" class="block text-sm font-semibold mb-1">Role</label>
                <select name="role" id="role" class="pc-input w-full" required>
                    <option value="user" @selected(old('role', 'user') === 'user')>User</option>
                    <option value="admin" @selected(old('role') === 'admin')>Admin</option>
                </select>
                @error('role') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
            </div>

            <div class="flex items-center gap-3 pt-2">
                <button type="submit" class="pc-btn-primary">Simpan</button>
                <a href="{{ route('admin.users.index') }}" class="pc-btn-soft">Batal</a>
            </div>
        </form>
    </div>
</div>
@endsection
