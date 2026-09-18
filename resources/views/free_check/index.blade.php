@extends('layouts.landing')

@section('title', 'Coba Cek Plagiarisme Gratis — NaskahCek')

@section('content')
<div class="min-h-screen bg-[#fcfdff] text-slate-900 selection:bg-blue-600 selection:text-white pt-[72px]"
    x-data="freePlagiarismApp()">

    {{-- NAVBAR (FIXED TOP) --}}
    <nav class="landing-nav fixed left-0 right-0 top-0 z-50 w-full border-b border-slate-200 bg-white shadow-sm">
        <div class="mx-auto flex h-[72px] max-w-[1320px] items-center justify-between px-5 sm:px-6 lg:px-8">
            <a href="{{ route('welcome') }}" class="flex items-center gap-2.5">
                <img src="{{ asset('images/naskahceklogo.png') }}" alt="NaskahCek"
                    class="h-9 w-9 rounded-xl object-cover">
                <span class="text-[17px] font-extrabold tracking-[-0.02em] text-slate-950">NaskahCek</span>
            </a>

            <div class="hidden items-center gap-1 md:flex">
                <a href="{{ route('free.check.index') }}"
                    class="rounded-lg bg-blue-50 px-3.5 py-2 text-[13px] font-medium text-blue-600 transition hover:bg-blue-100">Cek
                    Plagiasi Turnitin</a>
                <a href="{{ route('pricing') }}"
                    class="rounded-lg px-3.5 py-2 text-[13px] font-medium text-slate-600 transition hover:bg-slate-50 hover:text-blue-600">Paket
                    Harga</a>
                <a href="{{ route('templates.index') }}"
                    class="rounded-lg px-3.5 py-2 text-[13px] font-medium text-slate-600 transition hover:bg-slate-50 hover:text-blue-600">Template
                    Jurnal</a>
                <a href="{{ route('welcome') }}#faq"
                    class="rounded-lg px-3.5 py-2 text-[13px] font-medium text-slate-600 transition hover:bg-slate-50 hover:text-blue-600">Bantuan</a>
            </div>

            <div class="flex items-center gap-2">
                <a href="{{ route('login') }}"
                    class="hidden items-center rounded-lg bg-blue-600 px-4 py-2.5 text-[13px] font-bold text-white transition hover:bg-blue-700 md:inline-flex">Masuk</a>
            </div>

            <button type="button" id="mobile-menu-toggle"
                class="inline-flex h-10 w-10 items-center justify-center rounded-xl border border-slate-200 text-slate-700 transition hover:bg-slate-50 md:hidden"
                aria-controls="mobile-menu" aria-expanded="false" aria-label="Buka menu navigasi">
                <svg id="mobile-menu-open-icon" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"
                    aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                </svg>
                <svg id="mobile-menu-close-icon" class="hidden h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"
                    aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 6l12 12M6 18L18 6" />
                </svg>
            </button>
        </div>

        <div id="mobile-menu"
            class="pointer-events-none absolute left-0 right-0 top-full max-h-0 overflow-hidden border-t border-slate-200 bg-white px-5 opacity-0 shadow-lg transition-all duration-300 ease-out md:hidden">
            <div class="flex flex-col gap-1 py-2">
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
                    class="mb-3 mt-2 inline-flex items-center justify-center rounded-xl bg-blue-600 px-5 py-3 text-sm font-bold text-white transition hover:bg-blue-700">Masuk</a>
            </div>
        </div>
    </nav>

    <main class="py-10 sm:py-14 relative overflow-hidden">
        {{-- Background Light Mesh Gradients --}}
        <div
            class="pointer-events-none absolute -left-40 top-0 h-[500px] w-[500px] rounded-full bg-blue-100/50 blur-3xl">
        </div>
        <div
            class="pointer-events-none absolute -right-40 top-1/4 h-[500px] w-[500px] rounded-full bg-sky-100/60 blur-3xl">
        </div>

        <div class="relative mx-auto max-w-[1240px] px-5 sm:px-6 lg:px-8">

            {{-- Header Title --}}
            <div class="text-center max-w-2xl mx-auto mb-8">
                <h1 class="text-3xl sm:text-4xl font-black tracking-tight text-slate-950">
                    Cek Plagiarisme Gratis
                </h1>
                <p class="mt-2.5 text-sm sm:text-base text-slate-600">
                    Tempelkan teks artikel atau naskah akademik Anda di bawah ini (maksimal 5.000 kata per pengecekan).
                </p>
            </div>

            <div class="space-y-8">

                {{-- GRID AREA: INPUT TEKS (KIRI) & FITUR PRO TERKUNCI (KANAN) --}}
                <div class="grid grid-cols-1 lg:grid-cols-12 gap-6 items-stretch">

                    {{-- KOLOM TEKS EDITOR (LEBAR 8 KOLOM) --}}
                    <div
                        class="lg:col-span-8 xl:col-span-8 rounded-2xl border border-slate-200 bg-white p-6 sm:p-7 shadow-sm flex flex-col justify-between">
                        <div>
                            {{-- Toolbar atas: Badge status / info di kiri & Word counter di kanan --}}
                            <div class="flex items-center justify-between mb-3 pb-2.5 border-b border-slate-100 gap-3">
                                <div class="flex items-center gap-2">
                                    <span
                                        class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-lg text-xs font-semibold bg-blue-50 text-blue-700 border border-blue-100">
                                        <svg class="w-3.5 h-3.5 text-blue-600" fill="none" stroke="currentColor"
                                            viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                        </svg>
                                        <span>Kolom Teks Naskah</span>
                                    </span>
                                    <span class="hidden sm:inline-flex items-center gap-1 text-[11px] text-slate-400">
                                        <svg class="w-3.5 h-3.5 text-emerald-500" fill="none" stroke="currentColor"
                                            viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                                        </svg>
                                        Siap dianalisis
                                    </span>
                                </div>

                                <span class="text-xs font-semibold px-2.5 py-1 rounded-lg bg-slate-100"
                                    :class="wordCount > 5000 ? 'text-red-600 font-bold bg-red-50 ring-1 ring-red-200' : 'text-slate-600'"
                                    x-text="`${wordCount} / 5.000 Kata`">
                                </span>
                            </div>

                            <div class="relative">
                                <textarea x-model="text" @input="updateWordCount()"
                                    placeholder="Tempelkan (paste) teks karya tulis, makalah, atau naskah Anda di sini untuk memeriksa tingkat kemiripan plagiarisme secara langsung..."
                                    class="w-full min-h-[350px] lg:min-h-[365px] rounded-xl border border-slate-200 p-4 text-sm leading-relaxed text-slate-800 placeholder-slate-400 focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-500/20 resize-y pc-scrollbar"
                                    :disabled="isChecking"></textarea>
                            </div>
                        </div>

                        {{-- Toolbar bawah --}}
                        <div
                            class="mt-6 flex flex-col sm:flex-row items-stretch sm:items-center justify-between gap-4 pt-4 border-t border-slate-100">
                            {{-- Tombol Bersihkan Teks di Kiri Bawah --}}
                            <div class="flex items-center">
                                <button type="button" @click="text = ''; updateWordCount(); result = null"
                                    class="inline-flex items-center gap-1.5 text-xs font-semibold text-slate-500 hover:text-red-600 transition cursor-pointer"
                                    x-show="text.length > 0 && !isChecking">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                    </svg>
                                    Bersihkan Teks
                                </button>
                            </div>

                            {{-- Grup Tombol Kanan: Fitur URL & Drive di samping kiri tombol Periksa Plagiarisme --}}
                            <div class="flex flex-wrap items-center justify-end gap-2.5">
                                {{-- Tombol Cek via URL --}}
                                <button type="button" @click="openModal('url')" :disabled="isChecking || isFetchingUrl"
                                    class="inline-flex items-center justify-center gap-2 rounded-xl border border-slate-200 bg-white px-3.5 py-2.5 text-xs font-bold text-slate-700 shadow-xs hover:bg-slate-50 hover:border-slate-300 hover:text-blue-600 transition active:scale-[0.98] disabled:opacity-50 disabled:pointer-events-none cursor-pointer">
                                    <svg class="w-4 h-4 text-slate-500 group-hover:text-blue-600" fill="none"
                                        stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M13.828 10.172a4 4 0 00-5.656 0l-4 4a4 4 0 105.656 5.656l1.102-1.101m-.758-4.899a4 4 0 005.656 0l4-4a4 4 0 00-5.656-5.656l-1.1 1.1" />
                                    </svg>
                                    <span>Cek via URL</span>
                                </button>

                                {{-- Tombol dari Drive --}}
                                <button type="button" @click="openModal('gdrive')"
                                    :disabled="isChecking || isFetchingUrl"
                                    class="inline-flex items-center justify-center gap-2 rounded-xl border border-slate-200 bg-white px-3.5 py-2.5 text-xs font-bold text-slate-700 shadow-xs hover:bg-slate-50 hover:border-slate-300 hover:text-blue-600 transition active:scale-[0.98] disabled:opacity-50 disabled:pointer-events-none cursor-pointer">
                                    <svg class="w-4 h-4 text-emerald-600" viewBox="0 0 24 24" fill="currentColor">
                                        <path
                                            d="M19.35 10.04C18.67 6.59 15.64 4 12 4 9.11 4 6.6 5.64 5.35 8.04 2.34 8.36 0 10.91 0 14c0 3.31 2.69 6 6 6h13c2.76 0 5-2.24 5-5 0-2.64-2.05-4.78-4.65-4.96zM19 18H6c-2.21 0-4-1.79-4-4 0-2.05 1.53-3.76 3.56-3.97l1.07-.11.5-.95C8.08 7.14 9.94 6 12 6c2.62 0 4.88 1.86 5.39 4.43l.3 1.5 1.53.11c1.56.1 2.78 1.41 2.78 2.96 0 1.65-1.35 3-3 3z" />
                                    </svg>
                                    <span>Dari Drive</span>
                                </button>

                                {{-- Tombol Periksa Plagiarisme --}}
                                <button type="button" @click="startCheck()"
                                    :disabled="isChecking || isFetchingUrl || wordCount === 0 || wordCount > 5000"
                                    class="inline-flex items-center justify-center gap-2 rounded-xl bg-blue-600 px-5 py-2.5 text-xs sm:text-sm font-bold text-white shadow-lg shadow-blue-600/25 transition-all hover:-translate-y-0.5 hover:bg-blue-700 active:translate-y-0 disabled:opacity-50 disabled:pointer-events-none disabled:shadow-none cursor-pointer">
                                    <svg x-show="!isChecking" class="w-4 h-4 sm:w-4.5 sm:h-4.5" fill="none"
                                        stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5"
                                            d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                                    </svg>
                                    <svg x-show="isChecking" x-cloak class="animate-spin w-4 h-4 sm:w-4.5 sm:h-4.5"
                                        fill="none" viewBox="0 0 24 24">
                                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor"
                                            stroke-width="4" />
                                        <path class="opacity-75" fill="currentColor"
                                            d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z" />
                                    </svg>
                                    <span class="font-extrabold tracking-wide"
                                        x-text="isChecking ? 'Sedang Memeriksa Teks...' : 'Periksa Plagiarisme'"></span>
                                </button>
                            </div>
                        </div>
                    </div>

                    {{-- SIDEBAR KANAN: FITUR PRO TERKUNCI (LEBAR 4 KOLOM) --}}
                    <div
                        class="lg:col-span-4 xl:col-span-4 rounded-2xl border border-slate-200 bg-white p-6 sm:p-7 shadow-sm flex flex-col justify-between">
                        <div>
                            {{-- Header Fitur Lanjutan --}}
                            <div class="flex items-center justify-between pb-3 mb-4 border-b border-slate-100">
                                <div class="flex items-center gap-2">
                                    <div class="w-2 h-4 rounded-full bg-gradient-to-b from-blue-600 to-indigo-600">
                                    </div>
                                    <h3 class="text-xs font-bold uppercase tracking-wider text-slate-900">Fitur Akun Pro
                                    </h3>
                                </div>
                                <span
                                    class="inline-flex items-center gap-1 px-2 py-0.5 rounded-md bg-amber-50 text-amber-700 text-[10px] font-extrabold border border-amber-200/80">
                                    <svg class="w-3 h-3 text-amber-500" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd"
                                            d="M5 9V7a5 5 0 0110 0v2a2 2 0 012 2v5a2 2 0 01-2 2H5a2 2 0 01-2-2v-5a2 2 0 012-2zm8-2v2H7V7a3 3 0 016 0z"
                                            clip-rule="evenodd" />
                                    </svg>
                                    Terkunci
                                </span>
                            </div>

                            <p class="text-[11.5px] text-slate-500 mb-4 leading-relaxed">
                                Dapatkan akses penuh ke fitur naskah terlengkap untuk kemudahan penelitian akademik
                                Anda:
                            </p>

                            {{-- List 4 Fitur Terkunci --}}
                            <div class="space-y-3">

                                {{-- 1. Upload File Dokumen --}}
                                <a href="{{ route('register') }}"
                                    class="group relative block p-3.5 rounded-xl border border-slate-200/80 bg-slate-50/70 hover:bg-white hover:border-blue-300 hover:shadow-md transition-all duration-200">
                                    <div class="flex items-start justify-between gap-3">
                                        <div class="flex items-center gap-3">
                                            <div
                                                class="w-9 h-9 rounded-lg bg-blue-200 text-blue-700 flex items-center justify-center shrink-0 shadow-sm">
                                                <svg class="w-5 h-5" fill="none" stroke="currentColor"
                                                    viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round"
                                                        stroke-width="3"
                                                        d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12" />
                                                </svg>
                                            </div>
                                            <div>
                                                <h4
                                                    class="text-xs font-bold text-slate-800 group-hover:text-blue-600 transition">
                                                    Upload File Dokumen</h4>
                                                <p class="text-[10.5px] text-slate-500 mt-0.5">Upload DOCX / PDF hingga
                                                    ratusan halaman</p>
                                            </div>
                                        </div>
                                        <div
                                            class="p-1.5 rounded-md bg-white text-slate-400 group-hover:text-amber-600 group-hover:bg-amber-50 transition shrink-0">
                                            <svg class="w-3.5 h-3.5" fill="currentColor" viewBox="0 0 20 20">
                                                <path fill-rule="evenodd"
                                                    d="M5 9V7a5 5 0 0110 0v2a2 2 0 012 2v5a2 2 0 01-2 2H5a2 2 0 01-2-2v-5a2 2 0 012-2zm8-2v2H7V7a3 3 0 016 0z"
                                                    clip-rule="evenodd" />
                                            </svg>
                                        </div>
                                    </div>
                                </a>

                                {{-- 2. Kata Terdeteksi Plagiarisme --}}
                                <a href="{{ route('register') }}"
                                    class="group relative block p-3.5 rounded-xl border border-slate-200/80 bg-slate-50/70 hover:bg-white hover:border-blue-300 hover:shadow-md transition-all duration-200">
                                    <div class="flex items-start justify-between gap-3">
                                        <div class="flex items-center gap-3">
                                            <div
                                                class="w-9 h-9 rounded-lg bg-rose-200 text-rose-700 flex items-center justify-center shrink-0 shadow-sm">
                                                <svg class="w-5 h-5" fill="none" stroke="currentColor"
                                                    viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round"
                                                        stroke-width="3"
                                                        d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z" />
                                                </svg>
                                            </div>
                                            <div>
                                                <h4
                                                    class="text-xs font-bold text-slate-800 group-hover:text-blue-600 transition">
                                                    Highlight Kata Terdeteksi</h4>
                                                <p class="text-[10.5px] text-slate-500 mt-0.5">Penanda warna persis
                                                    format Turnitin</p>
                                            </div>
                                        </div>
                                        <div
                                            class="p-1.5 rounded-md bg-white text-slate-400 group-hover:text-amber-600 group-hover:bg-amber-50 transition shrink-0">
                                            <svg class="w-3.5 h-3.5" fill="currentColor" viewBox="0 0 20 20">
                                                <path fill-rule="evenodd"
                                                    d="M5 9V7a5 5 0 0110 0v2a2 2 0 012 2v5a2 2 0 01-2 2H5a2 2 0 01-2-2v-5a2 2 0 012-2zm8-2v2H7V7a3 3 0 016 0z"
                                                    clip-rule="evenodd" />
                                            </svg>
                                        </div>
                                    </div>
                                </a>

                                {{-- 3. Download File Hasil (PDF/DOCX) --}}
                                <a href="{{ route('register') }}"
                                    class="group relative block p-3.5 rounded-xl border border-slate-200/80 bg-slate-50/70 hover:bg-white hover:border-blue-300 hover:shadow-md transition-all duration-200">
                                    <div class="flex items-start justify-between gap-3">
                                        <div class="flex items-center gap-3">
                                            <div
                                                class="w-9 h-9 rounded-lg bg-emerald-200 text-emerald-700 flex items-center justify-center shrink-0 shadow-sm">
                                                <svg class="w-5 h-5" fill="none" stroke="currentColor"
                                                    viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round"
                                                        stroke-width="3"
                                                        d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                                </svg>
                                            </div>
                                            <div>
                                                <h4
                                                    class="text-xs font-bold text-slate-800 group-hover:text-blue-600 transition">
                                                    Download Laporan PDF</h4>
                                                <p class="text-[10.5px] text-slate-500 mt-0.5">Unduh sertifikat & hasil
                                                    cek siap cetak</p>
                                            </div>
                                        </div>
                                        <div
                                            class="p-1.5 rounded-md bg-white text-slate-400 group-hover:text-amber-600 group-hover:bg-amber-50 transition shrink-0">
                                            <svg class="w-3.5 h-3.5" fill="currentColor" viewBox="0 0 20 20">
                                                <path fill-rule="evenodd"
                                                    d="M5 9V7a5 5 0 0110 0v2a2 2 0 012 2v5a2 2 0 01-2 2H5a2 2 0 01-2-2v-5a2 2 0 012-2zm8-2v2H7V7a3 3 0 016 0z"
                                                    clip-rule="evenodd" />
                                            </svg>
                                        </div>
                                    </div>
                                </a>

                                {{-- 4. Lihat History Riwayat --}}
                                <a href="{{ route('register') }}"
                                    class="group relative block p-3.5 rounded-xl border border-slate-200/80 bg-slate-50/70 hover:bg-white hover:border-blue-300 hover:shadow-md transition-all duration-200">
                                    <div class="flex items-start justify-between gap-3">
                                        <div class="flex items-center gap-3">
                                            <div
                                                class="w-9 h-9 rounded-lg bg-indigo-200 text-indigo-700 flex items-center justify-center shrink-0 shadow-sm">
                                                <svg class="w-5 h-5" fill="none" stroke="currentColor"
                                                    viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round"
                                                        stroke-width="3"
                                                        d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                                                </svg>
                                            </div>
                                            <div>
                                                <h4
                                                    class="text-xs font-bold text-slate-800 group-hover:text-blue-600 transition">
                                                    Lihat Riwayat & History</h4>
                                                <p class="text-[10.5px] text-slate-500 mt-0.5">Arsip seluruh pengecekan
                                                    naskah Anda</p>
                                            </div>
                                        </div>
                                        <div
                                            class="p-1.5 rounded-md bg-white text-slate-400 group-hover:text-amber-600 group-hover:bg-amber-50 transition shrink-0">
                                            <svg class="w-3.5 h-3.5" fill="currentColor" viewBox="0 0 20 20">
                                                <path fill-rule="evenodd"
                                                    d="M5 9V7a5 5 0 0110 0v2a2 2 0 012 2v5a2 2 0 01-2 2H5a2 2 0 01-2-2v-5a2 2 0 012-2zm8-2v2H7V7a3 3 0 016 0z"
                                                    clip-rule="evenodd" />
                                            </svg>
                                        </div>
                                    </div>
                                </a>

                            </div>
                        </div>

                        {{-- CTA Buka Kunci Semua Fitur --}}
                        <div class="mt-6 pt-4 border-t border-slate-100 text-center">
                            <a href="{{ route('register') }}"
                                class="w-full inline-flex items-center justify-center gap-2 rounded-xl bg-gradient-to-r from-blue-600 to-indigo-600 px-4 py-3 text-xs font-bold text-white shadow-md shadow-blue-600/20 hover:from-blue-700 hover:to-indigo-700 transition active:scale-[0.98]">
                                <svg class="w-3.5 h-3.5 text-amber-300" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd"
                                        d="M5 9V7a5 5 0 0110 0v2a2 2 0 012 2v5a2 2 0 01-2 2H5a2 2 0 01-2-2v-5a2 2 0 012-2zm8-2v2H7V7a3 3 0 016 0z"
                                        clip-rule="evenodd" />
                                </svg>
                                <span>Daftar Akun & Buka Fitur</span>
                            </a>
                        </div>
                    </div>

                </div>

                {{-- PANEL HASIL PEMERIKSAAN (FULL WIDTH) --}}
                <div x-show="isChecking" x-cloak
                    class="rounded-2xl border border-slate-200 bg-white p-10 text-center shadow-sm">
                    <div class="w-14 h-14 mx-auto mb-4 p-3 rounded-2xl bg-blue-50 text-blue-600">
                        <svg class="animate-spin w-full h-full" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4" />
                            <path class="opacity-75" fill="currentColor"
                                d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z" />
                        </svg>
                    </div>
                    <h4 class="text-base font-bold text-slate-900">Menganalisis Teks</h4>
                    <p class="text-xs text-slate-500 mt-1">Membandingkan teks dengan jutaan sumber akademik dan web...
                    </p>
                </div>

                {{-- State: Hasil Sudah Keluar --}}
                <template x-if="!isChecking && result">
                    <div class="space-y-6">
                        {{-- Card Overall Similarity --}}
                        <div class="rounded-2xl border border-slate-200 bg-white p-6 sm:p-7 shadow-sm">
                            <div
                                class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 border-b border-slate-100 pb-5 mb-5">
                                <div>
                                    <p class="text-[10px] font-extrabold uppercase tracking-widest text-slate-400">
                                        Ringkasan Hasil Pengecekan</p>
                                    <h3 class="text-lg font-bold text-slate-900 mt-0.5" x-text="result.status_label">
                                    </h3>
                                </div>
                                <span class="self-start sm:self-auto px-4 py-1.5 rounded-full text-sm font-extrabold"
                                    :class="{
                                          'bg-emerald-50 text-emerald-700 border border-emerald-200': result.total_similarity <= 24,
                                          'bg-amber-50 text-amber-700 border border-amber-200': result.total_similarity > 24 && result.total_similarity <= 49,
                                          'bg-orange-50 text-orange-700 border border-orange-200': result.total_similarity > 49 && result.total_similarity <= 74,
                                          'bg-red-50 text-red-700 border border-red-200': result.total_similarity > 74
                                      }" x-text="`${result.total_similarity}% Overall Similarity`">
                                </span>
                            </div>

                            <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                                <div class="p-4 rounded-xl bg-slate-50 border border-slate-100 text-center">
                                    <span
                                        class="text-[10px] font-bold uppercase tracking-wider text-slate-400 block">Total
                                        Kata Dicek</span>
                                    <span class="text-2xl font-black text-slate-900 mt-1 block"
                                        x-text="`${result.total_words} Kata`"></span>
                                </div>
                                <div class="p-4 rounded-xl bg-slate-50 border border-slate-100 text-center">
                                    <span
                                        class="text-[10px] font-bold uppercase tracking-wider text-slate-400 block">Kalimat
                                        Terdeteksi</span>
                                    <span class="text-2xl font-black text-slate-900 mt-1 block"
                                        x-text="result.highlights_count"></span>
                                </div>
                                <div class="p-4 rounded-xl bg-slate-50 border border-slate-100 text-center">
                                    <span
                                        class="text-[10px] font-bold uppercase tracking-wider text-slate-400 block">Sumber
                                        Ditemukan</span>
                                    <span class="text-2xl font-black text-blue-600 mt-1 block"
                                        x-text="result.sources_count"></span>
                                </div>
                            </div>
                        </div>

                        {{-- Grid 2 Kolom untuk Primary Sources dan Frase Terdeteksi --}}
                        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                            {{-- Card Primary Sources --}}
                            <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
                                <div class="flex items-center justify-between border-b border-slate-100 pb-3 mb-4">
                                    <div class="flex items-center gap-2">
                                        <span class="w-2 h-4 rounded-full bg-blue-600"></span>
                                        <h4 class="text-xs font-bold uppercase tracking-wider text-slate-800">Primary
                                            Sources</h4>
                                    </div>
                                    <span class="text-xs font-semibold text-slate-500"
                                        x-text="`${result.sources.length} Sumber`"></span>
                                </div>

                                <div
                                    class="flex min-h-48 items-center justify-center rounded-xl border-2 border-dashed border-blue-300 bg-blue-100">
                                    <svg class="h-16 w-16 text-blue-500" fill="none" viewBox="0 0 24 24"
                                        stroke="currentColor" aria-label="Detail sumber terkunci">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.6"
                                            d="M16 10V7a4 4 0 0 0-8 0v3m-1 0h10a1 1 0 0 1 1 1v8a1 1 0 0 1-1 1H7a1 1 0 0 1-1-1v-8a1 1 0 0 1 1-1Zm5 4v2" />
                                    </svg>
                                </div>
                            </div>

                            {{-- Card Frase Terdeteksi --}}
                            <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
                                <div class="flex items-center justify-between border-b border-slate-100 pb-3 mb-4">
                                    <div class="flex items-center gap-2">
                                        <span class="w-2 h-4 rounded-full bg-rose-500"></span>
                                        <h4 class="text-xs font-bold uppercase tracking-wider text-slate-800">Frase
                                            Terdeteksi</h4>
                                    </div>
                                    <span class="text-xs font-semibold text-slate-500"
                                        x-text="`${result.highlights.length} Frase`"></span>
                                </div>

                                <div
                                    class="flex min-h-48 items-center justify-center rounded-xl border-2 border-dashed border-rose-400 bg-rose-200">
                                    <svg class="h-16 w-16 text-rose-500" fill="none" viewBox="0 0 24 24"
                                        stroke="currentColor" aria-label="Detail frase terkunci">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.6"
                                            d="M16 10V7a4 4 0 0 0-8 0v3m-1 0h10a1 1 0 0 1 1 1v8a1 1 0 0 1-1 1H7a1 1 0 0 1-1-1v-8a1 1 0 0 1 1-1Zm5 4v2" />
                                    </svg>
                                </div>
                            </div>
                        </div>
                    </div>
                </template>

                {{-- Banner Informasi Fitur Akun Full --}}
                <div
                    class="rounded-2xl border border-blue-100 bg-blue-50/70 p-6 flex flex-col sm:flex-row items-center justify-between gap-4">
                    <div>
                        <p class="text-sm font-bold text-blue-950">Ingin Cek Dokumen Lengkap (Ratusan Halaman & File
                            DOCX/PDF)?</p>
                        <p class="text-xs text-blue-700 mt-1">Daftar akun gratis untuk upload file dokumen asli, filter
                            BAB naskah, generator jurnal otomatis, dan parafrase cerdas.</p>
                    </div>
                    <a href="{{ route('register') }}"
                        class="shrink-0 rounded-xl bg-blue-600 px-5 py-2.5 text-xs font-bold text-white shadow-sm hover:bg-blue-700 transition">
                        Daftar Akun Gratis
                    </a>
                </div>

            </div>
        </div>
    </main>

    {{-- FOOTER --}}
    <footer id="footer" class="border-t border-slate-200 bg-white py-12 text-slate-600">
        <div class="mx-auto max-w-[1240px] px-5 sm:px-6 lg:px-8">
            <div class="grid gap-10 sm:grid-cols-2 lg:grid-cols-[1.7fr_1fr_1fr_1.2fr]">
                <div>
                    <div class="flex items-center gap-2.5">
                        <img src="{{ asset('images/naskahceklogo.png') }}" alt="NaskahCek"
                            class="h-9 w-9 rounded-xl object-cover">
                        <span class="text-lg font-black text-slate-900">NaskahCek</span>
                    </div>
                    <p class="mt-4 max-w-sm text-xs leading-6 text-slate-500">
                        Platform untuk membantu pemeriksaan similarity, perbaikan AI, dan persiapan naskah akademik
                        secara praktis dan terpercaya.
                    </p>
                </div>

                <div>
                    <h3 class="text-xs font-black uppercase tracking-wider text-slate-900">Produk</h3>
                    <ul class="mt-4 space-y-2.5 text-xs text-slate-500">
                        <li><a href="{{ route('welcome') }}#fitur" class="hover:text-blue-600">Fitur</a></li>
                        <li><a href="{{ route('welcome') }}#harga" class="hover:text-blue-600">Harga</a></li>
                        <li><a href="{{ route('templates.index') }}" class="hover:text-blue-600">Template Jurnal</a>
                        </li>
                    </ul>
                </div>

                <div>
                    <h3 class="text-xs font-black uppercase tracking-wider text-slate-900">Bantuan</h3>
                    <ul class="mt-4 space-y-2.5 text-xs text-slate-500">
                        <li><a href="{{ route('welcome') }}#faq" class="hover:text-blue-600">FAQ</a></li>
                        <li><a href="{{ route('welcome') }}#faq" class="hover:text-blue-600">Panduan Pengguna</a></li>
                    </ul>
                </div>

                <div>
                    <h3 class="text-xs font-black uppercase tracking-wider text-slate-900">Mulai Sekarang</h3>
                    <p class="mt-4 text-xs leading-6 text-slate-500">
                        Daftar dan coba platform NaskahCek untuk kebutuhan naskah akademik Anda.
                    </p>
                    <a href="{{ route('register') }}"
                        class="mt-4 inline-flex rounded-xl bg-blue-600 px-4 py-2.5 text-xs font-bold text-white shadow-lg shadow-blue-600/20 transition hover:bg-blue-700">
                        Buat Akun Gratis
                    </a>
                </div>
            </div>

            <div
                class="mt-12 flex flex-col items-center justify-between border-t border-slate-100 pt-6 sm:flex-row text-xs text-slate-400">
                <p>&copy; {{ date('Y') }} NaskahCek. Hak cipta dilindungi.</p>
            </div>
        </div>
    </footer>

    {{-- MODAL IMPORT URL / GOOGLE DRIVE --}}
    <div x-show="modalOpen" x-cloak class="fixed inset-0 z-50 overflow-y-auto" aria-labelledby="modal-title"
        role="dialog" aria-modal="true">
        {{-- Backdrop --}}
        <div x-show="modalOpen" x-transition:enter="ease-out duration-200" x-transition:enter-start="opacity-0"
            x-transition:enter-end="opacity-100" x-transition:leave="ease-in duration-150"
            x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0" @click="closeModal()"
            class="fixed inset-0 bg-slate-950/60 backdrop-blur-xs transition-opacity"></div>

        <div class="flex min-h-full items-center justify-center p-4 text-center sm:p-0">
            <div x-show="modalOpen" x-transition:enter="ease-out duration-200"
                x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100"
                x-transition:leave="ease-in duration-150"
                x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100"
                x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95" @click.stop
                class="relative transform overflow-hidden rounded-2xl bg-white text-left shadow-2xl transition-all sm:my-8 sm:w-full sm:max-w-lg border border-slate-200 p-6 sm:p-7">

                {{-- Header Modal --}}
                <div class="flex items-start justify-between pb-4 border-b border-slate-100">
                    <div class="flex items-center gap-3">
                        <div class="flex h-10 w-10 items-center justify-center rounded-xl shrink-0"
                            :class="modalType === 'gdrive' ? 'bg-emerald-50 text-emerald-600' : 'bg-blue-50 text-blue-600'">
                            <template x-if="modalType === 'gdrive'">
                                <svg class="w-5 h-5" viewBox="0 0 24 24" fill="currentColor">
                                    <path
                                        d="M19.35 10.04C18.67 6.59 15.64 4 12 4 9.11 4 6.6 5.64 5.35 8.04 2.34 8.36 0 10.91 0 14c0 3.31 2.69 6 6 6h13c2.76 0 5-2.24 5-5 0-2.64-2.05-4.78-4.65-4.96zM19 18H6c-2.21 0-4-1.79-4-4 0-2.05 1.53-3.76 3.56-3.97l1.07-.11.5-.95C8.08 7.14 9.94 6 12 6c2.62 0 4.88 1.86 5.39 4.43l.3 1.5 1.53.11c1.56.1 2.78 1.41 2.78 2.96 0 1.65-1.35 3-3 3z" />
                                </svg>
                            </template>
                            <template x-if="modalType === 'url'">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M13.828 10.172a4 4 0 00-5.656 0l-4 4a4 4 0 105.656 5.656l1.102-1.101m-.758-4.899a4 4 0 005.656 0l4-4a4 4 0 00-5.656-5.656l-1.1 1.1" />
                                </svg>
                            </template>
                        </div>
                        <div>
                            <h3 class="text-base font-bold text-slate-900"
                                x-text="modalType === 'gdrive' ? 'Impor Teks dari Google Drive' : 'Impor Teks dari URL Web'">
                            </h3>
                            <p class="text-xs text-slate-500 mt-0.5"
                                x-text="modalType === 'gdrive' ? 'Masukkan link share Google Docs atau file publik Google Drive.' : 'Masukkan alamat URL halaman web/artikel yang ingin diambil teksnya.'">
                            </p>
                        </div>
                    </div>
                    <button type="button" @click="closeModal()"
                        class="rounded-lg p-1 text-slate-400 hover:text-slate-600 hover:bg-slate-100 transition">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>

                {{-- Body Modal --}}
                <div class="mt-5 space-y-4">
                    <div>
                        <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-2">
                            <span
                                x-text="modalType === 'gdrive' ? 'URL Google Drive / Google Docs' : 'URL Halaman Web'"></span>
                        </label>
                        <div class="relative">
                            <input type="url" x-model="inputUrl" @keydown.enter.prevent="fetchFromUrl()"
                                :placeholder="modalType === 'gdrive' ? 'https://docs.google.com/document/d/... atau link share drive' : 'https://example.com/artikel-ilmiah'"
                                class="w-full rounded-xl border border-slate-200 px-4 py-3 text-xs sm:text-sm text-slate-800 placeholder-slate-400 focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-500/20"
                                :disabled="isFetchingUrl">
                        </div>
                        <p class="text-[11px] text-slate-500 mt-2 flex items-start gap-1.5">
                            <svg class="w-3.5 h-3.5 text-blue-500 shrink-0 mt-0.5" fill="none" stroke="currentColor"
                                viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                            <span
                                x-text="modalType === 'gdrive' ? 'Pastikan izin akses link Google Drive disetel ke &quot;Siapa saja yang memiliki link dapat melihat&quot; (Anyone with the link can view).' : 'Sistem akan mengekstrak isi teks utama dari halaman URL tersebut dan memasukkannya ke kotak pemeriksaan.'"></span>
                        </p>
                    </div>

                    {{-- Error Message Box --}}
                    <div x-show="fetchError" x-cloak
                        class="p-3 rounded-xl bg-red-50 border border-red-200 text-red-700 text-xs flex items-start gap-2">
                        <svg class="w-4 h-4 text-red-500 shrink-0 mt-0.5" fill="none" stroke="currentColor"
                            viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                        <span x-text="fetchError"></span>
                    </div>
                </div>

                {{-- Footer Modal --}}
                <div class="mt-6 flex items-center justify-end gap-2.5 pt-4 border-t border-slate-100">
                    <button type="button" @click="closeModal()" :disabled="isFetchingUrl"
                        class="rounded-xl px-4 py-2.5 text-xs font-semibold text-slate-600 hover:bg-slate-100 transition cursor-pointer">
                        Batal
                    </button>
                    <button type="button" @click="fetchFromUrl()" :disabled="isFetchingUrl || !inputUrl.trim()"
                        class="inline-flex items-center gap-2 rounded-xl bg-blue-600 px-5 py-2.5 text-xs font-bold text-white shadow-md hover:bg-blue-700 transition disabled:opacity-50 disabled:pointer-events-none cursor-pointer">
                        <svg x-show="isFetchingUrl" x-cloak class="animate-spin w-3.5 h-3.5" fill="none"
                            viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4" />
                            <path class="opacity-75" fill="currentColor"
                                d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z" />
                        </svg>
                        <span x-text="isFetchingUrl ? 'Mengambil Teks...' : 'Ambil Konten Teks'"></span>
                    </button>
                </div>

            </div>
        </div>
    </div>
</div>
@endsection

@push('styles')
<style>
@media (max-width: 767px) {
    nav {
        height: 62px;
    }

    nav > div {
        height: 62px !important;
    }

    nav > #mobile-menu {
        height: auto !important;
    }
}
</style>
@endpush

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
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

function freePlagiarismApp() {
    return {
        text: '',
        wordCount: 0,
        isChecking: false,
        result: null,

        // Modal State
        modalOpen: false,
        modalType: 'url', // 'url' atau 'gdrive'
        inputUrl: '',
        isFetchingUrl: false,
        fetchError: '',

        openModal(type) {
            this.modalType = type;
            this.inputUrl = '';
            this.fetchError = '';
            this.modalOpen = true;
        },

        closeModal() {
            if (this.isFetchingUrl) return;
            this.modalOpen = false;
            this.inputUrl = '';
            this.fetchError = '';
        },

        async fetchFromUrl() {
            const trimmed = this.inputUrl.trim();
            if (!trimmed) {
                this.fetchError = 'Harap masukkan URL yang valid.';
                Swal.fire({
                    icon: 'warning',
                    title: 'URL belum diisi',
                    text: 'Masukkan URL yang ingin diambil teksnya.',
                    confirmButtonColor: '#2563eb',
                });
                return;
            }

            this.isFetchingUrl = true;
            this.fetchError = '';

            try {
                const response = await fetch('{{ route("free.check.fetch_url") }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                    },
                    body: JSON.stringify({
                        url: trimmed,
                    })
                });

                const data = await response.json();

                if (response.ok && data.success) {
                    this.text = data.text;
                    this.updateWordCount();
                    this.result = null;
                    this.closeModal();
                } else {
                    this.fetchError = data.message || 'Gagal mengambil teks dari URL tersebut.';
                    Swal.fire({
                        icon: 'error',
                        title: 'Gagal mengambil teks',
                        text: this.fetchError,
                        confirmButtonColor: '#2563eb',
                    });
                }
            } catch (error) {
                console.error(error);
                this.fetchError = 'Gagal menghubungi server. Pastikan koneksi internet aktif.';
                Swal.fire({
                    icon: 'error',
                    title: 'Koneksi bermasalah',
                    text: this.fetchError,
                    confirmButtonColor: '#2563eb',
                });
            } finally {
                this.isFetchingUrl = false;
            }
        },

        updateWordCount() {
            const raw = this.text.trim();
            if (!raw) {
                this.wordCount = 0;
                return;
            }
            const words = raw.split(/\s+/).filter(w => w.length > 0);
            this.wordCount = words.length;
        },

        async pollCheckStatus(checkId) {
            const statusUrl = '{{ url('/cekplagiasiturnitin/status') }}/' + checkId;
            for (;;) {
                await new Promise(resolve => setTimeout(resolve, 2000));
                const response = await fetch(statusUrl, { headers: { 'Accept': 'application/json' } });
                const payload = await response.json();
                if (!response.ok || !payload.success) throw new Error(payload.message || 'Pemeriksaan gagal.');
                if (payload.data.status === 'completed') {
                    this.result = payload.data;
                    return;
                }
                if (payload.data.status === 'failed') {
                    throw new Error(payload.data.error_message || 'Pemeriksaan gagal.');
                }
            }
        },

        async startCheck() {
            this.updateWordCount();
            if (this.wordCount === 0 || this.wordCount > 5000) {
                return;
            }

            this.isChecking = true;
            this.result = null;

            try {
                const response = await fetch('{{ route("free.check.process") }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                    },
                    body: JSON.stringify({
                        text: this.text,
                    })
                });

                const data = await response.json();

                if (response.ok && data.success) {
                    await this.pollCheckStatus(data.data.check_id);
                } else {
                    Swal.fire({
                        icon: 'error',
                        title: 'Pemeriksaan gagal',
                        text: data.message || 'Terjadi kesalahan saat memeriksa teks.',
                        confirmButtonColor: '#2563eb',
                    });
                }

            } catch (error) {
                console.error(error);
                Swal.fire({
                    icon: 'error',
                    title: 'Koneksi bermasalah',
                    text: 'Gagal menghubungi server. Periksa koneksi internet Anda.',
                    confirmButtonColor: '#2563eb',
                });
            } finally {
                this.isChecking = false;
            }
        }
    }
}
</script>
@endpush
