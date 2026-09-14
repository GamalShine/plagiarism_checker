@extends($layout ?? 'layouts.user')

@section('title', 'Riwayat Aktivitas')
@section('page-title', 'History')
@section('page-subtitle', 'Log lengkap semua aktivitas Anda')

@php
    $routePrefix = str_starts_with(request()->route()?->getName() ?? '', 'admin.') ? 'admin' : 'user';
@endphp

@section('content')
    <div class="pc-card overflow-hidden">
        <div class="pc-card-header flex justify-between items-center">
            <div>
                <h3 class="pc-section-title">Semua Aktivitas</h3>
                <p class="text-xs mt-0.5" style="color: var(--pc-text-muted);">{{ $histories->total() }} entri</p>
            </div>
            <div id="deleteActionContainer" class="hidden items-center">
                <button type="button" id="bulkDeleteBtn" class="px-3 py-1.5 bg-red-500 text-white rounded text-sm font-medium hover:bg-red-600 transition-colors" style="background-color: #ef4444;">Hapus Terpilih</button>
            </div>
        </div>

        <form action="{{ route($routePrefix . '.history.destroyBulk') }}" method="POST" id="historyBulkForm">
            @csrf
            @method('DELETE')
        <div class="pc-table-wrap">
            <table class="pc-table" style="width: 100%;">
                <thead>
                    <tr>
                        <th class="w-12 text-center" style="width: 3rem;">No</th>
                        <th>Aktivitas</th>
                        <th>Detail</th>
                        <th class="text-right">Waktu</th>
                        <th class="w-12 text-center" style="width: 3rem;">
                            <input type="checkbox" id="selectAll" class="rounded border-gray-300">
                        </th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($histories as $history)
                        <tr>
                            <td class="text-center text-sm font-semibold" style="color: var(--pc-text-muted);">
                                {{ $histories->firstItem() + $loop->index }}
                            </td>
                            <td>
                                <div class="font-medium max-w-md line-clamp-2">{{ $history->description }}</div>
                                <span class="text-[10px] font-bold uppercase tracking-wider mt-1 inline-block" style="color: {{ $history->color }}">{{ $history->action_type }}</span>
                            </td>
                            <td class="text-sm">
                                @if($history->activity_type === 'plagiarism_check' && isset($history->metadata['check_id']))
                                    <a href="{{ route($routePrefix . '.plagiarism.result', $history->metadata['check_id']) }}" class="pc-link">Lihat Hasil Plagiarisme</a>
                                @elseif($history->activity_type === 'journal_generate' && isset($history->metadata['journal_id']))
                                    <a href="{{ route($routePrefix . '.journal.show', $history->metadata['journal_id']) }}" class="pc-link">Lihat Jurnal</a>
                                @elseif($history->activity_type === 'improvement' && isset($history->metadata['improvement_id']))
                                    <a href="{{ route($routePrefix . '.improvement.show', $history->metadata['improvement_id']) }}" class="pc-link" style="color: var(--pc-accent);">Lihat Perbaikan</a>
                                @else
                                    <span style="color: var(--pc-text-subtle);">—</span>
                                @endif
                            </td>
                            <td class="text-right">
                                <div class="text-sm font-medium">{{ $history->created_at->format('d M Y') }}</div>
                                <div class="text-xs" style="color: var(--pc-text-subtle);">{{ $history->created_at->format('H:i') }}</div>
                            </td>
                            <td class="text-center">
                                <input type="checkbox" name="ids[]" value="{{ $history->id }}" class="history-checkbox rounded border-gray-300">
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5">
                                <div class="pc-empty">
                                    <div class="pc-empty-icon">
                                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                    </div>
                                    <p>Belum ada riwayat aktivitas.</p>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($histories->hasPages())
        <div class="p-4 border-t" style="border-color: var(--pc-border);">
            {{ $histories->links() }}
        </div>
        @endif
        </form>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const selectAll = document.getElementById('selectAll');
            const checkboxes = document.querySelectorAll('.history-checkbox');
            const deleteActionContainer = document.getElementById('deleteActionContainer');
            const bulkDeleteBtn = document.getElementById('bulkDeleteBtn');
            const bulkForm = document.getElementById('historyBulkForm');

            function updateDeleteButton() {
                const checkedCount = document.querySelectorAll('.history-checkbox:checked').length;
                if (checkedCount > 0) {
                    deleteActionContainer.classList.remove('hidden');
                    deleteActionContainer.classList.add('flex');
                } else {
                    deleteActionContainer.classList.add('hidden');
                    deleteActionContainer.classList.remove('flex');
                }
            }

            if (bulkDeleteBtn && bulkForm) {
                bulkDeleteBtn.addEventListener('click', function () {
                    const checked = document.querySelectorAll('.history-checkbox:checked').length;
                    if (!checked) {
                        Swal.fire({
                            icon: 'info',
                            title: 'Belum ada yang dipilih',
                            text: 'Pilih minimal satu riwayat untuk dihapus.',
                            confirmButtonText: 'OK'
                        });
                        return;
                    }

                    Swal.fire({
                        icon: 'warning',
                        title: 'Hapus riwayat terpilih?',
                        text: 'Tindakan ini akan menghapus entri yang dipilih secara permanen.',
                        showCancelButton: true,
                        confirmButtonColor: '#dc2626',
                        cancelButtonColor: '#64748b',
                        confirmButtonText: 'Ya, hapus',
                        cancelButtonText: 'Batal'
                    }).then((result) => {
                        if (result.isConfirmed) {
                            bulkForm.submit();
                        }
                    });
                });
            }

            if (selectAll) {
                selectAll.addEventListener('change', function() {
                    checkboxes.forEach(cb => cb.checked = selectAll.checked);
                    updateDeleteButton();
                });
            }

            checkboxes.forEach(cb => {
                cb.addEventListener('change', updateDeleteButton);
            });
        });
    </script>
@endsection
