@extends('layouts.admin')

@section('title', 'Laporan')
@section('page-title', 'Laporan')
@section('page-subtitle', 'Kendala, bug, error, dan pesan yang dikirim melalui form Pusat Bantuan')

@section('content')
<div class="space-y-5">
    <div class="pc-card p-5 sm:p-6">
        <form action="{{ route('admin.reports.index') }}" method="GET" class="flex flex-col gap-3 sm:flex-row">
            <input type="hidden" name="channel" value="{{ $channel }}">
            <input type="search" name="search" value="{{ $search }}" class="pc-input w-full sm:flex-1"
                placeholder="Cari nama, email, kategori, atau isi laporan...">
            <div class="flex gap-2">
                <button type="submit" class="pc-btn-primary">Cari</button>
                @if($search !== '')
                    <a href="{{ route('admin.reports.index') }}" class="pc-btn-soft">Reset</a>
                @endif
            </div>
        </form>

        <nav class="mt-4 grid grid-cols-3 gap-2 sm:gap-3" aria-label="Kategori laporan">
            @foreach($channels as $key => $label)
                <a href="{{ route('admin.reports.index', array_filter(['channel' => $key, 'search' => $search !== '' ? $search : null])) }}"
                    @class([
                        'inline-flex min-w-0 w-full items-center justify-center gap-1.5 rounded-xl border px-2 py-3 text-center text-sm font-semibold transition sm:gap-2 sm:px-4',
                        'border-blue-600 bg-blue-600 text-white' => $channel === $key,
                        'border-slate-200 bg-white text-slate-600 hover:border-blue-300 hover:text-blue-600' => $channel !== $key,
                    ])>
                    <span class="truncate">{{ $label }}</span>
                    <span @class([
                        'shrink-0 rounded-full px-2 py-0.5 text-xs',
                        'bg-white/20 text-white' => $channel === $key,
                        'bg-slate-100 text-slate-600' => $channel !== $key,
                    ])>{{ $counts->get($key, 0) }}</span>
                </a>
            @endforeach
        </nav>
    </div>

    @forelse($reports as $report)
        <article class="pc-card overflow-hidden">
            <header class="flex flex-col gap-3 border-b p-4 sm:flex-row sm:items-start sm:justify-between sm:p-5"
                style="border-color: var(--pc-border);">
                <div class="min-w-0">
                    <h2 class="font-bold">{{ $report->category }}</h2>
                    <p class="mt-1 text-sm" style="color: var(--pc-text-muted);">
                        {{ $report->name }} ·
                        <a class="pc-link" href="mailto:{{ $report->email }}">{{ $report->email }}</a>
                    </p>
                </div>
                <div class="flex shrink-0 flex-wrap items-center gap-2">
                    <span class="{{ $report->channel === 'whatsapp' ? 'pc-badge-success' : 'pc-badge-neutral' }}">
                        {{ $channels[$report->channel] ?? ucfirst($report->channel) }}
                    </span>
                    <time class="text-xs" style="color: var(--pc-text-subtle);" datetime="{{ $report->created_at->toIso8601String() }}">
                        {{ $report->created_at->format('d M Y, H:i') }}
                    </time>
                </div>
            </header>
            <div class="p-4 sm:p-5">
                <p class="whitespace-pre-wrap break-words text-sm leading-6" style="color: var(--pc-text);">{{ $report->message }}</p>
            </div>
        </article>
    @empty
        <div class="pc-card p-10 text-center">
            <div class="mx-auto flex h-12 w-12 items-center justify-center rounded-2xl bg-slate-100 text-slate-500">
                <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 8h10M7 12h10m-10 4h6m-9 5 2.5-3H19a2 2 0 002-2V5a2 2 0 00-2-2H5a2 2 0 00-2 2v13z" />
                </svg>
            </div>
            <h2 class="mt-3 font-bold">Belum ada laporan</h2>
            <p class="mt-1 text-sm" style="color: var(--pc-text-muted);">Belum ada laporan kategori {{ $channels[$channel] }}.</p>
        </div>
    @endforelse

    @if($reports->hasPages())
        <div>{{ $reports->links() }}</div>
    @endif
</div>
@endsection