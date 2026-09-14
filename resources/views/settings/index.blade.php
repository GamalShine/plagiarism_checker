@extends($layout ?? 'layouts.user')

@section('title', 'Profile & Pengaturan')
@section('page-title', 'Profile')
@section('page-subtitle', 'Atur akun dan preferensi pengecekan Anda')

@php
    $routePrefix = str_starts_with(request()->route()?->getName() ?? '', 'admin.') ? 'admin' : 'user';
@endphp

@section('content')
<div class="space-y-6" x-data="settingsForm()">
    @php
        $user = auth()->user();
    @endphp

    <div class="pc-card overflow-hidden">
        <div class="p-6 sm:p-8">
            <div class="flex flex-col gap-5 lg:flex-row lg:items-center lg:justify-between">
                <div class="flex items-center gap-4">
                    <div class="flex h-16 w-16 items-center justify-center rounded-2xl bg-gradient-to-br from-indigo-500 to-teal-500 text-white shadow-lg shadow-indigo-500/20">
                        <svg class="h-8 w-8" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14c-4.418 0-8 2.239-8 5v1h16v-1c0-2.761-3.582-5-8-5z"/>
                        </svg>
                    </div>
                    <div>
                        <p class="text-xs font-semibold uppercase tracking-[0.2em] text-blue-600 dark:text-blue-300">ACCOUNT</p>
                        <h2 class="mt-2 text-2xl font-bold tracking-tight text-slate-900 dark:text-white">{{ $user->name }}</h2>
                        <p class="text-sm text-slate-500 dark:text-slate-400">{{ $user->email }}</p>
                    </div>
                </div>

                <div class="flex items-center gap-3">
                    <span class="pc-badge-success uppercase tracking-[0.12em]">AKTIF</span>
                    <button type="submit" form="settings-form" class="pc-btn-primary pc-btn-sm uppercase tracking-[0.12em]">
                        SIMPAN
                    </button>
                </div>
            </div>
        </div>
    </div>

    <div class="space-y-6">
        <form id="settings-form" action="{{ route($routePrefix . '.settings.update') }}" method="POST" class="space-y-6">
            @csrf

            <div class="pc-card p-6 sm:p-8">
                <div class="mb-6 flex items-center gap-3 pb-4 border-b" style="border-color: var(--pc-border);">
                    <div class="flex h-10 w-10 items-center justify-center rounded-xl" style="background: var(--pc-primary-soft); color: var(--pc-primary);">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14c-4.418 0-8 2.239-8 5v1h16v-1c0-2.761-3.582-5-8-5z"/></svg>
                    </div>
                    <div>
                        <h3 class="pc-section-title uppercase tracking-[0.08em]">INFORMASI PRIBADI</h3>
                        <p class="text-xs" style="color: var(--pc-text-muted);">Data dasar profil akun Anda</p>
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="mb-1.5 block text-xs font-semibold uppercase tracking-[0.12em]" style="color: var(--pc-text-muted);">NAMA LENGKAP</label>
                        <input type="text" name="name" class="pc-input" value="{{ old('name', $user->name) }}" placeholder="Nama lengkap">
                    </div>
                    <div>
                        <label class="mb-1.5 block text-xs font-semibold uppercase tracking-[0.12em]" style="color: var(--pc-text-muted);">EMAIL</label>
                        <input type="email" name="email" class="pc-input" value="{{ old('email', $user->email) }}" placeholder="Email">
                    </div>
                </div>
            </div>

            <div class="pc-card p-6 sm:p-8">
                <div class="mb-6 flex items-center gap-3 pb-4 border-b" style="border-color: var(--pc-border);">
                    <div class="flex h-10 w-10 items-center justify-center rounded-xl" style="background: var(--pc-accent-soft); color: var(--pc-accent);">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 10-8 0v4h8z"/></svg>
                    </div>
                    <div>
                        <h3 class="pc-section-title uppercase tracking-[0.08em]">KEAMANAN AKUN</h3>
                        <p class="text-xs" style="color: var(--pc-text-muted);">Kelola akses dan keamanan profil Anda</p>
                    </div>
                </div>

                <div class="space-y-4">
                    <div>
                        <label class="mb-1.5 block text-xs font-semibold uppercase tracking-[0.12em]" style="color: var(--pc-text-muted);">PASSWORD BARU</label>
                        <input type="password" name="password" class="pc-input" placeholder="Masukkan password baru">
                    </div>
                    <div>
                        <label class="mb-1.5 block text-xs font-semibold uppercase tracking-[0.12em]" style="color: var(--pc-text-muted);">KONFIRMASI PASSWORD</label>
                        <input type="password" name="password_confirmation" class="pc-input" placeholder="Ulangi password baru">
                    </div>
                </div>
            </div>

            <div class="pc-card p-6 sm:p-8">
                <div class="mb-6 flex items-center gap-3 pb-4 border-b" style="border-color: var(--pc-border);">
                    <div class="flex h-10 w-10 items-center justify-center rounded-xl" style="background: rgba(16, 185, 129, 0.12); color: #10b981;">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 12h.01M7 17h.01M12 7h7M12 12h7M12 17h7"/></svg>
                    </div>
                    <div>
                        <h3 class="pc-section-title uppercase tracking-[0.08em]">PREFERENSI TAMPILAN</h3>
                        <p class="text-xs" style="color: var(--pc-text-muted);">Sesuaikan kenyamanan saat memakai aplikasi</p>
                    </div>
                </div>

                <div class="space-y-4">
                    <div class="flex items-center justify-between gap-4 rounded-2xl border p-4" style="border-color: var(--pc-border);">
                        <div>
                            <p class="font-semibold">Mode Gelap</p>
                            <p class="text-xs" style="color: var(--pc-text-muted);">Aktifkan tema gelap untuk tampilan yang lebih nyaman</p>
                        </div>
                        <label class="relative inline-flex items-center cursor-pointer select-none">
                            <input type="checkbox" name="dark_mode" value="1" x-model="darkMode" @change="syncTheme()" class="sr-only peer">
                            <div class="w-12 h-6 bg-slate-200 peer-focus:outline-none rounded-full peer dark:bg-slate-700 peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-blue-600"></div>
                        </label>
                    </div>

                    <div class="flex items-center justify-between gap-4 rounded-2xl border p-4" style="border-color: var(--pc-border);">
                        <div>
                            <p class="font-semibold">Notifikasi Email</p>
                            <p class="text-xs" style="color: var(--pc-text-muted);">Dapatkan update aktivitas akun secara berkala</p>
                        </div>
                        <label class="relative inline-flex items-center cursor-pointer select-none">
                            <input type="checkbox" name="email_notifications" value="1" x-model="emailNotifications" class="sr-only peer">
                            <div class="w-12 h-6 bg-slate-200 peer-focus:outline-none rounded-full peer dark:bg-slate-700 peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-blue-600"></div>
                        </label>
                    </div>
                </div>
            </div>

            @if(auth()->user()?->isAdmin())
            <div class="pc-card p-6 sm:p-8">
                <div class="mb-6 flex items-center gap-3 pb-4 border-b" style="border-color: var(--pc-border);">
                    <div class="flex h-10 w-10 items-center justify-center rounded-xl" style="background: rgba(79, 70, 229, 0.10); color: var(--pc-primary);">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/></svg>
                    </div>
                    <div>
                        <h3 class="pc-section-title uppercase tracking-[0.08em]">SUMBER PENGECEKAN DEFAULT</h3>
                        <p class="text-xs" style="color: var(--pc-text-muted);">Sumber yang otomatis terpilih saat membuka halaman pengecekan</p>
                    </div>
                </div>

                @php
                    $defaultSources = old('default_sources', $settings->default_sources ?? ['web', 'google_scholar', 'openalex', 'crossref', 'crossref_posted', 'publications']);
                    $sourceOptions = [
                        ['key' => 'web', 'label' => 'Web Pages'],
                        ['key' => 'google_scholar', 'label' => 'Google Scholar'],
                        ['key' => 'openalex', 'label' => 'OpenAlex & arXiv'],
                        ['key' => 'crossref', 'label' => 'Crossref Published'],
                        ['key' => 'crossref_posted', 'label' => 'Crossref Posted'],
                        ['key' => 'publications', 'label' => 'Publications (CORE)'],
                        ['key' => 'elsevier', 'label' => 'Elsevier / Scopus'],
                    ];
                @endphp

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                    @foreach($sourceOptions as $source)
                        <label class="flex items-center gap-3 rounded-2xl border p-3" style="border-color: var(--pc-border);">
                            <input type="checkbox" name="default_sources[]" value="{{ $source['key'] }}" @checked(in_array($source['key'], $defaultSources, true)) class="h-4 w-4 rounded border-slate-300 text-indigo-600 focus:ring-indigo-500">
                            <span class="text-sm font-medium">{{ $source['label'] }}</span>
                        </label>
                    @endforeach
                </div>
            </div>
            @endif
        </form>
    </div>
</div>
@endsection

@push('scripts')
<script>
function settingsForm() {
    return {
        darkMode: {{ old('dark_mode', $settings->dark_mode ? 'true' : 'false') }},
        emailNotifications: {{ old('email_notifications', ($settings->email_notifications ?? true) ? 'true' : 'false') }},
        elsevierEnabled: {{ $settings->elsevier_enabled ? 'true' : 'false' }},
        syncTheme: function() {
            localStorage.setItem('naskahcek_dark_mode', this.darkMode);
            if (this.darkMode) {
                document.documentElement.classList.add('dark');
            } else {
                document.documentElement.classList.remove('dark');
            }

            var darkModeUrl = <?php echo json_encode(auth()->user()->isAdmin() && request()->routeIs('admin.*') ? route('admin.settings.dark-mode') : route('user.settings.dark-mode')); ?>;
            fetch(darkModeUrl, {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                    'Accept': 'application/json',
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({
                    dark_mode: this.darkMode
                })
            });
        }
    }
}
</script>
@endpush
