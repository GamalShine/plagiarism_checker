@extends('layouts.admin')

@section('title', 'Kelola Pengguna')
@section('page-title', 'Kelola Pengguna')
@section('page-subtitle', 'Tambah, ubah, dan hapus akun pengguna terdaftar')

@section('content')
<div class="space-y-6">
    @if($errors->has('delete'))
        <div class="pc-card p-4 border border-red-200 bg-red-50 dark:bg-red-950/30">
            <p class="text-sm text-red-600 dark:text-red-400">{{ $errors->first('delete') }}</p>
        </div>
    @endif

    <div class="pc-card p-6">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 mb-4">
            <h3 class="text-base font-bold">Daftar Pengguna</h3>
            <a href="{{ route('admin.users.create') }}" class="pc-btn-primary pc-btn-sm inline-flex items-center gap-2 w-fit">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                Tambah Pengguna
            </a>
        </div>

        <form action="{{ route('admin.users.index') }}" method="GET" class="flex flex-col sm:flex-row gap-3 mb-6">
            <div class="flex-1">
                <input type="text" name="search" value="{{ $search }}"
                       class="pc-input w-full" placeholder="Cari nama atau email...">
            </div>
            <button type="submit" class="pc-btn-soft">Cari</button>
            @if($search !== '')
                <a href="{{ route('admin.users.index') }}" class="pc-btn-soft">Reset</a>
            @endif
        </form>

        <div class="pc-table-wrap">
            <table class="pc-table">
                <thead>
                    <tr>
                        <th>Nama</th>
                        <th>Email</th>
                        <th>Role</th>
                        <th>Verifikasi</th>
                        <th>Terdaftar</th>
                        <th class="text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($users as $user)
                        <tr>
                            <td>
                                <div class="font-semibold">{{ $user->name }}</div>
                                @if($user->id === auth()->id())
                                    <span class="text-xs" style="color: var(--pc-text-subtle);">(Anda)</span>
                                @endif
                            </td>
                            <td class="text-sm" style="color: var(--pc-text-muted);">{{ $user->email }}</td>
                            <td>
                                @if($user->isAdmin())
                                    <span class="pc-badge-success">Admin</span>
                                @else
                                    <span class="pc-badge-neutral">User</span>
                                @endif
                            </td>
                            <td>
                                @if($user->email_verified_at)
                                    <span class="pc-badge-success">Terverifikasi</span>
                                @else
                                    <span class="pc-badge-warning">Belum</span>
                                @endif
                            </td>
                            <td class="text-sm" style="color: var(--pc-text-muted);">{{ $user->created_at->format('d M Y') }}</td>
                            <td class="text-right">
                                <div class="inline-flex items-center gap-2">
                                    <a href="{{ route('admin.users.edit', $user) }}" class="pc-btn-soft pc-btn-sm">Edit</a>
                                    @unless($user->id === auth()->id())
                                        <form action="{{ route('admin.users.destroy', $user) }}" method="POST"
                                              onsubmit="return confirm('Hapus pengguna {{ $user->name }}?')">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="pc-btn-soft pc-btn-sm text-red-600">Hapus</button>
                                        </form>
                                    @endunless
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6">
                                <div class="pc-empty">
                                    <div class="pc-empty-icon">
                                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                                    </div>
                                    <p>Belum ada pengguna ditemukan.</p>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($users->hasPages())
            <div class="p-4 border-t" style="border-color: var(--pc-border);">{{ $users->links() }}</div>
        @endif
    </div>
</div>
@endsection
