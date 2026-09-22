@extends('layouts.user')

@section('title', 'Riwayat Aktivitas')
@section('page-title', 'History')
@section('page-subtitle', 'Log lengkap semua aktivitas Anda')

@section('content')
    <div class="pc-card overflow-hidden">
        <div class="pc-card-header flex justify-between items-center">
            <div>
                <h3 class="pc-section-title">Semua Aktivitas</h3>
                <p class="text-xs mt-0.5" style="color: var(--pc-text-muted);">{{ $histories->total() }} entri</p>
            </div>
            <div id="deleteActionContainer" class="hidden items-center space-x-3" style="gap: 1rem;">
                <label class="flex items-center text-sm text-gray-600" style="color: var(--pc-text-subtle);">
                    <input type="checkbox" name="delete_backend" value="1" form="historyBulkForm" class="rounded border-gray-300 mr-2">
                    Hapus data backend juga
                </label>
                <button type="button" class="px-3 py-1.5 bg-red-500 text-white rounded text-sm font-medium hover:bg-red-600" style="background-color: #ef4444;" onclick="if(confirm('Yakin ingin menghapus riwayat yang dipilih?')) document.getElementById('historyBulkForm').submit()">Hapus Terpilih</button>
            </div>
        </div>

        <form action="{{ route('user.history.destroyBulk') }}" method="POST" id="historyBulkForm">
            @csrf
            @method('DELETE')
        <div class="pc-table-wrap">
            <table class="pc-table" style="width: 100%;">
                <thead>
                    <tr>
                        <th class="w-12 text-center" style="width: 3rem;">
                            <input type="checkbox" id="selectAll" class="rounded border-gray-300">
                        </th>
                        <th class="w-16 text-center">Tipe</th>
                        <th>Aktivitas</th>
                        <th>Detail</th>
                        <th class="text-right">Waktu</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($histories as $history)
                        <tr>
                            <td class="text-center">
                                <input type="checkbox" name="ids[]" value="{{ $history->id }}" class="history-checkbox rounded border-gray-300">
                            </td>
                            <td>
                                <div class="w-10 h-10 rounded-xl mx-auto flex items-center justify-center border text-lg" style="background-color: {{ $history->color }}12; color: {{ $history->color }}; border-color: {{ $history->color }}25">
                                    {{ $history->icon }}
                                </div>
                            </td>
                            <td>
                                <div class="font-medium max-w-md line-clamp-2">{{ $history->description }}</div>
                                <span class="text-[10px] font-bold uppercase tracking-wider mt-1 inline-block" style="color: {{ $history->color }}">{{ $history->action_type }}</span>
                            </td>
                            <td class="text-sm">
                                @if($history->activity_type === 'plagiarism_check' && isset($history->metadata['check_id']))
                                    <a href="{{ route('user.plagiarism.result', $history->metadata['check_id']) }}" class="pc-link">Lihat Hasil Plagiasi</a>
                                @elseif($history->activity_type === 'journal_generate' && isset($history->metadata['journal_id']))
                                    <a href="{{ route('user.journal.show', $history->metadata['journal_id']) }}" class="pc-link">Lihat Jurnal</a>
                                @elseif($history->activity_type === 'improvement' && isset($history->metadata['improvement_id']))
                                    <a href="{{ route('user.improvement.show', $history->metadata['improvement_id']) }}" class="pc-link" style="color: var(--pc-accent);">Lihat Perbaikan</a>
                                @else
                                    <span style="color: var(--pc-text-subtle);">—</span>
                                @endif
                            </td>
                            <td class="text-right">
                                <div class="text-sm font-medium">{{ $history->created_at->format('d M Y') }}</div>
                                <div class="text-xs" style="color: var(--pc-text-subtle);">{{ $history->created_at->format('H:i') }}</div>
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
