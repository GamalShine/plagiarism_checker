@extends($layout ?? 'layouts.user')

@section('title', 'Cek Plagiarisme')
@section('page-title', 'Cek Plagiarisme')
@section('page-subtitle', 'Unggah dokumen dan pilih sumber pengecekan')

@section('content')
@if($publicMode ?? false)
<nav class="fixed left-0 right-0 top-[42px] z-50 w-full border-b border-slate-200 bg-white shadow-sm">
    <div class="mx-auto flex h-[72px] max-w-[1180px] items-center justify-between px-5 sm:px-6 lg:px-8">
        <a href="{{ route('welcome') }}" class="flex items-center gap-2.5">
            <img src="{{ asset('images/naskahceklogo.png') }}" alt="NaskahCek" class="h-9 w-9 rounded-xl object-cover">
            <span class="text-[17px] font-extrabold tracking-[-0.02em] text-slate-950">NaskahCek</span>
        </a>
        <div class="hidden items-center gap-1 md:flex">
            <a href="{{ route('free.check.index') }}"
                class="rounded-lg bg-blue-50 px-3.5 py-2 text-[13px] font-medium text-blue-600">Cek Plagiasi
                Turnitin</a>
            <a href="{{ route('pricing') }}"
                class="rounded-lg px-3.5 py-2 text-[13px] font-medium text-slate-600 hover:bg-slate-50">Paket
                Harga</a>
            <a href="{{ route('templates.index') }}"
                class="rounded-lg px-3.5 py-2 text-[13px] font-medium text-slate-600 hover:bg-slate-50">Template
                Jurnal</a>
            <a href="{{ route('welcome') }}#faq"
                class="rounded-lg px-3.5 py-2 text-[13px] font-medium text-slate-600 hover:bg-slate-50">Bantuan</a>
        </div>
        <div class="hidden items-center gap-2 md:flex">
            <a href="{{ route('login') }}"
                class="inline-flex items-center rounded-xl bg-blue-600 px-5 py-2.5 text-[13px] font-bold text-white transition hover:bg-blue-700">Masuk</a>
        </div>
        <button type="button" id="mobile-menu-toggle"
            class="inline-flex h-10 w-10 items-center justify-center rounded-xl border border-slate-200 text-slate-700 transition hover:bg-slate-50 md:hidden"
            aria-controls="mobile-menu" aria-expanded="false" aria-label="Buka menu navigasi">
            <svg id="mobile-menu-open-icon" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"
                aria-hidden="true">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
            </svg>
            <svg id="mobile-menu-close-icon" class="hidden h-5 w-5" fill="none" viewBox="0 0 24 24"
                stroke="currentColor" aria-hidden="true">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 6l12 12M6 18L18 6" />
            </svg>
        </button>
    </div>

    <div id="mobile-menu"
        class="pointer-events-none absolute left-0 right-0 top-full max-h-0 overflow-hidden border-t border-slate-200 bg-white px-5 opacity-0 shadow-lg transition-all duration-300 ease-out md:hidden">
        <div class="flex flex-col gap-1 pt-3">
            <a href="{{ route('free.check.index') }}"
                class="rounded-xl bg-blue-50 px-3 py-3 text-sm font-medium text-blue-600 transition hover:bg-blue-100">Cek
                Plagiasi Turnitin</a>
            <a href="{{ route('pricing') }}"
                class="rounded-xl px-3 py-3 text-sm font-medium text-slate-700 transition hover:bg-blue-50 hover:text-blue-600">Paket
                Harga</a>
            <a href="{{ route('templates.index') }}"
                class="rounded-xl px-3 py-3 text-sm font-medium text-slate-700 transition hover:bg-blue-50 hover:text-blue-600">Template
                Jurnal</a>
            <a href="{{ route('welcome') }}#faq"
                class="rounded-xl px-3 py-3 text-sm font-medium text-slate-700 transition hover:bg-blue-50 hover:text-blue-600">Bantuan</a>
            <a href="{{ route('login') }}"
                class="mt-2 mb-3 inline-flex items-center justify-center rounded-xl bg-blue-600 px-5 py-3 text-sm font-bold text-white transition hover:bg-blue-700">Masuk</a>
        </div>
    </div>
</nav>
@endif
<div x-data="plagiarismChecker()"
    class="{{ ($publicMode ?? false) ? 'relative z-10 mx-auto w-full max-w-[1080px] space-y-5 px-5 pb-6 pt-[122px] sm:space-y-6 sm:px-6 sm:pt-[130px] lg:px-8' : '' }}">
    <div x-show="isProcessingPayment" x-cloak class="mb-6 pc-card p-6 sm:p-8">
        <div class="flex items-center gap-4">
            <div
                class="w-12 h-12 rounded-2xl bg-indigo-50 dark:bg-indigo-950/50 text-indigo-600 dark:text-indigo-400 flex items-center justify-center shrink-0">
                <svg class="animate-spin w-7 h-7" fill="none" viewBox="0 0 24 24" aria-hidden="true">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                    <path class="opacity-75" fill="currentColor"
                        d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z">
                    </path>
                </svg>
            </div>
            <div>
                <h2 class="text-lg font-bold"
                    x-text="guestToken ? 'Menunggu Pembayaran' : 'Memproses pengecekan plagiarisme'"></h2>
                <p class="text-sm mt-1" style="color: var(--pc-text-muted);"
                    x-text="guestToken ? 'Pengecekan akan dimulai setelah pembayaran dikonfirmasi' : paymentStatusText">
                </p>
                <div class="mt-3 h-2 w-full max-w-md overflow-hidden rounded-full bg-slate-200 dark:bg-slate-700">
                    <div class="h-full rounded-full bg-indigo-600 transition-all duration-500"
                        :style="`width: ${progress}%`"></div>
                </div>
                <p class="mt-3 text-sm font-semibold text-amber-600 dark:text-amber-400" x-text="paymentStatusText"></p>
            </div>
        </div>
    </div>

    <div class="pc-card p-6 sm:p-8 relative overflow-hidden">
        <form
            action="{{ ($publicMode ?? false) ? (auth()->check() ? route('user.plagiarism.pay') : route('free.plagiarism.check')) : (auth()->user()->isMember() ? route($routePrefix.'.plagiarism.check') : route($routePrefix.'.plagiarism.pay')) }}"
            method="POST" enctype="multipart/form-data" @submit="isChecking = true">
            @csrf

            @if($publicMode ?? false)
            <div class="mb-8 grid gap-4 sm:grid-cols-2">
                <div>
                    <label class="pc-label" for="guest-name">Nama</label>
                    <input id="guest-name" name="name" type="text" required maxlength="120" class="pc-input w-full"
                        value="{{ old('name') }}">
                    @error('name') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label class="pc-label" for="guest-email">Email untuk pembayaran</label>
                    <input id="guest-email" name="email" type="email" required maxlength="190" class="pc-input w-full"
                        value="{{ old('email') }}">
                    @error('email') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror
                </div>
            </div>
            @endif

            {{-- Upload Zone --}}
            <div class="mb-8">
                <label class="pc-label">Unggah Dokumen <span class="text-red-500">*</span></label>
                <div class="pc-upload-zone group" :class="{ 'active': dragover }" @dragover.prevent="dragover = true"
                    @dragleave.prevent="dragover = false" @drop.prevent="dragover = false; handleDrop($event)">

                    <div class="w-16 h-16 rounded-2xl flex items-center justify-center mb-4 transition-all duration-300 shadow-xs"
                        :class="fileName ? 'bg-emerald-50 dark:bg-emerald-950/40 text-emerald-600 dark:text-emerald-400' : 'bg-indigo-50 dark:bg-indigo-950/40 text-indigo-600 dark:text-indigo-400 group-hover:scale-110'">
                        <svg x-show="!fileName" class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                                d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12" />
                        </svg>
                        <svg x-show="fileName" x-cloak class="w-8 h-8" fill="none" stroke="currentColor"
                            viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                    </div>

                    <label for="file-upload" class="cursor-pointer">
                        <span class="pc-btn pc-btn-primary shadow-sm hover:shadow transition-all"
                            x-text="fileName ? 'Ganti File Dokumen' : 'Pilih File Dokumen'"></span>
                        <input id="file-upload" name="file" type="file" class="sr-only" accept=".pdf,.docx,.txt"
                            @change="handleFileChange" required>
                    </label>

                    <p class="text-sm mt-3" style="color: var(--pc-text-muted);" x-show="!fileName">
                        atau drag & drop file ke area ini
                    </p>

                    <div x-show="fileName" x-cloak
                        class="mt-3 inline-flex items-center gap-2 px-3 py-1.5 rounded-xl bg-emerald-50 dark:bg-emerald-950/40 border border-emerald-200 dark:border-emerald-800/60 text-xs font-semibold text-emerald-700 dark:text-emerald-300">
                        <svg class="w-4 h-4 text-emerald-500 shrink-0" fill="none" stroke="currentColor"
                            viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                        </svg>
                        <span x-text="fileName" class="truncate max-w-xs sm:max-w-md"></span>
                    </div>

                    <p class="text-xs mt-2" style="color: var(--pc-text-subtle);" x-show="!fileName">
                        Format: PDF, DOCX, TXT — Maks. 50MB. Upload dokumen karya tulis asli.
                    </p>
                </div>
                @error('file') <p class="text-red-500 text-xs mt-2">{{ $message }}</p> @enderror
            </div>

            @if (($routePrefix ?? 'user') === 'admin')
            {{-- Admin dapat memilih sumber yang digunakan untuk pengecekan --}}
            <div
                class="mb-8 rounded-xl border border-blue-200 bg-blue-50/60 p-4 dark:border-blue-900/60 dark:bg-blue-950/20">
                <div class="mb-4 flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                    <div>
                        <div class="flex items-center gap-2">
                            <span
                                class="text-xs font-bold uppercase tracking-wider text-slate-900 dark:text-slate-100">Cakupan
                                Basis Data Pengecekan</span>
                        </div>
                    </div>
                    <button type="button" @click="selectAll = !selectAll; toggleAll()"
                        class="pc-btn-soft pc-btn-sm text-xs font-semibold">
                        <span x-text="selectAll ? 'Batalkan Semua' : 'Pilih Semua'"></span>
                    </button>
                </div>

                @php
                $sourceOptions = [
                ['key' => 'web', 'label' => 'Web Pages', 'hint' => 'Halaman web umum', 'color' => 'blue', 'icon' => '
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.7"
                    d="M3 12h18M3 12a9 9 0 0 0 18 0M3 12a9 9 0 0 1 18 0M12 3a14.5 14.5 0 0 1 0 18M12 3a14.5 14.5 0 0 0 0 18" />
                '],
                ['key' => 'google_scholar', 'label' => 'Google Scholar', 'hint' => 'Literatur akademik', 'color' =>
                'red', 'icon' => '
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.7"
                    d="M3 10.5 12 4l9 6.5-9 6-9-6Zm4 2.5v4c2.8 2 7.2 2 10 0v-4M21 10.5v6" />'],
                ['key' => 'elsevier', 'label' => 'Scopus / Elsevier', 'hint' => 'Jurnal terindeks', 'color' => 'orange',
                'icon' => '
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.7"
                    d="M6 4h12a2 2 0 0 1 2 2v12a2 2 0 0 1-2 2H6a2 2 0 0 1-2-2V6a2 2 0 0 1 2-2Zm3 4h6M9 12h6M9 16h3" />
                '],
                ['key' => 'openalex', 'label' => 'OpenAlex', 'hint' => 'Katalog riset terbuka', 'color' => 'violet',
                'icon' => '
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.7"
                    d="M12 3 4 7.5 12 12l8-4.5L12 3Zm-8 9 8 4.5 8-4.5M4 16.5l8 4.5 8-4.5" />'],
                ['key' => 'crossref', 'label' => 'Crossref', 'hint' => 'Metadata publikasi', 'color' => 'emerald',
                'icon' => '
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.7" d="M4 7h16M4 12h16M4 17h16" />
                '],
                ['key' => 'crossref_posted', 'label' => 'Crossref Posted Content', 'hint' => 'Naskah pra-publikasi',
                'color' => 'cyan', 'icon' => '
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.7"
                    d="M6 3h8l4 4v14H6a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2Zm8 0v5h4M8 13h8M8 17h5" />'],
                ['key' => 'publications', 'label' => 'CORE Repository / Publications', 'hint' => 'Repositori publikasi',
                'color' => 'amber', 'icon' => '
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.7"
                    d="M4 19.5A2.5 2.5 0 0 1 6.5 17H20M6.5 3H20v18H6.5A2.5 2.5 0 0 1 4 18.5v-13A2.5 2.5 0 0 1 6.5 3ZM8 7h8M8 11h8" />
                '],
                ];
                @endphp

                <div class="grid grid-cols-1 gap-3 sm:grid-cols-2">
                    @foreach($sourceOptions as $source)
                    <label
                        class="group flex cursor-pointer items-center gap-3 rounded-2xl border border-slate-200 bg-white p-3 transition duration-200 hover:-translate-y-0.5 hover:border-blue-300 hover:shadow-md dark:border-slate-700 dark:bg-slate-900/50"
                        :class="sources.includes('{{ $source['key'] }}') ? 'border-{{ $source['color'] }}-400 bg-{{ $source['color'] }}-50/70 ring-2 ring-{{ $source['color'] }}-200 dark:bg-{{ $source['color'] }}-950/20 dark:ring-{{ $source['color'] }}-900/50' : ''">
                        <input type="checkbox" name="sources[]" value="{{ $source['key'] }}" x-model="sources"
                            @change="selectAll = sources.length === allSourceKeys.length"
                            class="h-4 w-4 shrink-0 rounded border-slate-300 text-blue-600 focus:ring-blue-500">
                        <span class="min-w-0 flex-1">
                            <span
                                class="block truncate text-xs font-bold text-slate-800 dark:text-slate-100">{{ $source['label'] }}</span>
                            <span
                                class="mt-0.5 block text-[10px] font-medium text-slate-400 dark:text-slate-500">{{ $source['hint'] }}</span>
                        </span>
                        <span
                            class="hidden rounded-full bg-emerald-50 px-2 py-1 text-[9px] font-bold uppercase tracking-wide text-emerald-600 sm:inline-flex dark:bg-emerald-950/40 dark:text-emerald-300">Aktif</span>
                    </label>
                    @endforeach
                </div>
                <p x-show="sources.length === 0" x-cloak class="mt-3 text-xs font-semibold text-red-600">Pilih minimal
                    satu sumber pengecekan.</p>
            </div>
            @else
            {{-- Hidden Inputs agar semua sumber tetap terkirim saat form user disubmit --}}
            @foreach(['web', 'google_scholar', 'elsevier', 'openalex', 'crossref', 'crossref_posted', 'publications'] as $sKey)
            <input type="hidden" name="sources[]" value="{{ $sKey }}">
            @endforeach

            {{-- Coverage Database Bar / Info Strip yang Clean & Professional --}}
            <div
                class="mb-8 p-4 rounded-xl border border-slate-200/80 bg-slate-50/70 dark:bg-slate-800/50 dark:border-slate-700/80">
                <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3">
                    <div class="flex items-center gap-3">
                        <div>
                            <div class="flex items-center gap-2">
                                <span
                                    class="text-xs font-bold text-slate-900 dark:text-slate-100 uppercase tracking-wider">Proses
                                    Pengecekan Plagiarisme</span>
                                <span
                                    class="px-2 py-0.5 rounded text-[10px] font-bold bg-emerald-50 text-emerald-700 dark:bg-emerald-950/50 dark:text-emerald-300 border border-emerald-200 dark:border-emerald-800"
                                    x-text="guestToken ? 'Sedang Berjalan' : 'Siap Dimulai'">
                                    Siap Dimulai
                                </span>
                            </div>
                            <p class="hidden text-xs text-slate-500 dark:text-slate-400 mt-0.5 sm:block">
                                Dokumen akan dianalisis, dibandingkan dengan sumber referensi, dan disusun menjadi
                                laporan hasil pengecekan.
                            </p>
                        </div>
                    </div>

                    <div class="hidden text-[11px] text-slate-400 dark:text-slate-500 font-medium shrink-0 sm:block">
                        Status diperbarui otomatis
                    </div>
                </div>
            </div>
            @endif

            <div class="flex flex-col sm:flex-row items-center justify-between gap-4 pt-4 border-t"
                style="border-color: var(--pc-border);">
                <p class="text-xs text-slate-500 dark:text-slate-400">
                    Pastikan file yang diunggah adalah naskah dokumen asli.
                </p>
                <button type="submit"
                    class="pc-btn-primary pc-btn-lg w-full sm:w-auto shadow-md hover:shadow-lg transition-all"
                    :disabled="isChecking || isDetectingChapters || !fileName">
                    <svg x-show="!isChecking" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                    </svg>
                    <svg x-show="isChecking" x-cloak class="animate-spin w-5 h-5" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4" />
                        <path class="opacity-75" fill="currentColor"
                            d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z" />
                    </svg>
                    <span x-text="isChecking ? 'Memproses Pengecekan...' : 'Cek Plagiarisme'"></span>
                </button>
            </div>
        </form>

        {{-- Loading overlay --}}
        <div x-show="isChecking" x-cloak x-transition.opacity
            class="absolute inset-0 z-10 backdrop-blur-sm flex flex-col items-center justify-center rounded-3xl bg-white/85 dark:bg-slate-900/85">
            <div class="text-center px-6 max-w-sm">
                <div
                    class="w-16 h-16 mx-auto mb-5 p-3 rounded-2xl bg-indigo-50 dark:bg-indigo-950/50 text-indigo-600 dark:text-indigo-400">
                    <svg class="animate-spin w-full h-full" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4" />
                        <path class="opacity-75" fill="currentColor"
                            d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z" />
                    </svg>
                </div>
                <h3 class="text-lg font-bold mb-1.5 text-slate-900 dark:text-white">Menganalisis Dokumen</h3>
                <p class="text-sm font-medium text-indigo-600 dark:text-indigo-400" x-text="statusText"></p>
                <p class="text-xs mt-2" style="color: var(--pc-text-subtle);">Mohon tunggu, hasil akan otomatis
                    ditampilkan setelah proses selesai.</p>
            </div>
        </div>
    </div>

    @if($publicMode ?? false)
    <section x-show="guestResultReady" x-cloak class="grid grid-cols-1 gap-4 lg:grid-cols-12" aria-live="polite">
        <div
            class="lg:col-span-8 flex h-full flex-col rounded-2xl border border-slate-200/80 bg-white p-5 shadow-lg dark:border-slate-700/80 dark:bg-slate-800">
            <div class="mb-4 flex items-center justify-between border-b border-slate-100 pb-3 dark:border-slate-700/60">
                <div>
                    <p class="text-xs font-bold uppercase tracking-wider text-indigo-600">Hasil Pengecekan</p>
                    <h2 class="mt-1 text-lg font-black text-slate-900 dark:text-white">Ringkasan Dokumen</h2>
                </div>
                <span
                    class="rounded-full bg-emerald-50 px-2.5 py-1 text-[10px] font-bold text-emerald-700 dark:bg-emerald-950/30 dark:text-emerald-300">Selesai</span>
            </div>
            <div class="grid grid-cols-2 gap-3.5">
                <div
                    class="relative rounded-xl border border-slate-100 bg-slate-50/80 p-4 text-center dark:border-slate-700/70 dark:bg-slate-800/80">
                    <span class="block text-[10px] font-extrabold uppercase tracking-wider text-slate-400">Overall
                        Similarity</span>
                    <div class="mt-1 text-4xl font-black" :style="`color: ${similarityColor(guestResult.similarity)}`">
                        <span x-text="guestResult.similarity"></span><span class="text-lg">%</span></div>
                    <span class="mt-2 inline-flex items-center gap-1.5 rounded-full px-2.5 py-0.5 text-[10px] font-bold"
                        :style="`background-color: ${similarityColor(guestResult.similarity)}15; color: ${similarityColor(guestResult.similarity)}`"><span
                            class="h-1.5 w-1.5 rounded-full"
                            :style="`background-color: ${similarityColor(guestResult.similarity)}`"></span><span
                            x-text="similarityLabel(guestResult.similarity)"></span></span>
                </div>
                <div
                    class="rounded-xl border border-slate-100 bg-slate-50/80 p-4 text-center dark:border-slate-700/70 dark:bg-slate-800/80">
                    <span class="block text-[10px] font-extrabold uppercase tracking-wider text-slate-400">Original
                        Text</span>
                    <div class="mt-1 text-4xl font-black text-slate-800 dark:text-slate-100"><span
                            x-text="originalTextScore()"></span><span class="text-lg">%</span></div>
                    <span
                        class="mt-2 inline-flex rounded-full bg-emerald-50 px-2.5 py-0.5 text-[10px] font-semibold text-emerald-600 dark:bg-emerald-950/40 dark:text-emerald-400">Tingkat
                        Originalitas</span>
                </div>
            </div>
            <div class="mt-auto flex justify-end pt-5">
                <a href="#" @click.prevent="downloadGuestPdf"
                    class="inline-flex items-center gap-1.5 rounded-lg bg-indigo-600 px-3 py-2 text-xs font-bold text-white shadow-sm transition hover:bg-indigo-700">
                    <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" />
                    </svg>
                    Download PDF
                </a>
            </div>
        </div>
        <div class="lg:col-span-4 flex h-full flex-col gap-3">
            <div
                class="h-full min-h-0 rounded-2xl border border-slate-200/80 bg-white p-5 shadow-lg dark:border-slate-700/80 dark:bg-slate-800">
                <div class="mb-3 flex items-center gap-2 border-b border-slate-100 pb-3 dark:border-slate-700/60"><span
                        class="h-4 w-2 rounded-full bg-indigo-600"></span>
                    <h3 class="text-xs font-bold uppercase tracking-wider text-slate-800 dark:text-slate-200">Primary
                        Sources</h3>
                </div>
                <div class="max-h-64 space-y-2 overflow-y-auto">
                    <template x-for="(source, index) in guestResult.sources" :key="source.url + source.title">
                        <div
                            class="flex items-start gap-3 rounded-xl border border-slate-200/70 p-2 dark:border-slate-700/70">
                            <span
                                class="flex h-5 w-5 shrink-0 items-center justify-center rounded-md text-[10px] font-bold text-white"
                                :style="`background-color: ${source.color_code}`" x-text="index + 1"></span>
                            <div class="min-w-0 flex-1"><a
                                    class="block truncate text-xs font-semibold text-slate-800 hover:text-indigo-600 dark:text-slate-200"
                                    :href="source.url !== '#' ? source.url : null" target="_blank" rel="noopener"
                                    x-text="source.title || source.source_label"></a>
                                <div class="mt-1 flex items-center gap-2 text-[10px] text-slate-400"><span
                                        class="truncate" x-text="source.source_label"></span><span>&bull;</span><span
                                        class="shrink-0 font-semibold text-slate-600 dark:text-slate-300"
                                        x-text="source.matched_words + ' words'"></span><span>&bull;</span><strong
                                        class="shrink-0 text-indigo-600" x-text="source.percentage"></strong></div>
                            </div>
                        </div>
                    </template>
                    <p x-show="guestResult.hidden_sources_count > 0"
                        class="pt-2 text-center text-xs italic text-slate-400"
                        x-text="'dan ' + guestResult.hidden_sources_count + ' sumber lainnya'"></p>
                    <p x-show="guestResult.sources.length === 0" class="py-4 text-center text-xs italic text-slate-400">
                        Tidak ditemukan sumber kemiripan</p>
                </div>
            </div>
        </div>
        <div x-show="false" class="hidden">
            <div class="mb-3 flex items-center gap-2 border-b border-slate-100 pb-3 dark:border-slate-700/60"><span
                    class="h-4 w-2 rounded-full bg-rose-500"></span>
                <h3 class="text-xs font-bold uppercase tracking-wider text-slate-800 dark:text-slate-200">Frase
                    Terdeteksi</h3><span class="text-[11px] text-slate-400"
                    x-text="guestResult.highlights.length + ' frase'"></span>
            </div>
            <div class="grid gap-2 md:grid-cols-2">
                <template x-for="highlight in guestResult.highlights.slice(0, 20)"
                    :key="highlight.text + highlight.percentage">
                    <div class="rounded-xl border border-slate-200/70 p-2.5 dark:border-slate-700/70">
                        <div class="mb-1 flex items-center justify-between gap-2"><span
                                class="truncate text-[10px] font-semibold text-slate-500"
                                x-text="highlight.source_label"></span><span
                                class="rounded bg-slate-100 px-1.5 py-0.5 text-[9px] font-bold text-slate-600"
                                x-text="highlight.percentage + '% match'"></span></div>
                        <p class="line-clamp-2 border-l-2 border-slate-200 pl-1 text-[11px] italic text-slate-600 dark:border-slate-700 dark:text-slate-300"
                            x-text="'&quot;' + highlight.text + '&quot;'"></p>
                    </div>
                </template>
            </div>
        </div>
    </section>
    @endif
</div>
@endsection

@push('styles')
<style>
@media (max-width: 767px) {
    nav {
        height: 62px;
    }

    nav>div {
        height: 62px !important;
    }

    nav>#mobile-menu {
        height: auto !important;
    }
}
</style>
@endpush

@push('scripts')
<script>
(function() {
    const menuToggle = document.getElementById('mobile-menu-toggle');
    const mobileMenu = document.getElementById('mobile-menu');
    const openIcon = document.getElementById('mobile-menu-open-icon');
    const closeIcon = document.getElementById('mobile-menu-close-icon');

    if (!menuToggle || !mobileMenu || !openIcon || !closeIcon) return;

    function closeMenu() {
        mobileMenu.classList.add('max-h-0', 'pointer-events-none', 'opacity-0');
        mobileMenu.classList.remove('max-h-[500px]', 'opacity-100');
        openIcon.classList.remove('hidden');
        closeIcon.classList.add('hidden');
        menuToggle.setAttribute('aria-expanded', 'false');
        menuToggle.setAttribute('aria-label', 'Buka menu navigasi');
    }

    menuToggle.addEventListener('click', function() {
        const isOpen = mobileMenu.classList.contains('max-h-0');
        if (isOpen) {
            mobileMenu.classList.remove('max-h-0', 'pointer-events-none', 'opacity-0');
            mobileMenu.classList.add('max-h-[500px]', 'opacity-100');
            openIcon.classList.add('hidden');
            closeIcon.classList.remove('hidden');
            menuToggle.setAttribute('aria-expanded', 'true');
            menuToggle.setAttribute('aria-label', 'Tutup menu navigasi');
        } else {
            closeMenu();
        }
    });

    mobileMenu.querySelectorAll('a').forEach(function(link) {
        link.addEventListener('click', closeMenu);
    });
})();

function plagiarismChecker() {
    return {
        dragover: false,
        fileName: '',
        chapters: [],
        detectedChapters: [],
        isDetectingChapters: false,
        selectAllChapters: false,
        sources: @js(data_get($settings, 'default_sources') ?: ['web', 'google_scholar', 'elsevier', 'openalex',
            'crossref', 'crossref_posted', 'publications'
        ]),
        selectAll: false,
        isChecking: false,
        isProcessingPayment: false,
        paymentOrderId: @js(request('payment')),
        guestToken: @js($guestToken ?? null),
        guestPaymentBase: @js(rtrim(request()->getBaseUrl(), '/')),
        guestResultReady: false,
        guestResult: {
            similarity: 0,
            total_sentences: 0,
            matched_sentences: 0,
            sources: [],
            highlights: [],
            highlights_count: 0,
            hidden_sources_count: 0,
            export_url: ''
        },
        paymentStatusText: 'Menunggu konfirmasi pembayaran...',
        progress: 0,
        progressTimer: null,
        paymentPollInterval: null,
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
            this.$watch('isChecking', v => {
                if (v) this.startStatusRotation();
            });

            if (this.paymentOrderId) {
                this.isProcessingPayment = true;
                this.startProgress();
                this.startPaymentPolling();
            }
            if (this.guestToken) {
                this.isProcessingPayment = true;
                this.startProgress();
                this.startGuestPolling();
            }
        },

        startProgress() {
            this.progressTimer = setInterval(() => {
                if (this.progress < 92) {
                    this.progress += this.progress < 25 ? 2 : 1;
                }
            }, 1500);
        },

        similarityColor(score) {
            score = Number(score || 0);
            if (score <= 24) return '#16a34a';
            if (score <= 49) return '#ca8a04';
            if (score <= 74) return '#ea580c';
            return '#dc2626';
        },

        similarityLabel(score) {
            score = Number(score || 0);
            if (score <= 24) return 'Original';
            if (score <= 49) return 'Sedang';
            if (score <= 74) return 'Tinggi';
            return 'Plagiat';
        },

        originalTextScore() {
            return Math.max(0, Math.min(100, 100 - Number(this.guestResult.similarity || 0)));
        },

        downloadGuestPdf() {
            if (!this.guestResult.export_url) {
                this.paymentStatusText = 'Link download belum tersedia. Muat ulang halaman hasil lalu coba lagi.';
                return;
            }

            window.location.assign(this.guestResult.export_url);
        },

        startPaymentPolling() {
            this.pollPaymentStatus();
            this.paymentPollInterval = setInterval(() => this.pollPaymentStatus(), 3000);
        },

        startGuestPolling() {
            this.pollGuestStatus();
            this.paymentPollInterval = setInterval(() => this.pollGuestStatus(), 3000);
        },

        async pollGuestStatus() {
            try {
                const response = await fetch(
                    `${this.guestPaymentBase}/guest-payment/${encodeURIComponent(this.guestToken)}/status`, {
                        headers: {
                            Accept: 'application/json'
                        }
                    });
                if (!response.ok) return;
                const data = await response.json();
                if (data.status === 'failed' || data.plagiarism_status === 'failed') {
                    clearInterval(this.paymentPollInterval);
                    clearInterval(this.progressTimer);
                    this.isProcessingPayment = false;
                    this.paymentStatusText = 'Pembayaran atau proses pengecekan gagal.';
                    return;
                }
                if (data.plagiarism_status === 'completed') {
                    clearInterval(this.paymentPollInterval);
                    clearInterval(this.progressTimer);
                    this.progress = 100;
                    this.guestResult = data;
                    this.guestResultReady = true;
                    this.isProcessingPayment = false;
                    return;
                }
                this.paymentStatusText = data.status === 'paid' ?
                    'Pembayaran diterima. Sistem sedang menganalisis dokumen...' :
                    'Menunggu konfirmasi pembayaran...';
            } catch (error) {
                this.paymentStatusText = 'Menghubungkan ke status pembayaran...';
            }
        },

        async pollPaymentStatus() {
            try {
                const response = await fetch(`{{ url('/user/payment') }}/${this.paymentOrderId}/status`);
                const data = await response.json();

                if (data.status === 'failed' || data.plagiarism_status === 'failed') {
                    clearInterval(this.paymentPollInterval);
                    clearInterval(this.progressTimer);
                    this.paymentStatusText = 'Pembayaran atau proses pengecekan gagal.';
                    return;
                }

                if (data.status !== 'paid') {
                    this.paymentStatusText = 'Menunggu konfirmasi pembayaran...';
                    return;
                }

                if (data.plagiarism_status === 'completed' && data.result_url) {
                    clearInterval(this.paymentPollInterval);
                    clearInterval(this.progressTimer);
                    this.progress = 100;
                    window.location.href = data.result_url;
                    return;
                }

                if (data.status === 'paid') {
                    this.progress = Math.max(this.progress, 25);
                }
                this.paymentStatusText = 'Pembayaran diterima. Sistem sedang menganalisis dokumen...';
            } catch (error) {
                this.paymentStatusText = 'Menghubungkan ke status pengecekan...';
            }
        },

        async handleFileChange(e) {
            const files = e.target.files;
            if (files.length > 0) {
                this.fileName = files[0].name;
                this.detectedChapters = [];
                this.chapters = [];
                this.selectAllChapters = false;
            } else {
                this.fileName = '';
                this.detectedChapters = [];
                this.chapters = [];
                this.selectAllChapters = false;
            }
        },

        async handleDrop(e) {
            const files = e.dataTransfer.files;
            if (files.length > 0) {
                document.getElementById('file-upload').files = files;
                this.fileName = files[0].name;
                this.detectedChapters = [];
                this.chapters = [];
                this.selectAllChapters = false;
            }
        },

        async detectChapters(file) {
            this.detectedChapters = [];
            this.chapters = [];
            this.selectAllChapters = false;
            return;
        },

        toggleAll() {
            this.sources = this.selectAll ? [...this.allSourceKeys] : [];
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
                if (!this.isChecking) {
                    clearInterval(interval);
                    return;
                }
                idx = (idx + 1) % steps.length;
                this.statusText = steps[idx];
            }, 2500);
        }
    }
}
</script>
@endpush
