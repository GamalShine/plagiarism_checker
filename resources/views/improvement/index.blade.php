@extends('layouts.user')

@section('title', 'Perbaiki File')
@section('page-title', 'Perbaikan File')
@section('page-subtitle', 'Analisis dan turunkan tingkat plagiarisme dengan parafrase cerdas')

@section('content')
<div class="grid grid-cols-1 lg:grid-cols-3 gap-5">

        {{-- Form panel --}}
        <div class="pc-card p-6" x-data="{ tab: 'history' }">
            <h3 class="pc-section-title mb-4">Mulai Perbaikan</h3>

            <div class="pc-tabs mb-6">
                <button type="button" @click="tab = 'history'" class="pc-tab" :class="{ 'active': tab === 'history' }">Dari Riwayat</button>
                <button type="button" @click="tab = 'upload'" class="pc-tab" :class="{ 'active': tab === 'upload' }">Upload Baru</button>
            </div>

            <form x-show="tab === 'history'" action="{{ route('user.improvement.analyze') }}" method="POST" class="space-y-4">
                @csrf
                <div>
                    <label class="pc-label">Pilih Hasil Cek Plagiasi</label>
                    @if($completedChecks->isEmpty())
                        <div class="p-4 rounded-xl text-sm text-center" style="background: var(--pc-bg-subtle); color: var(--pc-text-muted);">
                            Belum ada riwayat pengecekan selesai.
                        </div>
                    @else
                        <select name="plagiarism_check_id" class="pc-select" required>
                            <option value="">— Pilih Dokumen —</option>
                            @foreach($completedChecks as $check)
                                <option value="{{ $check->id }}">{{ $check->document->title }} ({{ $check->total_similarity }}%)</option>
                            @endforeach
                        </select>
                    @endif
                </div>

                <div>
                    <label class="pc-label">Mode Perbaikan</label>
                    <div class="grid grid-cols-2 gap-2">
                        <label class="cursor-pointer">
                            <input type="radio" name="mode" value="manual" class="peer sr-only" checked>
                            <div class="text-center p-3 text-sm font-semibold rounded-xl border-2 peer-checked:border-indigo-500 peer-checked:bg-indigo-50 dark:peer-checked:bg-indigo-900/20 peer-checked:text-indigo-700 dark:peer-checked:text-indigo-300 transition-all" style="border-color: var(--pc-border);">Manual</div>
                        </label>
                        <label class="cursor-pointer">
                            <input type="radio" name="mode" value="automatic" class="peer sr-only">
                            <div class="text-center p-3 text-sm font-semibold rounded-xl border-2 peer-checked:border-indigo-500 peer-checked:bg-indigo-50 dark:peer-checked:bg-indigo-900/20 peer-checked:text-indigo-700 dark:peer-checked:text-indigo-300 transition-all" style="border-color: var(--pc-border);">Otomatis</div>
                        </label>
                    </div>
                </div>

                <button type="submit" class="pc-btn-primary w-full" {{ $completedChecks->isEmpty() ? 'disabled' : '' }}>
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/></svg>
                    Mulai Analisis
                </button>
            </form>

            <form x-show="tab === 'upload'" x-cloak action="{{ route('user.improvement.analyze') }}" method="POST" enctype="multipart/form-data" class="space-y-4">
                @csrf
                <div>
                    <label class="pc-label">Upload Dokumen</label>
                    <input type="file" name="file" accept=".txt,.pdf,.docx" class="pc-input file:mr-3 file:py-1 file:px-3 file:rounded-lg file:border-0 file:text-xs file:font-semibold file:bg-indigo-50 file:text-indigo-700" required>
                    <p class="text-xs mt-1" style="color: var(--pc-text-subtle);">PDF, DOCX, TXT — maks. 10MB</p>
                </div>

                <div>
                    <label class="pc-label">Mode Perbaikan</label>
                    <div class="grid grid-cols-2 gap-2">
                        <label class="cursor-pointer">
                            <input type="radio" name="mode" value="manual" class="peer sr-only" checked>
                            <div class="text-center p-3 text-sm font-semibold rounded-xl border-2 peer-checked:border-indigo-500 peer-checked:bg-indigo-50 dark:peer-checked:bg-indigo-900/20 transition-all" style="border-color: var(--pc-border);">Manual</div>
                        </label>
                        <label class="cursor-pointer">
                            <input type="radio" name="mode" value="automatic" class="peer sr-only">
                            <div class="text-center p-3 text-sm font-semibold rounded-xl border-2 peer-checked:border-indigo-500 peer-checked:bg-indigo-900/20 transition-all" style="border-color: var(--pc-border);">Otomatis</div>
                        </label>
                    </div>
                </div>

                <button type="submit" class="pc-btn-primary w-full">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/></svg>
                    Mulai Analisis
                </button>
            </form>
        </div>

        {{-- History table --}}
        <div class="lg:col-span-2 pc-card overflow-hidden flex flex-col">
            <div class="pc-card-header">
                <h3 class="pc-section-title">Riwayat Perbaikan</h3>
            </div>

            <div class="pc-table-wrap flex-1">
                <table class="pc-table">
                    <thead>
                        <tr>
                            <th>Dokumen</th>
                            <th>Similarity</th>
                            <th>Status</th>
                            <th class="text-right">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($improvements as $item)
                            <tr>
                                <td>
                                    <div class="font-semibold max-w-[200px] truncate">{{ $item->document->title ?? 'Dokumen Dihapus' }}</div>
                                    <div class="text-xs mt-0.5" style="color: var(--pc-text-subtle);">{{ $item->created_at->format('d M Y') }} · {{ ucfirst($item->mode) }}</div>
                                </td>
                                <td>
                                    @if($item->original_similarity !== null && $item->improved_similarity !== null)
                                        <div class="flex items-center gap-1.5 text-sm font-semibold">
                                            <span class="text-red-500">{{ $item->original_similarity }}%</span>
                                            <svg class="w-3 h-3" style="color: var(--pc-text-subtle);" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"/></svg>
                                            <span class="text-emerald-500">{{ $item->improved_similarity }}%</span>
                                        </div>
                                    @else
                                        <span style="color: var(--pc-text-subtle);">—</span>
                                    @endif
                                </td>
                                <td>
                                    @if($item->status === 'completed')
                                        <span class="pc-badge-success">Selesai</span>
                                    @elseif($item->status === 'failed')
                                        <span class="pc-badge-danger">Gagal</span>
                                    @else
                                        <span class="pc-badge-info">Proses</span>
                                    @endif
                                </td>
                                <td class="text-right">
                                    <a href="{{ route('user.improvement.show', $item->id) }}" class="pc-btn-soft pc-btn-sm">Lihat</a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4">
                                    <div class="pc-empty py-8"><p class="text-sm">Belum ada riwayat perbaikan.</p></div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if($improvements->hasPages())
            <div class="p-4 border-t" style="border-color: var(--pc-border);">{{ $improvements->links() }}</div>
            @endif
        </div>
    </div>
@endsection
