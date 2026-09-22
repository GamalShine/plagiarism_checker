@extends('layouts.user')

@section('title', 'Dashboard')
@section('page-title', 'Dashboard')
@section('page-subtitle', 'Ringkasan aktivitas dan statistik Anda')

@section('content')

    {{-- Stats --}}
    <div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-4 gap-4 lg:gap-5">
        <div class="pc-stat-card">
            <div class="flex items-start justify-between">
                <div class="pc-stat-icon">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                </div>
                <span class="pc-badge-neutral">Total</span>
            </div>
            <p class="pc-stat-value">{{ number_format($totalChecks) }}</p>
            <p class="pc-stat-label">Cek Plagiasi Selesai</p>
        </div>

        <div class="pc-stat-card">
            <div class="flex items-start justify-between">
                <div class="pc-stat-icon" style="background: var(--pc-accent-soft); color: var(--pc-accent);">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"/></svg>
                </div>
                <span class="pc-badge-neutral">Rata-rata</span>
            </div>
            <p class="pc-stat-value" style="color: var(--pc-accent);">{{ number_format($avgSimilarity, 1) }}%</p>
            <p class="pc-stat-label">Overall Similarity</p>
        </div>

        <div class="pc-stat-card">
            <div class="flex items-start justify-between">
                <div class="pc-stat-icon">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/></svg>
                </div>
                <span class="pc-badge-neutral">Total</span>
            </div>
            <p class="pc-stat-value">{{ number_format($totalJournals) }}</p>
            <p class="pc-stat-label">Jurnal Dibuat</p>
        </div>

        <div class="pc-stat-card">
            <div class="flex items-start justify-between">
                <div class="pc-stat-icon">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                </div>
                <span class="pc-badge-neutral">Total</span>
            </div>
            <p class="pc-stat-value">{{ number_format($totalImprovements) }}</p>
            <p class="pc-stat-label">Perbaikan File</p>
        </div>
    </div>

    {{-- Chart + Activity --}}
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-5">
        <div class="lg:col-span-2 pc-card p-6 flex flex-col h-[380px]">
            <h3 class="pc-section-title mb-1">Tren Similarity</h3>
            <p class="text-xs mb-5" style="color: var(--pc-text-muted);">6 bulan terakhir</p>
            <div class="flex-1 relative min-h-0">
                <canvas id="trendChart"></canvas>
            </div>
        </div>

        <div class="pc-card p-6 flex flex-col h-[380px]">
            <div class="flex items-center justify-between mb-5">
                <h3 class="pc-section-title">Aktivitas Terakhir</h3>
                <a href="{{ route('user.history.index') }}" class="pc-link text-xs">Semua</a>
            </div>
            <div class="flex-1 overflow-y-auto pc-scrollbar space-y-4 pr-1">
                @forelse($recentHistory as $history)
                    <div class="flex items-start gap-3">
                        <div class="w-9 h-9 rounded-xl flex items-center justify-center shrink-0 text-sm border" style="background-color: {{ $history->color }}15; color: {{ $history->color }}; border-color: {{ $history->color }}30">
                            {{ $history->icon }}
                        </div>
                        <div class="min-w-0">
                            <p class="text-sm font-medium line-clamp-2 leading-snug">{{ $history->description }}</p>
                            <p class="text-xs mt-1" style="color: var(--pc-text-subtle);">{{ $history->created_at->diffForHumans() }}</p>
                        </div>
                    </div>
                @empty
                    <div class="pc-empty py-8">
                        <p class="text-sm">Belum ada aktivitas.</p>
                    </div>
                @endforelse
            </div>
        </div>
    </div>

    {{-- Recent checks table --}}
    <div class="pc-card overflow-hidden">
        <div class="pc-card-header">
            <div>
                <h3 class="pc-section-title">Pengecekan Terakhir</h3>
                <p class="text-xs mt-0.5" style="color: var(--pc-text-muted);">Riwayat cek plagiasi Anda</p>
            </div>
            <a href="{{ route('user.plagiarism.index') }}" class="pc-btn-secondary pc-btn-sm">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                Cek Baru
            </a>
        </div>
        <div class="pc-table-wrap">
            <table class="pc-table">
                <thead>
                    <tr>
                        <th>Dokumen</th>
                        <th>Tanggal</th>
                        <th>Similarity</th>
                        <th>Status</th>
                        <th class="text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($recentChecks as $check)
                        <tr>
                            <td>
                                <div class="font-semibold">{{ $check->document->title }}</div>
                                <div class="text-xs mt-0.5" style="color: var(--pc-text-muted);">{{ $check->document->original_filename }}</div>
                            </td>
                            <td class="text-sm" style="color: var(--pc-text-muted);">{{ $check->created_at->format('d M Y, H:i') }}</td>
                            <td>
                                @if($check->status === 'completed')
                                    <div class="flex items-center gap-2">
                                        <div class="pc-progress max-w-[72px]">
                                            <div class="pc-progress-bar" style="width: {{ $check->total_similarity }}%; background-color: {{ $check->similarity_color }}"></div>
                                        </div>
                                        <span class="text-sm font-semibold" style="color: {{ $check->similarity_color }}">{{ $check->total_similarity }}%</span>
                                    </div>
                                @else
                                    <span class="text-sm" style="color: var(--pc-text-subtle);">—</span>
                                @endif
                            </td>
                            <td>
                                @if($check->status === 'completed')
                                    <span class="pc-badge-success">Selesai</span>
                                @elseif($check->status === 'failed')
                                    <span class="pc-badge-danger">Gagal</span>
                                @else
                                    <span class="pc-badge-info">Proses</span>
                                @endif
                            </td>
                            <td class="text-right">
                                @if($check->status === 'completed')
                                    <a href="{{ route('user.plagiarism.result', $check->id) }}" class="pc-btn-soft pc-btn-sm">Lihat</a>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5">
                                <div class="pc-empty">
                                    <div class="pc-empty-icon">
                                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                                    </div>
                                    <p class="text-sm">Belum ada pengecekan.</p>
                                    <a href="{{ route('user.plagiarism.index') }}" class="pc-link text-sm mt-2">Mulai cek plagiasi</a>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    const ctx = document.getElementById('trendChart').getContext('2d');
    const isDark = document.documentElement.classList.contains('dark');
    const textColor = isDark ? '#94a3b8' : '#64748b';
    const gridColor = isDark ? '#1f2937' : '#e2e8f0';

    const gradient = ctx.createLinearGradient(0, 0, 0, 300);
    gradient.addColorStop(0, 'rgba(79, 70, 229, 0.25)');
    gradient.addColorStop(1, 'rgba(79, 70, 229, 0)');

    new Chart(ctx, {
        type: 'line',
        data: {
            labels: {!! json_encode($months) !!},
            datasets: [{
                label: 'Rata-rata Similarity (%)',
                data: {!! json_encode($similarities) !!},
                borderColor: '#4f46e5',
                backgroundColor: gradient,
                borderWidth: 2.5,
                pointBackgroundColor: isDark ? '#1f2937' : '#fff',
                pointBorderColor: '#4f46e5',
                pointBorderWidth: 2,
                pointRadius: 4,
                pointHoverRadius: 6,
                fill: true,
                tension: 0.35
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: { display: false },
                tooltip: {
                    backgroundColor: isDark ? '#111827' : '#fff',
                    titleColor: isDark ? '#f1f5f9' : '#0f172a',
                    bodyColor: isDark ? '#94a3b8' : '#64748b',
                    borderColor: isDark ? '#374151' : '#e2e8f0',
                    borderWidth: 1,
                    padding: 12,
                    displayColors: false,
                    callbacks: { label: ctx => ctx.parsed.y + '% Similarity' }
                }
            },
            scales: {
                y: {
                    beginAtZero: true, max: 100,
                    grid: { color: gridColor, drawBorder: false },
                    ticks: { color: textColor, padding: 8, callback: v => v + '%' }
                },
                x: {
                    grid: { display: false },
                    ticks: { color: textColor, padding: 8 }
                }
            },
            interaction: { intersect: false, mode: 'index' }
        }
    });
});
</script>
@endpush
