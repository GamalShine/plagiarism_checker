@extends('layouts.user')

@section('title', 'Pengaturan')
@section('page-title', 'Pengaturan')
@section('page-subtitle', 'Kelola API keys dan preferensi pengecekan')

@section('content')
<div class="space-y-5" x-data="settingsForm()">

    <form action="{{ route('user.settings.update') }}" method="POST" class="space-y-5">
        @csrf

        {{-- API Keys --}}
        <div class="pc-card p-6 sm:p-8">
            <div class="flex items-center gap-3 mb-6 pb-4 border-b" style="border-color: var(--pc-border);">
                <div class="w-10 h-10 rounded-xl flex items-center justify-center" style="background: var(--pc-primary-soft); color: var(--pc-primary);">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1121 9z"/></svg>
                </div>
                <div>
                    <h3 class="pc-section-title">Konfigurasi API</h3>
                    <p class="text-xs" style="color: var(--pc-text-muted);">Kosongkan untuk mode simulasi</p>
                </div>
            </div>

            <div class="space-y-5">
                <div class="p-5 rounded-2xl border" style="background: var(--pc-bg-subtle); border-color: var(--pc-border);">
                    <h4 class="font-bold text-sm mb-4">Google Custom Search</h4>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="text-xs font-semibold mb-1.5 block" style="color: var(--pc-text-muted);">API Key</label>
                            <input type="password" name="google_cse_key" class="pc-input" value="{{ old('google_cse_key', $settings->google_cse_key) }}" placeholder="API Key">
                        </div>
                        <div>
                            <label class="text-xs font-semibold mb-1.5 block" style="color: var(--pc-text-muted);">Search Engine ID (CX)</label>
                            <input type="text" name="google_cse_id" class="pc-input" value="{{ old('google_cse_id', $settings->google_cse_id) }}" placeholder="Custom Search ID">
                        </div>
                    </div>
                </div>

                <div class="p-5 rounded-2xl border" style="background: var(--pc-bg-subtle); border-color: var(--pc-border);">
                    <h4 class="font-bold text-sm mb-4">Google Scholar (SerpAPI)</h4>
                    <input type="password" name="serpapi_key" class="pc-input" value="{{ old('serpapi_key', $settings->serpapi_key) }}" placeholder="SerpAPI Key">
                    <p class="text-xs mt-2" style="color: var(--pc-text-muted);">Dapatkan key gratis di <a href="https://serpapi.com" target="_blank" class="pc-link">serpapi.com</a></p>
                </div>

                <div class="p-5 rounded-2xl border transition-opacity" style="background: var(--pc-bg-subtle); border-color: var(--pc-border);" :class="!elsevierEnabled ? 'opacity-60' : ''">
                    <div class="flex items-center justify-between mb-4">
                        <h4 class="font-bold text-sm">Elsevier / Scopus</h4>
                        <label class="flex items-center gap-2 cursor-pointer">
                            <input type="checkbox" name="elsevier_enabled" value="1" class="sr-only" x-model="elsevierEnabled">
                            <div class="pc-toggle" :class="{ 'active': elsevierEnabled }">
                                <span class="pc-toggle-knob"></span>
                            </div>
                            <span class="text-xs font-semibold" x-text="elsevierEnabled ? 'Aktif' : 'Nonaktif'"></span>
                        </label>
                    </div>
                    <input type="password" name="elsevier_api_key" class="pc-input" value="{{ old('elsevier_api_key', $settings->elsevier_api_key) }}" placeholder="Elsevier API Key" :disabled="!elsevierEnabled">
                </div>
            </div>
        </div>

        {{-- Default sources --}}
        <div class="pc-card p-6 sm:p-8">
            <div class="flex items-center gap-3 mb-6 pb-4 border-b" style="border-color: var(--pc-border);">
                <div class="w-10 h-10 rounded-xl flex items-center justify-center" style="background: var(--pc-accent-soft); color: var(--pc-accent);">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                </div>
                <div>
                    <h3 class="pc-section-title">Sumber Default</h3>
                    <p class="text-xs" style="color: var(--pc-text-muted);">Dipilih otomatis saat cek plagiasi</p>
                </div>
            </div>

            @php $defaultSources = $settings->default_sources ?? ['web', 'google_scholar', 'openalex', 'crossref', 'crossref_posted', 'publications']; @endphp

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                @foreach(['web' => 'Web Pages', 'google_scholar' => 'Google Scholar', 'elsevier' => 'Elsevier / Scopus', 'openalex' => 'OpenAlex & arXiv', 'crossref' => 'Crossref Published', 'crossref_posted' => 'Crossref Posted', 'publications' => 'Publications Database'] as $key => $label)
                <label class="flex items-center gap-3 p-3 rounded-xl border cursor-pointer transition-colors hover:border-indigo-300" style="border-color: var(--pc-border);">
                    <input type="checkbox" name="default_sources[]" value="{{ $key }}" class="w-4 h-4 rounded text-indigo-600 focus:ring-indigo-500" {{ in_array($key, $defaultSources) ? 'checked' : '' }}>
                    <span class="text-sm font-medium">{{ $label }}</span>
                </label>
                @endforeach
            </div>
            @error('default_sources') <p class="text-red-500 text-xs mt-2">{{ $message }}</p> @enderror
        </div>

        <div class="flex justify-end pt-2 border-t" style="border-color: var(--pc-border);">
            <button type="submit" class="pc-btn-primary pc-btn-lg">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                Simpan Pengaturan
            </button>
        </div>
    </form>
</div>
@endsection

@push('scripts')
<script>
function settingsForm() {
    return { elsevierEnabled: {{ $settings->elsevier_enabled ? 'true' : 'false' }} }
}
</script>
@endpush
