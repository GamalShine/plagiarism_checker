@extends($layout ?? 'layouts.user')

@section('title', 'Buat Jurnal Baru')
@section('page-title', 'Generator Jurnal')
@section('page-subtitle', 'Pilih template standar dan unggah naskah Anda untuk membuat jurnal otomatis')

@php
    $routePrefix = str_starts_with(request()->route()?->getName() ?? '', 'admin.') ? 'admin' : 'user';
@endphp

@section('content')
<div x-data="journalCreatorApp()" class="space-y-6">

    {{-- Tabs Mode: Otomatis dari File (Rekomendasi) vs Input Manual --}}
    <div class="flex items-center gap-2 p-1.5 rounded-2xl bg-slate-100 dark:bg-slate-800/80 max-w-md">
        <button type="button" @click="mode = 'upload'"
            class="flex-1 py-2.5 px-4 rounded-xl text-xs font-bold transition-all text-center flex items-center justify-center gap-2"
            :class="mode === 'upload' ? 'bg-white dark:bg-slate-700 text-blue-600 dark:text-blue-400 shadow-xs' : 'text-slate-600 dark:text-slate-400 hover:text-slate-900'">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"/>
            </svg>
            <span>Upload File (Otomatis)</span>
        </button>
        <button type="button" @click="mode = 'manual'"
            class="flex-1 py-2.5 px-4 rounded-xl text-xs font-bold transition-all text-center flex items-center justify-center gap-2"
            :class="mode === 'manual' ? 'bg-white dark:bg-slate-700 text-blue-600 dark:text-blue-400 shadow-xs' : 'text-slate-600 dark:text-slate-400 hover:text-slate-900'">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
            </svg>
            <span>Isi Manual / Form</span>
        </button>
    </div>

    {{-- ======================================================== --}}
    {{-- FORM MODE 1: UPLOAD DOKUMEN (AUTO-CONVERT KE TEMPLATE) --}}
    {{-- ======================================================== --}}
    <form x-show="mode === 'upload'" action="{{ route($routePrefix . '.journal.generate') }}" method="POST" enctype="multipart/form-data" @submit="isGenerating = true" class="space-y-6">
        @csrf
        <input type="hidden" name="mode" value="upload">

        {{-- 1. PILIH TEMPLATE --}}
        <div class="pc-card p-6 sm:p-8">
            <div class="flex items-center justify-between pb-4 mb-6 border-b border-slate-100 dark:border-slate-800">
                <div>
                    <h3 class="pc-section-title">Langkah 1: Pilih Template Target Jurnal <span class="text-red-500">*</span></h3>
                    <p class="text-xs text-slate-500 mt-1">Pilih format tata letak jurnal yang ingin Anda hasilkan</p>
                </div>
                <span class="text-xs font-bold text-blue-600 dark:text-blue-400 bg-blue-50 dark:bg-blue-950/40 px-3 py-1.5 rounded-lg">
                    Format Resmi
                </span>
            </div>

            @php
            $templateOptions = [
                ['key' => 'scopus', 'name' => 'Scopus & Internasional', 'badge' => 'Internasional', 'badgeColor' => 'bg-blue-50 text-blue-700 border-blue-200', 'desc' => 'Format standar artikel jurnal internasional bereputasi Scopus & IEEE'],
                ['key' => 'sinta_1', 'name' => 'SINTA 1 (Akreditasi Utama)', 'badge' => 'SINTA 1', 'badgeColor' => 'bg-emerald-50 text-emerald-700 border-emerald-200', 'desc' => 'Standar nasional tertinggi akreditasi jurnal Kemdikbudristek'],
                ['key' => 'sinta_2', 'name' => 'SINTA 2 (Nasional)', 'badge' => 'SINTA 2', 'badgeColor' => 'bg-emerald-50 text-emerald-700 border-emerald-200', 'desc' => 'Dua kolom akademik formal dengan struktur IMRaD lengkap'],
                ['key' => 'sinta_3', 'name' => 'SINTA 3 (Nasional)', 'badge' => 'SINTA 3', 'badgeColor' => 'bg-teal-50 text-teal-700 border-teal-200', 'desc' => 'Format jurnal nasional terakreditasi peringkat 3'],
                ['key' => 'sinta_4', 'name' => 'SINTA 4 (Nasional)', 'badge' => 'SINTA 4', 'badgeColor' => 'bg-teal-50 text-teal-700 border-teal-200', 'desc' => 'Format naskah ilmiah umum nasional peringkat 4'],
                ['key' => 'sinta_5', 'name' => 'SINTA 5 (Nasional)', 'badge' => 'SINTA 5', 'badgeColor' => 'bg-teal-50 text-teal-700 border-teal-200', 'desc' => 'Format naskah ilmiah umum nasional peringkat 5'],
                ['key' => 'doaj', 'name' => 'DOAJ (Open Access)', 'badge' => 'Global Open Access', 'badgeColor' => 'bg-amber-50 text-amber-700 border-amber-200', 'desc' => 'Directory of Open Access Journals dengan tipografi modern'],
                ['key' => 'garuda', 'name' => 'Garuda (Ristekbrin)', 'badge' => 'Indeks Garuda', 'badgeColor' => 'bg-indigo-50 text-indigo-700 border-indigo-200', 'desc' => 'Format artikel portal Garuda Ristekbrin/Kemdikbud'],
            ];
            @endphp

            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-3.5">
                @foreach($templateOptions as $tmpl)
                <label class="relative flex flex-col justify-between p-4 rounded-xl border-2 cursor-pointer transition-all duration-200 group"
                       :class="selectedTemplate === '{{ $tmpl['key'] }}' ? 'border-blue-600 bg-blue-50/50 dark:bg-blue-950/20 ring-2 ring-blue-500/20' : 'border-slate-200 dark:border-slate-700 hover:border-slate-300 dark:hover:border-slate-600 bg-white dark:bg-slate-800'">
                    <input type="radio" name="template_type" value="{{ $tmpl['key'] }}" x-model="selectedTemplate" class="sr-only">
                    
                    <div>
                        <div class="flex items-center justify-between gap-2 mb-2">
                            <span class="px-2 py-0.5 rounded text-[10px] font-extrabold uppercase border {{ $tmpl['badgeColor'] }}">{{ $tmpl['badge'] }}</span>
                            <span class="w-4 h-4 rounded-full border flex items-center justify-center transition-colors"
                                  :class="selectedTemplate === '{{ $tmpl['key'] }}' ? 'border-blue-600 bg-blue-600 text-white' : 'border-slate-300 dark:border-slate-600'">
                                <svg class="w-2.5 h-2.5" fill="currentColor" viewBox="0 0 20 20" x-show="selectedTemplate === '{{ $tmpl['key'] }}'">
                                    <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/>
                                </svg>
                            </span>
                        </div>
                        <h4 class="text-sm font-bold text-slate-900 dark:text-white group-hover:text-blue-600 transition">{{ $tmpl['name'] }}</h4>
                        <p class="text-[11px] text-slate-500 dark:text-slate-400 mt-1 line-clamp-2 leading-relaxed">{{ $tmpl['desc'] }}</p>
                    </div>
                </label>
                @endforeach
            </div>
            @error('template_type') <p class="text-red-500 text-xs mt-2">{{ $message }}</p> @enderror
        </div>

        {{-- 2. UPLOAD FILE NASKAH --}}
        <div class="pc-card p-6 sm:p-8">
            <div class="flex items-center justify-between pb-4 mb-6 border-b border-slate-100 dark:border-slate-800">
                <div>
                    <h3 class="pc-section-title">Langkah 2: Unggah Dokumen Naskah / Skripsi / Tesis <span class="text-red-500">*</span></h3>
                    <p class="text-xs text-slate-500 mt-1">Sistem otomatis mendeteksi Abstrak, Penulis, Pendahuluan, Metodologi, hingga Kesimpulan & Daftar Pustaka</p>
                </div>
            </div>

            <div class="pc-upload-zone group" :class="{ 'active': dragover }" @dragover.prevent="dragover = true"
                @dragleave.prevent="dragover = false" @drop.prevent="dragover = false; handleDrop($event)">

                <div class="w-16 h-16 rounded-2xl flex items-center justify-center mb-4 transition-all duration-300 shadow-xs"
                    :class="fileName ? 'bg-emerald-50 dark:bg-emerald-950/40 text-emerald-600 dark:text-emerald-400' : 'bg-blue-50 dark:bg-blue-950/40 text-blue-600 dark:text-blue-400 group-hover:scale-110'">
                    <svg x-show="!fileName" class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                            d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12" />
                    </svg>
                    <svg x-show="fileName" x-cloak class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                </div>

                <label for="file-upload-journal" class="cursor-pointer">
                    <span class="pc-btn pc-btn-primary shadow-sm hover:shadow transition-all" x-text="fileName ? 'Ganti File Naskah' : 'Pilih File Dokumen (Word / PDF)'"></span>
                    <input id="file-upload-journal" name="file" type="file" class="sr-only" accept=".pdf,.docx,.txt"
                        @change="handleFileChange" :required="mode === 'upload'">
                </label>

                <p class="text-sm mt-3" style="color: var(--pc-text-muted);" x-show="!fileName">
                    atau drag & drop file naskah ke area ini
                </p>
                
                <div x-show="fileName" x-cloak class="mt-3 inline-flex items-center gap-2 px-3.5 py-1.5 rounded-xl bg-emerald-50 dark:bg-emerald-950/40 border border-emerald-200 dark:border-emerald-800/60 text-xs font-semibold text-emerald-700 dark:text-emerald-300">
                    <svg class="w-4 h-4 text-emerald-500 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                    </svg>
                    <span x-text="fileName" class="truncate max-w-xs sm:max-w-md"></span>
                </div>

                <p class="text-xs mt-2" style="color: var(--pc-text-subtle);" x-show="!fileName">
                    Format yang didukung: DOCX, PDF, TXT (Maks. 300MB)
                </p>
            </div>
            @error('file') <p class="text-red-500 text-xs mt-2">{{ $message }}</p> @enderror
        </div>

        {{-- 3. METADATA TAMBAHAN (OPSIONAL) --}}
        <div class="pc-card p-6 sm:p-8">
            <h3 class="pc-section-title mb-4">Informasi Penulis (Opsional — Otomatis Terisi jika Dikosongkan)</h3>
            <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                <div>
                    <label class="pc-label">Nama Penulis</label>
                    <input type="text" name="author" class="pc-input text-xs sm:text-sm" value="{{ auth()->user()->name }}" placeholder="Nama Penulis Utama">
                </div>
                <div>
                    <label class="pc-label">Afiliasi / Kampus</label>
                    <input type="text" name="institution" class="pc-input text-xs sm:text-sm" placeholder="Contoh: Universitas Gadjah Mada">
                </div>
                <div>
                    <label class="pc-label">Email Korespondensi</label>
                    <input type="email" name="email" class="pc-input text-xs sm:text-sm" value="{{ auth()->user()->email }}" placeholder="email@institusi.ac.id">
                </div>
            </div>
        </div>

        {{-- TOMBOL SUBMIT GENERATE --}}
        <div class="flex items-center justify-between pt-2">
            <p class="text-xs text-slate-500 dark:text-slate-400">
                Jurnal akan otomatis diekspor ke format <strong>DOCX (Word)</strong> dan <strong>PDF</strong> siap submit.
            </p>
            <button type="submit" class="pc-btn-primary pc-btn-lg w-full sm:w-auto shadow-md hover:shadow-lg transition-all"
                :disabled="isGenerating || !fileName">
                <svg x-show="!isGenerating" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/>
                </svg>
                <svg x-show="isGenerating" x-cloak class="animate-spin w-5 h-5" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"/>
                </svg>
                <span x-text="isGenerating ? 'Sedang Menyusun Jurnal...' : 'Generate Jurnal Otomatis'"></span>
            </button>
        </div>
    </form>


    {{-- ======================================================== --}}
    {{-- FORM MODE 2: INPUT MANUAL LENGKAP                        --}}
    {{-- ======================================================== --}}
    <form x-show="mode === 'manual'" x-cloak action="{{ route($routePrefix . '.journal.generate') }}" method="POST" @submit="isGenerating = true" class="space-y-5">
        @csrf
        <input type="hidden" name="mode" value="manual">

        <div class="pc-card p-6 sm:p-8">
            <h3 class="pc-section-title mb-6 pb-4 border-b" style="border-color: var(--pc-border);">Pilih Template Jurnal</h3>
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-3.5">
                @foreach($templateOptions as $tmpl)
                <label class="relative flex flex-col justify-between p-4 rounded-xl border-2 cursor-pointer transition-all duration-200"
                       :class="selectedTemplate === '{{ $tmpl['key'] }}' ? 'border-blue-600 bg-blue-50/50 dark:bg-blue-950/20 ring-2 ring-blue-500/20' : 'border-slate-200 dark:border-slate-700 hover:border-slate-300 dark:hover:border-slate-600 bg-white dark:bg-slate-800'">
                    <input type="radio" name="template_type" value="{{ $tmpl['key'] }}" x-model="selectedTemplate" class="sr-only">
                    <div>
                        <span class="px-2 py-0.5 rounded text-[10px] font-extrabold uppercase border {{ $tmpl['badgeColor'] }} mb-2 inline-block">{{ $tmpl['badge'] }}</span>
                        <h4 class="text-sm font-bold text-slate-900 dark:text-white">{{ $tmpl['name'] }}</h4>
                        <p class="text-[11px] text-slate-500 dark:text-slate-400 mt-1 line-clamp-2">{{ $tmpl['desc'] }}</p>
                    </div>
                </label>
                @endforeach
            </div>
        </div>

        <div class="pc-card p-6 sm:p-8">
            <h3 class="pc-section-title mb-6 pb-4 border-b" style="border-color: var(--pc-border);">Informasi Jurnal</h3>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                <div class="md:col-span-2">
                    <label class="pc-label">Judul Jurnal <span class="text-red-500">*</span></label>
                    <input type="text" name="title" class="pc-input" value="{{ old('title') }}" :required="mode === 'manual'" placeholder="Analisis Metode Deep Learning untuk Deteksi Plagiarisme">
                </div>
                <div>
                    <label class="pc-label">Penulis Utama <span class="text-red-500">*</span></label>
                    <input type="text" name="author" class="pc-input" value="{{ old('author', auth()->user()->name) }}" :required="mode === 'manual'">
                </div>
                <div>
                    <label class="pc-label">Email Penulis</label>
                    <input type="email" name="email" class="pc-input" value="{{ old('email', auth()->user()->email) }}">
                </div>
                <div class="md:col-span-2">
                    <label class="pc-label">Institusi / Afiliasi</label>
                    <input type="text" name="institution" class="pc-input" value="{{ old('institution') }}" placeholder="Universitas Indonesia, Fakultas Ilmu Komputer">
                </div>
                <div class="md:col-span-2">
                    <label class="pc-label">Abstrak <span class="text-red-500">*</span></label>
                    <textarea name="abstract" rows="4" class="pc-textarea" :required="mode === 'manual'" placeholder="Tuliskan abstrak jurnal...">{{ old('abstract') }}</textarea>
                </div>
                <div class="md:col-span-2">
                    <label class="pc-label">Kata Kunci <span class="text-red-500">*</span></label>
                    <input type="text" name="keywords" class="pc-input" value="{{ old('keywords') }}" :required="mode === 'manual'" placeholder="plagiarisme, NLP, deep learning">
                </div>
            </div>
        </div>

        <div class="pc-card p-6 sm:p-8">
            <div class="flex justify-between items-center mb-6 pb-4 border-b" style="border-color: var(--pc-border);">
                <h3 class="pc-section-title">Isi Jurnal</h3>
                <button type="button" @click="addSection()" class="pc-link text-xs flex items-center gap-1">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                    Tambah Bagian
                </button>
            </div>

            <div class="space-y-4">
                <template x-for="(section, index) in sections" :key="section.id">
                    <div class="p-5 rounded-2xl border relative group" style="background: var(--pc-bg-subtle); border-color: var(--pc-border);">
                        <button type="button" @click="removeSection(index)" x-show="sections.length > 1" class="absolute top-4 right-4 p-1 rounded-lg text-slate-400 hover:text-red-500 opacity-0 group-hover:opacity-100 transition-opacity">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                        </button>
                        <div class="mb-4 pr-8">
                            <label class="text-xs font-bold uppercase tracking-wider mb-2 block" style="color: var(--pc-text-muted);" x-text="`Bagian ${index + 1}`"></label>
                            <input type="text" :name="`sections[${index}][title]`" x-model="section.title" class="pc-input" :required="mode === 'manual'" placeholder="Pendahuluan, Metodologi, dll">
                        </div>
                        <textarea :name="`sections[${index}][content]`" x-model="section.content" rows="5" class="pc-textarea" :required="mode === 'manual'" placeholder="Konten bagian..."></textarea>
                    </div>
                </template>
            </div>
        </div>

        <div class="flex justify-end">
            <button type="submit" class="pc-btn-primary pc-btn-lg w-full sm:w-auto" :disabled="isGenerating">
                <svg x-show="!isGenerating" class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/></svg>
                <svg x-show="isGenerating" class="animate-spin w-5 h-5" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"/></svg>
                <span x-text="isGenerating ? 'Membuat Jurnal...' : 'Generate Jurnal'"></span>
            </button>
        </div>
    </form>

</div>
@endsection

@push('scripts')
<script>
function journalCreatorApp() {
    return {
        mode: 'upload',
        selectedTemplate: 'scopus',
        dragover: false,
        fileName: '',
        isGenerating: false,
        sections: [{ id: Date.now(), title: 'Pendahuluan', content: '' }],

        handleFileChange(e) {
            const files = e.target.files;
            this.fileName = files.length > 0 ? files[0].name : '';
        },

        handleDrop(e) {
            const files = e.dataTransfer.files;
            if (files.length > 0) {
                document.getElementById('file-upload-journal').files = files;
                this.fileName = files[0].name;
            }
        },

        addSection() {
            this.sections.push({ id: Date.now(), title: '', content: '' });
        },

        removeSection(i) {
            if (this.sections.length > 1) {
                this.sections.splice(i, 1);
            }
        }
    }
}
</script>
@endpush
