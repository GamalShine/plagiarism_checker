@extends($layout ?? 'layouts.user')

@section('title', 'Buat Jurnal')
@section('page-title', 'Generator Jurnal')
@section('page-subtitle', 'Kelola dan buat jurnal akademik otomatis')

@php
    $routePrefix = str_starts_with(request()->route()?->getName() ?? '', 'admin.') ? 'admin' : 'user';
@endphp

@section('header-actions')
<a href="{{ route($routePrefix . '.journal.create') }}" class="pc-btn-primary pc-btn-sm hidden sm:inline-flex">
    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
    Buat Baru
</a>
@endsection

@section('content')

    <div class="sm:hidden">
        <a href="{{ route($routePrefix . '.journal.create') }}" class="pc-btn-primary w-full">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
            Buat Jurnal Baru
        </a>
    </div>

    <div class="pc-card overflow-hidden">
        <div class="pc-table-wrap">
            <table class="pc-table">
                <thead>
                    <tr>
                        <th>Judul Jurnal</th>
                        <th>Penulis</th>
                        <th>Template</th>
                        <th>Tanggal</th>
                        <th class="text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($journals as $journal)
                        <tr>
                            <td>
                                <div class="font-semibold max-w-xs truncate" title="{{ $journal->title }}">{{ $journal->title }}</div>
                            </td>
                            <td class="text-sm" style="color: var(--pc-text-muted);">{{ $journal->author }}</td>
                            <td><span class="pc-badge-neutral">{{ $journal->template_name }}</span></td>
                            <td class="text-sm" style="color: var(--pc-text-muted);">{{ $journal->created_at->format('d M Y') }}</td>
                            <td class="text-right">
                                <a href="{{ route($routePrefix . '.journal.show', $journal->id) }}" class="pc-btn-soft pc-btn-sm">Detail</a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5">
                                <div class="pc-empty">
                                    <div class="pc-empty-icon">
                                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/></svg>
                                    </div>
                                    <p>Belum ada jurnal yang dibuat.</p>
                                    <a href="{{ route($routePrefix . '.journal.create') }}" class="pc-link text-sm mt-2">Buat jurnal pertama</a>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($journals->hasPages())
        <div class="p-4 border-t" style="border-color: var(--pc-border);">{{ $journals->links() }}</div>
        @endif
    </div>
@endsection
