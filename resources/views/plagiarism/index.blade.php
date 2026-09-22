@extends($layout ?? 'layouts.user')

@section('title', 'Cek Plagiasi')
@section('page-title', 'Cek Plagiasi')
@section('page-subtitle', 'Unggah dokumen dan pilih sumber pengecekan')

@section('content')
<div x-data="plagiarismChecker()">
    <div class="pc-card p-6 sm:p-8 relative overflow-hidden">
        <form action="{{ route($routePrefix.'.plagiarism.pay') }}" method="POST" enctype="multipart/form-data" @submit="isChecking = true">
            @csrf

            {{-- Upload --}}
            <div class="mb-8">
                <label class="pc-label">Unggah Dokumen <span class="text-red-500">*</span></label>
                <div class="pc-upload-zone"
                     :class="{ 'active': dragover }"
                     @dragover.prevent="dragover = true"
                     @dragleave.prevent="dragover = false"
                     @drop.prevent="dragover = false; handleDrop($event)">

                    <div class="w-14 h-14 rounded-2xl flex items-center justify-center mb-4 transition-transform" style="background: var(--pc-bg-subtle);" :class="dragover ? 'scale-110' : ''">
                        <svg x-show="!fileName" class="w-7 h-7" style="color: var(--pc-text-subtle);" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"/></svg>
                        <svg x-show="fileName" class="w-7 h-7 text-emerald-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    </div>

                    <label for="file-upload" class="cursor-pointer">
                        <span class="pc-btn pc-btn-secondary" x-text="fileName ? 'Ganti file' : 'Pilih file'"></span>
                        <input id="file-upload" name="file" type="file" class="sr-only" accept=".pdf,.docx,.txt" @change="handleFileChange" required>
                    </label>
                    <span x-show="!fileName" class="text-sm ml-1" style="color: var(--pc-text-muted);">atau drag & drop</span>
                    <p class="text-xs mt-2" style="color: var(--pc-text-subtle);" x-text="fileName ? fileName : 'PDF, DOCX, TXT — maks. 10MB. Upload dokumen asli, bukan laporan Turnitin.'"></p>
                </div>
                @error('file') <p class="text-red-500 text-xs mt-2">{{ $message }}</p> @enderror
            </div>

            {{-- Filter BAB --}}
            <div class="mb-8" x-show="fileName && !isDetectingChapters && detectedChapters.length > 0" x-cloak>
                <div class="flex items-center justify-between mb-4">
                    <label class="pc-label mb-0">Filter BAB yang Dicek <span class="text-xs font-normal" style="color: var(--pc-text-muted);">(Opsional)</span></label>
                    <button type="button" @click="selectAllChapters = !selectAllChapters; toggleAllChapters()" class="pc-btn-link text-xs">
                        <span x-text="selectAllChapters ? 'Batalkan semua' : 'Pilih semua'"></span>
                    </button>
                </div>
                <p class="text-xs mb-3" style="color: var(--pc-text-subtle);">Sistem mendeteksi <span class="font-bold text-indigo-500" x-text="detectedChapters.length"></span> bagian di dalam dokumen Anda. Jika Anda memilih spesifik bagian di bawah, sistem hanya akan membaca dan mengecek bagian dokumen yang dipilih. Kosongkan (jangan pilih satupun) jika ingin mengecek seluruh dokumen.</p>

                <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-6 gap-3">
                    <template x-for="chapter in detectedChapters" :key="chapter.key">
                        <label class="pc-source-card !p-3 flex items-center gap-2 cursor-pointer transition-colors" :class="{ 'selected border-indigo-500 bg-indigo-50/50 dark:bg-indigo-900/20': chapters.includes(chapter.key) }">
                            <input type="checkbox" name="chapters[]" :value="chapter.key" class="w-4 h-4 rounded border-slate-300 text-indigo-600 focus:ring-indigo-500 shrink-0" x-model="chapters">
                            <span class="text-sm font-semibold" x-text="chapter.label"></span>
                        </label>
                    </template>
                </div>
            </div>

            {{-- Loading Chapters Indicator --}}
            <div class="mb-8 p-4 rounded-xl border border-indigo-100 bg-indigo-50/50 dark:bg-indigo-900/20 dark:border-indigo-800" x-show="isDetectingChapters" x-cloak>
                <div class="flex items-center gap-3">
                    <svg class="animate-spin w-5 h-5 text-indigo-500" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"/></svg>
                    <span class="text-sm font-medium text-indigo-700 dark:text-indigo-300">Sedang mendeteksi BAB dan struktur dokumen...</span>
                </div>
            </div>

            {{-- Sources --}}
            <div class="mb-8">
                <div class="flex items-center justify-between mb-4">
                    <label class="pc-label mb-0">Sumber Pengecekan <span class="text-red-500">*</span></label>
                    <button type="button" @click="selectAll = !selectAll; toggleAll()" class="pc-btn-link text-xs">
                        <span x-text="selectAll ? 'Batalkan semua' : 'Pilih semua'"></span>
                    </button>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                    @php
                        $sourceList = [
                            ['key' => 'web', 'label' => 'Web Pages', 'desc' => 'Pencarian internet umum'],
                            ['key' => 'google_scholar', 'label' => 'Google Scholar', 'desc' => 'Jurnal & artikel akademik'],
                            ['key' => 'elsevier', 'label' => 'Elsevier / Scopus', 'desc' => 'Database Scopus', 'sim' => !$settings?->elsevier_enabled || !$settings?->elsevier_api_key],
                            ['key' => 'openalex', 'label' => 'OpenAlex & arXiv', 'desc' => 'Open access papers'],
                            ['key' => 'crossref', 'label' => 'Crossref Published', 'desc' => 'Artikel & buku terbitan'],
                            ['key' => 'crossref_posted', 'label' => 'Crossref Posted', 'desc' => 'Manuskrip & preprint'],
                            ['key' => 'publications', 'label' => 'Publications (CORE)', 'desc' => 'Agregator publikasi umum'],
                        ];
                    @endphp

                    @foreach($sourceList as $src)
                    <label class="pc-source-card" :class="{ 'selected': sources.includes('{{ $src['key'] }}') }">
                        <input type="checkbox" name="sources[]" value="{{ $src['key'] }}" class="w-4 h-4 rounded border-slate-300 text-indigo-600 focus:ring-indigo-500 shrink-0 mt-0.5" x-model="sources">
                        <div class="min-w-0">
                            <div class="flex items-center gap-2">
                                <span class="text-sm font-semibold">{{ $src['label'] }}</span>
                                @if(!empty($src['sim']))
                                    <span class="pc-badge-warning text-[10px] py-0.5">Simulasi</span>
                                @endif
                            </div>
                            <p class="text-xs mt-0.5" style="color: var(--pc-text-muted);">{{ $src['desc'] }}</p>
                        </div>
                    </label>
                    @endforeach
                </div>
                @error('sources') <p class="text-red-500 text-xs mt-2">{{ $message }}</p> @enderror
            </div>

            <div class="flex justify-end pt-2 border-t" style="border-color: var(--pc-border);">
                <button type="submit" class="pc-btn-primary pc-btn-lg w-full sm:w-auto" :disabled="isChecking || isDetectingChapters || sources.length === 0 || !fileName">
                    <svg x-show="!isChecking" class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                    <svg x-show="isChecking" class="animate-spin w-5 h-5" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"/></svg>
                    <span x-text="isChecking ? 'Memproses...' : 'Mulai Pengecekan'"></span>
                </button>
            </div>
        </form>

        {{-- Loading overlay --}}
        <div x-show="isChecking" x-cloak x-transition.opacity
             class="absolute inset-0 z-10 backdrop-blur-sm flex flex-col items-center justify-center rounded-3xl bg-white/85 dark:bg-slate-900/85">
            <div class="text-center px-6">
                <div class="w-16 h-16 mx-auto mb-6">
                    <svg class="animate-spin w-full h-full text-indigo-500" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"/>
                    </svg>
                </div>
                <h3 class="text-lg font-bold mb-1">Menganalisis Dokumen</h3>
                <p class="text-sm" style="color: var(--pc-text-muted);" x-text="statusText"></p>
                <p class="text-xs mt-2" style="color: var(--pc-text-subtle);">Biasanya selesai dalam beberapa detik</p>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
function plagiarismChecker() {
    return {
        dragover: false,
        fileName: '',
        chapters: [],
        detectedChapters: [],
        isDetectingChapters: false,
        selectAllChapters: false,
        sources: {!! json_encode($settings?->default_sources ?? ['web', 'google_scholar', 'openalex', 'crossref', 'crossref_posted', 'publications']) !!},
        selectAll: false,
        isChecking: false,
        statusText: 'Membaca dan mengekstrak teks dokumen...',
        allSourceKeys: ['web', 'google_scholar', 'elsevier', 'openalex', 'crossref', 'crossref_posted', 'publications'],
        babDictionary: {
            'abstrak': 'Abstrak',
            'kata_pengantar': 'Kata Pengantar',
            '1': 'BAB I',
            '2': 'BAB II',
            '3': 'BAB III',
            '4': 'BAB IV',
            '5': 'BAB V',
            '6': 'BAB VI',
            '7': 'BAB VII',
            '8': 'BAB VIII',
            '9': 'BAB IX',
            '10': 'BAB X',
            'daftar_pustaka': 'Daftar Pustaka',
            'lampiran': 'Lampiran',
        },

        init() {
            this.selectAll = this.sources.length === this.allSourceKeys.length;
            this.$watch('isChecking', v => { if (v) this.startStatusRotation(); });
        },

        async handleFileChange(e) {
            const files = e.target.files;
            if (files.length > 0) {
                this.fileName = files[0].name;
                await this.detectChapters(files[0]);
            } else {
                this.fileName = '';
                this.detectedChapters = [];
                this.chapters = [];
            }
        },

        async handleDrop(e) {
            const files = e.dataTransfer.files;
            if (files.length > 0) {
                document.getElementById('file-upload').files = files;
                this.fileName = files[0].name;
                await this.detectChapters(files[0]);
            }
        },

        async detectChapters(file) {
            this.isDetectingChapters = true;
            this.chapters = [];
            
            const formData = new FormData();
            formData.append('file', file);
            formData.append('_token', '{{ csrf_token() }}');
            
            try {
                const url = '{{ route($routePrefix.".plagiarism.extract_chapters") }}';
                const response = await fetch(url, {
                    method: 'POST',
                    body: formData
                });
                
                if (response.ok) {
                    const data = await response.json();
                    this.detectedChapters = (data.chapters || []).map(key => {
                        return { key: key, label: this.babDictionary[key] || key.toUpperCase() };
                    });
                }
            } catch (error) {
                console.error('Failed to extract chapters:', error);
            } finally {
                this.isDetectingChapters = false;
            }
        },

        toggleAll() {
            this.sources = this.selectAll ? [...this.allSourceKeys] : [];
        },

        toggleAllChapters() {
            this.chapters = this.selectAllChapters ? this.detectedChapters.map(c => c.key) : [];
        },

        startStatusRotation() {
            const steps = [
                'Membaca dan mengekstrak teks dokumen...',
                'Mencari sumber referensi...',
                'Membandingkan kalimat dengan sumber...',
                'Menyusun hasil analisis...',
            ];
            let idx = 0;
            this.statusText = steps[0];
            const interval = setInterval(() => {
                if (!this.isChecking) { clearInterval(interval); return; }
                idx = (idx + 1) % steps.length;
                this.statusText = steps[idx];
            }, 2500);
        }
    }
}
</script>
@endpush
