@extends($layout ?? 'layouts.user')

@section('title', 'Detail Perbaikan')
@section('page-title', 'Tinjauan Perbaikan')
@section('page-subtitle', $improvement->document->title ?? 'Dokumen')

@php
    $routePrefix = str_starts_with(request()->route()?->getName() ?? '', 'admin.') ? 'admin' : 'user';
@endphp

@section('content')
<div x-data="improvementApp()">

    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
        <div>
            <span class="pc-badge-neutral">Mode: {{ ucfirst($improvement->mode) }}</span>
            <h2 class="text-2xl font-bold tracking-tight mt-2">{{ $improvement->document->title ?? 'Dokumen' }}</h2>
            <p class="text-sm mt-1" style="color: var(--pc-text-muted);">{{ $improvement->created_at->format('d M Y, H:i') }}</p>
        </div>
        
        @if($improvement->improved_content)
        <a href="{{ route($routePrefix . '.improvement.download', $improvement->id) }}" class="pc-btn-primary w-full sm:w-auto">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/></svg>
            Unduh Hasil Perbaikan
        </a>
        @endif
    </div>

    <!-- Similarity Progress -->
    @if($improvement->original_similarity !== null)
    <div class="pc-card p-8 flex flex-col md:flex-row items-center justify-center gap-8 md:gap-16">
        <div class="text-center">
            <p class="text-sm font-medium mb-2" style="color: var(--pc-text-muted);">Sebelum</p>
            <div class="text-4xl font-extrabold text-red-500">{{ $improvement->original_similarity }}%</div>
        </div>
        <div class="hidden md:flex flex-col items-center">
            <svg class="w-7 h-7 mb-1" style="color: var(--pc-text-subtle);" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"/></svg>
            <span class="text-[10px] font-bold uppercase tracking-widest" style="color: var(--pc-text-subtle);">Parafrase</span>
        </div>
        <div class="text-center">
            <p class="text-sm font-medium mb-2" style="color: var(--pc-text-muted);">Setelah</p>
            @if($improvement->improved_similarity !== null)
                <div class="text-4xl font-extrabold text-emerald-500">{{ $improvement->improved_similarity }}%</div>
            @else
                <div class="text-4xl font-extrabold" style="color: var(--pc-text-subtle);">?</div>
            @endif
        </div>
    </div>
    @endif

    @if(!$improvement->improved_content)
        <!-- Saran Perbaikan (Form) -->
        <form action="{{ route($routePrefix . '.improvement.apply', $improvement->id) }}" method="POST" class="pc-card p-6">
            @csrf
            
            <div class="flex justify-between items-center mb-6 pb-4 border-b border-gray-200 dark:border-gray-800">
                <div>
                    <h3 class="pc-section-title">Saran Parafrase</h3>
                    <p class="text-sm mt-1" style="color: var(--pc-text-muted);">Pilih kalimat yang ingin diterapkan perbaikannya.</p>
                </div>
                
                @if($improvement->mode === 'manual')
                <div class="flex items-center gap-2">
                    <button type="button" @click="selectAll = !selectAll; toggleAll()" class="pc-btn-soft pc-btn-sm">
                        <span x-text="selectAll ? 'Batal Pilih Semua' : 'Pilih Semua'"></span>
                    </button>
                </div>
                @endif
            </div>

            @if(empty($improvement->suggestions))
                <div class="text-center py-8 text-gray-500">
                    <svg class="w-12 h-12 mx-auto text-emerald-400 mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    <p>Tidak ada saran perbaikan. Dokumen Anda sudah terlihat baik!</p>
                </div>
            @else
                <div class="space-y-4 mb-8">
                    <input type="hidden" name="mode" value="{{ $improvement->mode === 'automatic' ? 'all' : 'selected' }}">
                    
                    @foreach($improvement->suggestions as $sug)
                        <label class="block p-4 border-2 rounded-xl transition-all cursor-pointer"
                               :class="selected.includes({{ $sug['index'] }}) ? 'border-violet-500 bg-violet-50/30 dark:bg-violet-900/10' : 'border-gray-100 dark:border-gray-800 hover:border-violet-300 dark:hover:border-violet-700'">
                            
                            <div class="flex items-start gap-4">
                                @if($improvement->mode === 'manual')
                                <div class="flex-shrink-0 pt-1">
                                    <input type="checkbox" name="selected[]" value="{{ $sug['index'] }}" x-model="selected" class="w-5 h-5 text-violet-600 rounded border-gray-300 focus:ring-violet-500">
                                </div>
                                @endif
                                
                                <div class="flex-1 w-full overflow-hidden">
                                    <div class="flex items-center gap-2 mb-2">
                                        <span class="px-2 py-0.5 text-[10px] font-bold uppercase rounded border" style="color: {{ $sug['color'] }}; border-color: {{ $sug['color'] }}50; background-color: {{ $sug['color'] }}10">
                                            {{ $sug['source'] }}
                                        </span>
                                        <span class="text-xs font-semibold text-rose-500">{{ $sug['similarity'] }}% Match</span>
                                    </div>
                                    
                                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                        <div class="bg-rose-50/50 dark:bg-rose-900/10 p-3 rounded-lg border border-rose-100 dark:border-rose-900/30">
                                            <p class="text-[10px] font-bold text-rose-500 uppercase tracking-wider mb-1">Teks Asli</p>
                                            <p class="text-sm text-gray-700 dark:text-gray-300">{{ $sug['original'] }}</p>
                                        </div>
                                        <div class="bg-emerald-50/50 dark:bg-emerald-900/10 p-3 rounded-lg border border-emerald-100 dark:border-emerald-900/30">
                                            <p class="text-[10px] font-bold text-emerald-500 uppercase tracking-wider mb-1">Saran Parafrase</p>
                                            <p class="text-sm text-gray-700 dark:text-gray-300 font-medium">{{ $sug['suggestion'] }}</p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </label>
                    @endforeach
                </div>

                <div class="flex justify-end">
                    <button type="submit" class="pc-btn-primary" :disabled="selected.length === 0 && '{{ $improvement->mode }}' === 'manual'">
                        Terapkan Perbaikan <span x-show="'{{ $improvement->mode }}' === 'manual'" x-text="`(${selected.length})`"></span>
                    </button>
                </div>
            @endif
        </form>
    @else
        <!-- Hasil Akhir -->
        <div class="pc-card overflow-hidden" x-data="{ view: 'improved' }">
            <div class="pc-tabs m-4 mb-0">
                <button type="button" @click="view = 'improved'" class="pc-tab" :class="{ 'active': view === 'improved' }">Hasil Perbaikan</button>
                <button type="button" @click="view = 'original'" class="pc-tab" :class="{ 'active': view === 'original' }">Teks Asli</button>
                <button type="button" @click="view = 'diff'" class="pc-tab" :class="{ 'active': view === 'diff' }">Perbandingan</button>
            </div>
            
            <div class="p-6">
                <!-- Improved -->
                <div x-show="view === 'improved'" class="whitespace-pre-wrap text-justify text-sm leading-relaxed text-gray-800 dark:text-gray-200 bg-emerald-50/30 dark:bg-emerald-900/5 p-6 rounded-xl border border-emerald-100 dark:border-emerald-900/20 max-h-[600px] overflow-y-auto">
                    {{ $improvement->improved_content }}
                </div>

                <!-- Original -->
                <div x-show="view === 'original'" style="display: none;" class="whitespace-pre-wrap text-justify text-sm leading-relaxed text-gray-800 dark:text-gray-200 bg-rose-50/30 dark:bg-rose-900/5 p-6 rounded-xl border border-rose-100 dark:border-rose-900/20 max-h-[600px] overflow-y-auto opacity-75">
                    {{ $improvement->original_content }}
                </div>

                <!-- Diff List -->
                <div x-show="view === 'diff'" style="display: none;" class="space-y-4 max-h-[600px] overflow-y-auto pr-2">
                    @foreach($improvement->suggestions ?? [] as $sug)
                        @if(str_contains($improvement->improved_content, $sug['suggestion']))
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 p-4 rounded-xl border border-gray-200 dark:border-gray-800">
                            <div class="bg-rose-50/50 dark:bg-rose-900/10 p-3 rounded-lg border border-rose-100 dark:border-rose-900/30">
                                <p class="text-[10px] font-bold text-rose-500 uppercase tracking-wider mb-1">Teks Asli</p>
                                <p class="text-sm text-gray-500 line-through">{{ $sug['original'] }}</p>
                            </div>
                            <div class="bg-emerald-50/50 dark:bg-emerald-900/10 p-3 rounded-lg border border-emerald-100 dark:border-emerald-900/30">
                                <p class="text-[10px] font-bold text-emerald-500 uppercase tracking-wider mb-1">Teks Baru</p>
                                <p class="text-sm text-gray-800 dark:text-gray-200">{{ $sug['suggestion'] }}</p>
                            </div>
                        </div>
                        @endif
                    @endforeach
                </div>
            </div>
        </div>
    @endif

</div>
@endsection

@push('scripts')
<script>
function improvementApp() {
    const suggestions = {!! json_encode($improvement->suggestions ?? []) !!};
    const indices = suggestions.map(s => s.index);
    
    return {
        selected: [],
        selectAll: false,
        
        init() {
            if ('{{ $improvement->mode }}' === 'automatic') {
                this.selected = [...indices];
            }
            
            this.$watch('selected', value => {
                this.selectAll = value.length === indices.length && indices.length > 0;
            });
        },
        
        toggleAll() {
            if (this.selectAll) {
                this.selected = [...indices];
            } else {
                this.selected = [];
            }
        }
    }
}
</script>
@endpush
