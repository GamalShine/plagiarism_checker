@extends('layouts.admin')

@section('title', 'Kelola Link')
@section('page-title', 'Link Sekali Pakai')
@section('page-subtitle', 'Buat dan kelola link cek plagiasi tanpa login')

@section('content')
<div class="space-y-6">
    @if(session('new_link_url'))
        <div class="pc-card p-4 border border-emerald-200 bg-emerald-50 dark:bg-emerald-950/30" x-data="{ copied: false }">
            <p class="text-sm font-semibold text-emerald-700 dark:text-emerald-300 mb-2">Link baru siap dibagikan:</p>
            <div class="flex flex-col sm:flex-row gap-2">
                <input type="text" readonly value="{{ session('new_link_url') }}"
                       class="pc-input flex-1 text-sm" id="new-link-url">
                <button type="button"
                        class="pc-btn-primary pc-btn-sm"
                        @click="navigator.clipboard.writeText(document.getElementById('new-link-url').value); copied = true; setTimeout(() => copied = false, 1500)">
                    <span x-text="copied ? 'Tersalin!' : 'Salin Link'"></span>
                </button>
            </div>
            <p class="text-xs mt-2" style="color: var(--pc-text-muted);">Link hanya bisa dipakai 1 kali. Setelah dipakai, otomatis tidak tersedia lagi.</p>
        </div>
    @endif

    <div class="pc-card p-6">
        <h3 class="text-base font-bold mb-4">Buat Link Baru</h3>
        <form action="{{ route('admin.links.store') }}" method="POST" class="flex flex-col sm:flex-row gap-3">
            @csrf
            <div class="flex-1">
                <input type="text" name="title" value="{{ old('title') }}"
                       class="pc-input w-full" placeholder="Label opsional (mis. Mahasiswa A / Sidang Budi)">
                @error('title') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
            </div>
            <button type="submit" class="pc-btn-primary">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                Buat Link
            </button>
        </form>
    </div>

    <div class="pc-card overflow-hidden">
        <div class="pc-table-wrap">
            <table class="pc-table">
                <thead>
                    <tr>
                        <th>Label</th>
                        <th>Link</th>
                        <th>Status</th>
                        <th>Dibuat</th>
                        <th>Dipakai</th>
                        <th class="text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($links as $link)
                        <tr>
                            <td>
                                <div class="font-semibold">{{ $link->title ?: 'Tanpa label' }}</div>
                                <div class="text-xs" style="color: var(--pc-text-subtle);">oleh {{ $link->creator?->name }}</div>
                            </td>
                            <td>
                                <div class="flex items-center gap-2 max-w-xs">
                                    <code class="text-xs truncate" title="{{ $link->public_url }}">{{ $link->public_url }}</code>
                                    <button type="button" class="pc-btn-soft pc-btn-sm"
                                            onclick="navigator.clipboard.writeText(@js($link->public_url))">
                                        Salin
                                    </button>
                                </div>
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
                            <td class="text-sm" style="color: var(--pc-text-muted);">{{ $link->created_at->format('d M Y H:i') }}</td>
                            <td class="text-sm" style="color: var(--pc-text-muted);">
                                @if($link->used_at)
                                    {{ $link->used_at->format('d M Y H:i') }}
                                    @if($link->used_ip)
                                        <div class="text-xs">IP: {{ $link->used_ip }}</div>
                                    @endif
                                @else
                                    —
                                @endif
                            </td>
                            <td class="text-right">
                                <div class="inline-flex items-center gap-2">
                                    @unless($link->is_used || $link->used_at)
                                        <form action="{{ route('admin.links.update', $link) }}" method="POST">
                                            @csrf
                                            @method('PUT')
                                            <input type="hidden" name="title" value="{{ $link->title }}">
                                            <input type="hidden" name="is_active" value="{{ $link->is_active ? 0 : 1 }}">
                                            <button type="submit" class="pc-btn-soft pc-btn-sm">
                                                {{ $link->is_active ? 'Nonaktifkan' : 'Aktifkan' }}
                                            </button>
                                        </form>
                                    @endunless
                                    <form action="{{ route('admin.links.destroy', $link) }}" method="POST"
                                          onsubmit="return confirm('Hapus link ini?')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="pc-btn-soft pc-btn-sm text-red-600">Hapus</button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6">
                                <div class="pc-empty">
                                    <div class="pc-empty-icon">
                                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.828 10.172a4 4 0 00-5.656 0l-4 4a4 4 0 105.656 5.656l1.102-1.101m-.758-4.899a4 4 0 005.656 0l4-4a4 4 0 00-5.656-5.656l-1.1 1.1"/></svg>
                                    </div>
                                    <p>Belum ada link sekali pakai.</p>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($links->hasPages())
            <div class="p-4 border-t" style="border-color: var(--pc-border);">{{ $links->links() }}</div>
        @endif
    </div>
</div>
@endsection
