@extends('layouts.user')

@section('title', 'Buat Jurnal Baru')
@section('page-title', 'Buat Jurnal')
@section('page-subtitle', 'Isi form untuk generate jurnal akademik')

@section('content')
<div x-data="journalForm()">
    <form action="{{ route('user.journal.generate') }}" method="POST" @submit="isGenerating = true" class="space-y-5">
        @csrf

        <div class="pc-card p-6 sm:p-8">
            <h3 class="pc-section-title mb-6 pb-4 border-b" style="border-color: var(--pc-border);">Informasi Jurnal</h3>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                <div class="md:col-span-2">
                    <label class="pc-label">Judul Jurnal <span class="text-red-500">*</span></label>
                    <input type="text" name="title" class="pc-input" value="{{ old('title') }}" required placeholder="Analisis Metode Deep Learning untuk Deteksi Plagiarisme">
                    @error('title') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label class="pc-label">Penulis Utama <span class="text-red-500">*</span></label>
                    <input type="text" name="author" class="pc-input" value="{{ old('author', auth()->user()->name) }}" required>
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
                    <textarea name="abstract" rows="4" class="pc-textarea" required placeholder="Tuliskan abstrak jurnal...">{{ old('abstract') }}</textarea>
                </div>
                <div class="md:col-span-2">
                    <label class="pc-label">Kata Kunci <span class="text-red-500">*</span></label>
                    <input type="text" name="keywords" class="pc-input" value="{{ old('keywords') }}" required placeholder="plagiarisme, NLP, deep learning">
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
                            <input type="text" :name="`sections[${index}][title]`" x-model="section.title" class="pc-input" required placeholder="Pendahuluan, Metodologi, dll">
                        </div>
                        <textarea :name="`sections[${index}][content]`" x-model="section.content" rows="5" class="pc-textarea" required placeholder="Konten bagian..."></textarea>
                    </div>
                </template>
            </div>
        </div>

        <div class="pc-card p-6 sm:p-8">
            <h3 class="pc-section-title mb-5">Pilih Template</h3>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <label class="cursor-pointer">
                    <input type="radio" name="template_type" value="template_a" class="peer sr-only" checked>
                    <div class="p-5 rounded-2xl border-2 peer-checked:border-indigo-500 peer-checked:bg-indigo-50 dark:peer-checked:bg-indigo-900/15 transition-all" style="border-color: var(--pc-border);">
                        <h4 class="font-bold mb-1">Template A — Standard</h4>
                        <p class="text-sm" style="color: var(--pc-text-muted);">Format akademik klasik Times New Roman</p>
                    </div>
                </label>
                <label class="cursor-pointer">
                    <input type="radio" name="template_type" value="template_b" class="peer sr-only">
                    <div class="p-5 rounded-2xl border-2 peer-checked:border-indigo-500 peer-checked:bg-indigo-50 dark:peer-checked:bg-indigo-900/15 transition-all" style="border-color: var(--pc-border);">
                        <h4 class="font-bold mb-1">Template B — Modern</h4>
                        <p class="text-sm" style="color: var(--pc-text-muted);">Desain kontemporer dengan font Arial</p>
                    </div>
                </label>
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
function journalForm() {
    return {
        isGenerating: false,
        sections: [{ id: Date.now(), title: 'Pendahuluan', content: '' }],
        addSection() { this.sections.push({ id: Date.now(), title: '', content: '' }); },
        removeSection(i) { if (this.sections.length > 1) this.sections.splice(i, 1); }
    }
}
</script>
@endpush
