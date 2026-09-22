@extends('layouts.admin')

@section('title', 'Dashboard Admin')
@section('page-title', 'Dashboard Admin')
@section('page-subtitle', 'Ringkasan sistem dan manajemen')

@section('content')
<div class="space-y-6">
    <div class="grid grid-cols-2 gap-4 xl:grid-cols-4">
        <div class="pc-card p-5">
            <p class="text-sm" style="color: var(--pc-text-muted);">Total Pengguna</p>
            <p class="text-3xl font-bold mt-1">{{ $stats['users'] }}</p>
        </div>
        <div class="pc-card p-5">
            <p class="text-sm" style="color: var(--pc-text-muted);">Admin</p>
            <p class="text-3xl font-bold mt-1">{{ $stats['admins'] }}</p>
        </div>
        <div class="pc-card p-5">
            <p class="text-sm" style="color: var(--pc-text-muted);">Link Sekali Pakai</p>
            <p class="text-3xl font-bold mt-1">{{ $stats['links'] }}</p>
        </div>
        <div class="pc-card p-5">
            <p class="text-sm" style="color: var(--pc-text-muted);">Cek Selesai</p>
            <p class="text-3xl font-bold mt-1">{{ $stats['checks'] }}</p>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <div class="pc-card overflow-hidden">
            <div class="p-5 border-b flex items-center justify-between" style="border-color: var(--pc-border);">
                <h3 class="font-bold">Pengguna Terbaru</h3>
                <a href="{{ route('admin.users.index') }}" class="pc-link text-xs">Semua</a>
            </div>
            <div class="pc-table-wrap">
                <table class="pc-table">
                    <thead>
                        <tr>
                            <th>Nama</th>
                            <th>Role</th>
                            <th>Terdaftar</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($recentUsers as $user)
                            <tr>
                                <td>
                                    <div class="font-semibold">{{ $user->name }}</div>
                                    <div class="text-xs" style="color: var(--pc-text-subtle);">{{ $user->email }}</div>
                                </td>
                                <td>
                                    @if($user->isAdmin())
                                        <span class="pc-badge-success">Admin</span>
                                    @else
                                        <span class="pc-badge-neutral">User</span>
                                    @endif
                                </td>
                                <td class="text-sm" style="color: var(--pc-text-muted);">{{ $user->created_at->format('d M Y') }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="3" class="text-sm" style="color: var(--pc-text-muted);">Belum ada pengguna.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <div class="pc-card overflow-hidden">
            <div class="p-5 border-b flex items-center justify-between" style="border-color: var(--pc-border);">
                <h3 class="font-bold">Link Terbaru</h3>
                <a href="{{ route('admin.links.index') }}" class="pc-link text-xs">Semua</a>
            </div>
            <div class="pc-table-wrap">
                <table class="pc-table">
                    <thead>
                        <tr>
                            <th>Label</th>
                            <th>Status</th>
                            <th>Dibuat</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($recentLinks as $link)
                            <tr>
                                <td>
                                    <div class="font-semibold">{{ $link->title ?: 'Tanpa label' }}</div>
                                    <div class="text-xs" style="color: var(--pc-text-subtle);">oleh {{ $link->creator?->name }}</div>
                                </td>
                                <td>
                                    @if($link->status_color === 'success')
                                        <span class="pc-badge-success">{{ $link->status_label }}</span>
                                    @elseif($link->status_color === 'warning')
                                        <span class="pc-badge-warning">{{ $link->status_label }}</span>
                                    @else
                                        <span class="pc-badge-neutral">{{ $link->status_label }}</span>
                                    @endif
                                </td>
                                <td class="text-sm" style="color: var(--pc-text-muted);">{{ $link->created_at->format('d M Y') }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="3" class="text-sm" style="color: var(--pc-text-muted);">Belum ada link.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection
