@extends('layouts.landing')

@section('title', 'NaskahKu PRO — Cek Similarity, Perbaiki Naskah, Buat Jurnal')

@section('content')
<div class="relative w-full overflow-x-hidden bg-white text-slate-900 selection:bg-blue-600 selection:text-white">

    <!-- Global Background Glows -->
    <div
        class="pointer-events-none fixed top-0 left-1/2 -z-10 h-[650px] w-[1000px] -translate-x-1/2 rounded-full bg-blue-100 blur-3xl opacity-50">
    </div>
    <div
        class="pointer-events-none fixed top-[1400px] right-0 -z-10 h-[500px] w-[500px] rounded-full bg-blue-100 blur-3xl opacity-50">
    </div>

    <!-- FIXED NAVBAR -->
    <nav class="fixed inset-x-0 top-0 z-50 border-b border-blue-100 bg-white shadow-sm transition-all">
        <div class="mx-auto flex max-w-7xl items-center justify-between px-4 py-3 sm:px-6 lg:px-8">
            <!-- Brand Logo -->
            <a href="#" class="flex items-center gap-2 group hover:opacity-80 transition-opacity">
                <img src="{{ asset('images/naskahkulogo.png') }}" alt="NaskahKu"
                    class="h-9 w-9 rounded-lg object-cover ring-1 ring-slate-200" />
                <span class="hidden sm:inline text-lg font-black tracking-tight text-slate-900">
                    NaskahKu
                    <span
                        class="ml-1 rounded-full bg-blue-100 px-2 py-0.5 text-[9px] font-bold text-blue-600 border border-blue-200">PRO</span>
                </span>
            </a>

            <!-- Desktop Nav Links -->
            <div class="hidden items-center gap-0.5 lg:flex">
                <a href="#features"
                    class="px-3 py-2 rounded-lg text-sm font-medium text-slate-600 hover:text-blue-600 hover:bg-slate-50 transition-all">Fitur</a>
                <a href="#workflow"
                    class="px-3 py-2 rounded-lg text-sm font-medium text-slate-600 hover:text-blue-600 hover:bg-slate-50 transition-all">Cara
                    Kerja</a>
                <a href="#rewrite"
                    class="px-3 py-2 rounded-lg text-sm font-medium text-slate-600 hover:text-blue-600 hover:bg-slate-50 transition-all">AI
                    Rewrite</a>
                <a href="#journal"
                    class="px-3 py-2 rounded-lg text-sm font-medium text-slate-600 hover:text-blue-600 hover:bg-slate-50 transition-all">AI
                    Journal</a>
                <a href="#detection-types"
                    class="px-3 py-2 rounded-lg text-sm font-medium text-slate-600 hover:text-blue-600 hover:bg-slate-50 transition-all">Pengecekan</a>
                <a href="#pricing"
                    class="px-3 py-2 rounded-lg text-sm font-medium text-slate-600 hover:text-blue-600 hover:bg-slate-50 transition-all">Harga</a>
                <a href="#faq"
                    class="px-3 py-2 rounded-lg text-sm font-medium text-slate-600 hover:text-blue-600 hover:bg-slate-50 transition-all">FAQ</a>
            </div>

            <!-- Auth Buttons -->
            <div class="flex items-center gap-2">
                @auth
                @if(auth()->user()->isAdmin())
                <a href="{{ route('admin.dashboard') }}"
                    class="inline-flex items-center justify-center rounded-lg bg-blue-600 px-4 py-2 text-xs font-semibold text-white hover:bg-blue-700 transition-all shadow-sm">
                    Dashboard
                </a>
                @else
                <a href="{{ route('user.dashboard') }}"
                    class="inline-flex items-center justify-center rounded-lg bg-blue-600 px-4 py-2 text-xs font-semibold text-white hover:bg-blue-700 transition-all shadow-sm">
                    Dashboard
                </a>
                @endif
                @else
                <a href="{{ route('login') }}"
                    class="inline-flex items-center justify-center rounded-lg border border-slate-300 bg-white px-3 py-2 text-xs font-semibold text-slate-700 hover:bg-slate-50 transition-all">
                    Masuk
                </a>
                <a href="{{ route('register') }}"
                    class="inline-flex items-center justify-center rounded-lg bg-blue-600 px-4 py-2 text-xs font-semibold text-white hover:bg-blue-700 transition-all shadow-sm">
                    Cek Gratis
                </a>
                @endauth
            </div>
        </div>
    </nav>

    <!-- HERO SECTION (2 Kolom) -->
    <section id="hero" class="relative mx-auto max-w-7xl px-4 pt-28 pb-16 sm:px-6 sm:pt-32 sm:pb-20 lg:px-8">
        <div class="grid lg:grid-cols-2 gap-12 items-center">
            <!-- LEFT: Text & CTA -->
            <div class="space-y-6 fade-up">
                <div class="inline-block">
                    <div class="rounded-full bg-blue-50 px-4 py-2 border border-blue-200">
                        <p class="text-xs font-bold text-blue-600 uppercase tracking-wider">Platform Cerdas untuk Karya
                            Ilmiah</p>
                    </div>
                </div>

                <h1 class="text-5xl sm:text-6xl font-black tracking-tight text-slate-900 leading-tight">
                    Periksa, Perbaiki, dan Siapkan Naskah Akademik Anda
                </h1>

                <p class="text-lg sm:text-xl text-slate-600 leading-relaxed font-normal max-w-xl">
                    Platform cerdas untuk menganalisis kemiripan, membantu memperbaiki naskah, dan menyusun draft jurnal
                    dalam satu alur kerja.
                </p>

                <!-- Feature Indicators -->
                <div class="space-y-2 pt-2">
                    <div class="flex items-center gap-2 text-sm text-slate-700">
                        <svg class="h-4 w-4 text-blue-600 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd"
                                d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z"
                                clip-rule="evenodd" />
                        </svg>
                        <span>Cek Similarity</span>
                    </div>
                    <div class="flex items-center gap-2 text-sm text-slate-700">
                        <svg class="h-4 w-4 text-blue-600 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd"
                                d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z"
                                clip-rule="evenodd" />
                        </svg>
                        <span>AI Academic Rewrite</span>
                    </div>
                    <div class="flex items-center gap-2 text-sm text-slate-700">
                        <svg class="h-4 w-4 text-blue-600 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd"
                                d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z"
                                clip-rule="evenodd" />
                        </svg>
                        <span>AI Journal Generator</span>
                    </div>
                </div>

                <!-- CTA Buttons -->
                <div class="flex flex-col sm:flex-row gap-3 pt-6">
                    @auth
                    <a href="{{ route('user.plagiarism.index') }}"
                        class="inline-flex items-center justify-center rounded-xl bg-blue-600 px-7 py-3 text-base font-bold text-white shadow-lg shadow-blue-600/20 hover:bg-blue-700 transition-all">
                        Mulai Cek Dokumen
                        <svg class="h-5 w-5 ml-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M13 7l5 5m0 0l-5 5m5-5H6" />
                        </svg>
                    </a>
                    @else
                    <a href="{{ route('register') }}"
                        class="inline-flex items-center justify-center rounded-xl bg-blue-600 px-7 py-3 text-base font-bold text-white shadow-lg shadow-blue-600/20 hover:bg-blue-700 transition-all">
                        Mulai Cek Dokumen Gratis
                        <svg class="h-5 w-5 ml-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M13 7l5 5m0 0l-5 5m5-5H6" />
                        </svg>
                    </a>
                    @endauth

                    <a href="#features"
                        class="inline-flex items-center justify-center rounded-xl border border-slate-300 bg-white px-7 py-3 text-base font-bold text-slate-900 hover:border-slate-400 hover:bg-slate-50 transition-all shadow-sm">
                        Buat Jurnal dengan AI
                    </a>
                </div>
            </div>

            <!-- RIGHT: Report Preview Mockup -->
            <div class="fade-up lg:flex hidden">
                <div class="w-full float-animation">
                    <div class="rounded-2xl bg-white border border-slate-200 shadow-2xl overflow-hidden">
                        <!-- Header -->
                        <div class="bg-blue-600 px-6 py-4">
                            <h3 class="text-lg font-bold text-white">Hasil Pemeriksaan</h3>
                            <p class="text-blue-100 text-sm mt-1">Dokumen Anda sudah dianalisis</p>
                        </div>

                        <!-- Content -->
                        <div class="p-6 space-y-4">
                            <!-- Similarity Score -->
                            <div class="rounded-xl bg-blue-50 border border-blue-200 p-4">
                                <div class="flex items-center justify-between mb-2">
                                    <span class="text-sm font-semibold text-slate-700">Similarity Score</span>
                                    <span class="text-2xl font-black text-blue-600">18%</span>
                                </div>
                                <p class="text-xs text-slate-600 mb-3">Status: <span
                                        class="font-bold text-blue-600">Rendah</span></p>
                                <div class="w-full bg-slate-200 rounded-full h-2">
                                    <div class="bg-blue-600 h-2 rounded-full" style="width: 18%"></div>
                                </div>
                            </div>

                            <!-- Stats Grid -->
                            <div class="grid grid-cols-2 gap-3">
                                <div class="rounded-lg bg-slate-50 border border-slate-200 p-3">
                                    <p class="text-xs text-slate-600 font-medium">Original</p>
                                    <p class="text-xl font-black text-slate-900 mt-1">82%</p>
                                </div>
                                <div class="rounded-lg bg-slate-50 border border-slate-200 p-3">
                                    <p class="text-xs text-slate-600 font-medium">Jumlah Kata</p>
                                    <p class="text-xl font-black text-slate-900 mt-1">10.345</p>
                                </div>
                                <div class="rounded-lg bg-slate-50 border border-slate-200 p-3">
                                    <p class="text-xs text-slate-600 font-medium">Jumlah Sumber</p>
                                    <p class="text-xl font-black text-slate-900 mt-1">23</p>
                                </div>
                                <div class="rounded-lg bg-slate-50 border border-slate-200 p-3">
                                    <p class="text-xs text-slate-600 font-medium">Waktu Analisis</p>
                                    <p class="text-xl font-black text-slate-900 mt-1">18 detik</p>
                                </div>
                            </div>

                            <!-- Sample Sources -->
                            <div class="border-t border-slate-200 pt-4 mt-4">
                                <p class="text-xs font-semibold text-slate-700 mb-2">Sumber Ditemukan:</p>
                                <div class="space-y-1 text-xs text-slate-600">
                                    <p>1. Jurnal Ilmiah (5.2%)</p>
                                    <p>2. Publikasi Online (3.8%)</p>
                                    <p>3. Dokumen Digital (2.1%)</p>
                                    <p>4. Website Referensi (1.9%)</p>
                                </div>
                            </div>

                            <!-- Sample Highlight -->
                            <div class="border-t border-slate-200 pt-4 mt-4">
                                <p class="text-xs font-semibold text-slate-700 mb-2">Teks Dengan Similarity:</p>
                                <p class="text-xs text-slate-600 bg-amber-50 border border-amber-200 rounded px-2 py-1">
                                    "Metodologi penelitian ini menggunakan pendekatan kualitatif dengan studi kasus..."
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- WHY NASKAHKU SECTION -->
    <section id="why-naskahku" class="border-y border-blue-50 bg-white py-20 sm:py-28">
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
            <div class="mx-auto max-w-3xl text-center mb-16 fade-up">
                <p class="text-sm font-bold uppercase tracking-wider text-blue-600 mb-3">Kenapa NaskahKu</p>
                <h2 class="text-4xl sm:text-5xl font-black tracking-tight text-slate-900 mb-4">
                    Satu platform untuk mengecek, memperbaiki, dan menyiapkan naskah akademik
                </h2>
                <p class="text-lg text-slate-600">
                    Kami membantu mahasiswa, dosen, dan peneliti melihat potensi similaritas, memperbaiki teks dengan
                    konteks akademik, dan menyiapkan draft jurnal tanpa melewati banyak tools yang terpisah.
                </p>
            </div>

            <div class="grid gap-6 md:grid-cols-4">
                <div
                    class="fade-up rounded-2xl border border-slate-200 bg-white p-6 shadow-sm hover:shadow-md transition-all text-center">
                    <div
                        class="inline-flex h-12 w-12 items-center justify-center rounded-xl bg-blue-100 text-blue-600 mb-4">
                        <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                    </div>
                    <h3 class="font-bold text-slate-900 mb-2">Deteksi yang jelas</h3>
                    <p class="text-sm text-slate-600">Membantu menemukan kemiripan teks dan sumber utama dengan laporan
                        yang mudah dibaca.</p>
                </div>

                <div
                    class="fade-up rounded-2xl border border-slate-200 bg-white p-6 shadow-sm hover:shadow-md transition-all text-center">
                    <div
                        class="inline-flex h-12 w-12 items-center justify-center rounded-xl bg-teal-100 text-teal-600 mb-4">
                        <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M3 12h18M7 8h10M7 16h10" />
                        </svg>
                    </div>
                    <h3 class="font-bold text-slate-900 mb-2">Database luas</h3>
                    <p class="text-sm text-slate-600">Mencakup sumber akademik, publikasi online, dan repositori yang
                        relevan untuk pemeriksaan.</p>
                </div>

                <div
                    class="fade-up rounded-2xl border border-slate-200 bg-white p-6 shadow-sm hover:shadow-md transition-all text-center">
                    <div
                        class="inline-flex h-12 w-12 items-center justify-center rounded-xl bg-violet-100 text-violet-600 mb-4">
                        <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M4 6h16M4 12h10m-10 6h16" />
                        </svg>
                    </div>
                    <h3 class="font-bold text-slate-900 mb-2">Laporan yang terarah</h3>
                    <p class="text-sm text-slate-600">Setiap hasil disusun agar Anda tahu bagian mana yang perlu
                        diperbaiki dan dari sumber mana.</p>
                </div>

                <div
                    class="fade-up rounded-2xl border border-slate-200 bg-white p-6 shadow-sm hover:shadow-md transition-all text-center">
                    <div
                        class="inline-flex h-12 w-12 items-center justify-center rounded-xl bg-amber-100 text-amber-600 mb-4">
                        <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
                        </svg>
                    </div>
                    <h3 class="font-bold text-slate-900 mb-2">Privasi tetap aman</h3>
                    <p class="text-sm text-slate-600">Dokumen diproses dengan kontrol akses yang jelas dan perlindungan
                        data yang lebih terjamin.</p>
                </div>
            </div>

            <div class="mt-12 rounded-2xl border border-slate-200 bg-slate-50 p-6 sm:p-8 fade-up">
                <div class="grid gap-6 md:grid-cols-4 text-center">
                    <div>
                        <p class="text-2xl font-black text-blue-600">Kemiripan teks</p>
                        <p class="mt-2 text-sm text-slate-600">Deteksi salinan langsung, frasa, dan pola yang mirip.</p>
                    </div>
                    <div>
                        <p class="text-2xl font-black text-blue-600">Sumber</p>
                        <p class="mt-2 text-sm text-slate-600">Jurnal, publikasi, repositori, dan indeks akademik.</p>
                    </div>
                    <div>
                        <p class="text-2xl font-black text-blue-600">Rekomendasi</p>
                        <p class="mt-2 text-sm text-slate-600">Area yang perlu dibenahi ditandai dengan jelas.</p>
                    </div>
                    <div>
                        <p class="text-2xl font-black text-blue-600">Perbaikan</p>
                        <p class="mt-2 text-sm text-slate-600">AI membantu menyusun ulang teks agar lebih akademik.</p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- FEATURES SECTION -->
    <section id="features" class="relative mx-auto max-w-7xl px-4 py-20 sm:px-6 sm:py-28 lg:px-8">
        <div class="mx-auto max-w-3xl text-center mb-16 fade-up">
            <p class="text-sm font-bold uppercase tracking-wider text-blue-600 mb-3">Fitur Utama</p>
            <h2 class="text-4xl sm:text-5xl font-black tracking-tight text-slate-900 mb-4">
                Kelola Naskah Akademik dalam Satu Platform
            </h2>
            <p class="text-lg text-slate-600">
                Mulai dari menemukan kemiripan, memperbaiki naskah, hingga menyusun draft jurnal dengan lebih praktis.
            </p>
        </div>

        <div class="grid gap-6 md:grid-cols-3">
            <!-- Card 1: Cek Similarity -->
            <div
                class="fade-up group relative rounded-2xl border border-slate-200 bg-white p-8 shadow-sm hover:shadow-lg hover:border-blue-400 transition-all duration-300">
                <div
                    class="flex h-12 w-12 items-center justify-center rounded-xl bg-blue-100 border border-blue-200 text-blue-600 mb-5 group-hover:scale-110 transition-transform">
                    <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                    </svg>
                </div>
                <h3 class="text-xl font-bold text-slate-900 mb-3">Cek Similarity</h3>
                <p class="text-sm leading-relaxed text-slate-600 mb-6">
                    Analisis tingkat kemiripan naskah, temukan sumber yang memiliki kecocokan, dan pahami bagian yang
                    perlu diperiksa.
                </p>
                <a href="{{ auth()->check() ? route('user.plagiarism.index') : route('register') }}"
                    class="inline-flex items-center gap-2 text-sm font-bold text-blue-600 hover:text-blue-700 transition-colors">
                    Cek Dokumen
                    <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                    </svg>
                </a>
            </div>

            <!-- Card 2: AI Rewrite -->
            <div
                class="fade-up group relative rounded-2xl border border-slate-200 bg-white p-8 shadow-sm hover:shadow-lg hover:border-blue-400 transition-all duration-300">
                <div
                    class="flex h-12 w-12 items-center justify-center rounded-xl bg-blue-100 border border-blue-200 text-blue-600 mb-5 group-hover:scale-110 transition-transform">
                    <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                    </svg>
                </div>
                <h3 class="text-xl font-bold text-slate-900 mb-3">Perbaikan Naskah dengan AI</h3>
                <p class="text-sm leading-relaxed text-slate-600 mb-6">
                    Identifikasi bagian dengan kemiripan tinggi dan lakukan academic rewriting untuk membantu
                    memperbaiki struktur serta gaya penulisan dengan tetap mempertahankan konteks.
                </p>
                <a href="{{ auth()->check() ? route('user.improvement.index') : route('register') }}"
                    class="inline-flex items-center gap-2 text-sm font-bold text-blue-600 hover:text-blue-700 transition-colors">
                    Perbaiki Naskah
                    <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                    </svg>
                </a>
            </div>

            <!-- Card 3: AI Journal -->
            <div
                class="fade-up group relative rounded-2xl border border-slate-200 bg-white p-8 shadow-sm hover:shadow-lg hover:border-blue-400 transition-all duration-300">
                <div
                    class="flex h-12 w-12 items-center justify-center rounded-xl bg-blue-100 border border-blue-200 text-blue-600 mb-5 group-hover:scale-110 transition-transform">
                    <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z" />
                    </svg>
                </div>
                <h3 class="text-xl font-bold text-slate-900 mb-3">AI Journal Generator</h3>
                <p class="text-sm leading-relaxed text-slate-600 mb-6">
                    Bantu menyusun draft jurnal berdasarkan topik, bahan penelitian, dan struktur akademik yang Anda
                    pilih.
                </p>
                <a href="{{ auth()->check() ? route('user.journal.create') : route('register') }}"
                    class="inline-flex items-center gap-2 text-sm font-bold text-blue-600 hover:text-blue-700 transition-colors">
                    Buat Jurnal
                    <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                    </svg>
                </a>
            </div>
        </div>
    </section>

    <!-- WORKFLOW SECTION -->
    <section id="workflow" class="border-t border-blue-50 bg-white py-20 sm:py-28">
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
            <div class="mx-auto max-w-3xl text-center mb-16 fade-up">
                <p class="text-sm font-bold uppercase tracking-wider text-blue-600 mb-3">Alur Kerja</p>
                <h2 class="text-4xl sm:text-5xl font-black tracking-tight text-slate-900 mb-4">
                    Perjalanan Naskah Anda
                </h2>
                <p class="text-lg text-slate-600">
                    Kelola naskah akademik dari tahap pemeriksaan hingga siap dikembangkan menjadi draft jurnal.
                </p>
            </div>

            <div class="grid gap-8 lg:grid-cols-4">
                <!-- Step 1 -->
                <div class="fade-up">
                    <div class="flex items-center justify-center mb-4">
                        <div
                            class="flex h-16 w-16 items-center justify-center rounded-2xl bg-blue-600 text-2xl font-black text-white shadow-lg">
                            01</div>
                    </div>
                    <h3 class="text-lg font-bold text-slate-900 text-center mb-2">CEK</h3>
                    <p class="text-xs font-semibold text-blue-600 text-center mb-3">Analisis Similarity</p>
                    <p class="text-sm text-slate-600 text-center">Unggah dokumen dan lihat tingkat kemiripan serta
                        sumber yang ditemukan.</p>
                </div>

                <!-- Arrow -->
                <div class="hidden lg:flex items-center justify-center">
                    <svg class="h-8 w-8 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                    </svg>
                </div>

                <!-- Step 2 -->
                <div class="fade-up">
                    <div class="flex items-center justify-center mb-4">
                        <div
                            class="flex h-16 w-16 items-center justify-center rounded-2xl bg-blue-600 text-2xl font-black text-white shadow-lg">
                            02</div>
                    </div>
                    <h3 class="text-lg font-bold text-slate-900 text-center mb-2">PERBAIKI</h3>
                    <p class="text-xs font-semibold text-blue-600 text-center mb-3">Academic Rewrite</p>
                    <p class="text-sm text-slate-600 text-center">Pilih bagian yang perlu diperbaiki dan gunakan AI
                        untuk membantu menyusun ulang teks.</p>
                </div>

                <!-- Arrow -->
                <div class="hidden lg:flex items-center justify-center">
                    <svg class="h-8 w-8 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                    </svg>
                </div>

                <!-- Step 3 -->
                <div class="fade-up">
                    <div class="flex items-center justify-center mb-4">
                        <div
                            class="flex h-16 w-16 items-center justify-center rounded-2xl bg-blue-600 text-2xl font-black text-white shadow-lg">
                            03</div>
                    </div>
                    <h3 class="text-lg font-bold text-slate-900 text-center mb-2">SUSUN</h3>
                    <p class="text-xs font-semibold text-blue-600 text-center mb-3">AI Journal Generator</p>
                    <p class="text-sm text-slate-600 text-center">Gunakan topik dan bahan penelitian untuk membantu
                        menyusun draft jurnal.</p>
                </div>
            </div>
        </div>
    </section>

    <!-- AI REWRITE SECTION -->
    <section id="rewrite" class="relative mx-auto max-w-7xl px-4 py-20 sm:px-6 sm:py-28 lg:px-8">
        <div class="mx-auto max-w-3xl text-center mb-16 fade-up">
            <p class="text-sm font-bold uppercase tracking-wider text-blue-600 mb-3">AI Perbaikan Naskah</p>
            <h2 class="text-4xl sm:text-5xl font-black tracking-tight text-slate-900 mb-4">
                Perbaiki Naskah dengan Bantuan AI
            </h2>
            <p class="text-lg text-slate-600">
                Ubah bagian yang perlu diperbaiki menjadi tulisan akademik yang lebih terstruktur tanpa kehilangan
                konteks pembahasan.
            </p>
        </div>

        <div class="grid lg:grid-cols-2 gap-8 items-center">
            <!-- BEFORE -->
            <div class="fade-up">
                <h3 class="text-lg font-bold text-slate-900 mb-4">Sebelum Perbaikan</h3>
                <div class="rounded-2xl border border-slate-200 bg-slate-50 p-6 mb-4">
                    <p class="text-sm text-slate-700 leading-relaxed">
                        "Penelitian ini menggunakan metode yang sama dengan penelitian sebelumnya. Hasilnya menunjukkan
                        bahwa metode tersebut sangat efektif. Penelitian sebelumnya juga menunjukkan hasil yang sama."
                    </p>
                </div>
                <div class="flex items-center justify-between px-4 py-3 rounded-xl bg-red-50 border border-red-200">
                    <span class="text-sm font-semibold text-slate-900">Similarity Score:</span>
                    <span class="text-2xl font-black text-red-600">42%</span>
                </div>
                <p class="text-xs text-slate-600 mt-2 text-center">*Contoh hasil</p>
            </div>

            <!-- AFTER -->
            <div class="fade-up">
                <h3 class="text-lg font-bold text-slate-900 mb-4">Setelah Perbaikan</h3>
                <div class="rounded-2xl border border-slate-200 bg-emerald-50 p-6 mb-4">
                    <p class="text-sm text-slate-700 leading-relaxed">
                        "Studi ini mengadopsi pendekatan metodologis serupa dengan penelitian terdahulu, namun dengan
                        penyesuaian parameter untuk konteks spesifik. Hasil pengujian menunjukkan peningkatan signifikan
                        dalam efisiensi penerapan, sejalan dengan temuan empiris dari literatur akademik terkait."
                    </p>
                </div>
                <div class="flex items-center justify-between px-4 py-3 rounded-xl bg-teal-50 border border-teal-200">
                    <span class="text-sm font-semibold text-slate-900">Similarity Score:</span>
                    <span class="text-2xl font-black text-teal-600">8%</span>
                </div>
                <p class="text-xs text-slate-600 mt-2 text-center">*Contoh hasil</p>
            </div>
        </div>

        <!-- Rewrite Options -->
        <div class="grid md:grid-cols-5 gap-4 mt-12">
            <div class="fade-up text-center">
                <div
                    class="inline-flex h-12 w-12 items-center justify-center rounded-xl bg-blue-100 text-blue-600 mb-3 mx-auto">
                    <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
                    </svg>
                </div>
                <p class="text-sm font-semibold text-slate-900">Rewrite</p>
            </div>
            <div class="fade-up text-center">
                <div
                    class="inline-flex h-12 w-12 items-center justify-center rounded-xl bg-teal-100 text-teal-600 mb-3 mx-auto">
                    <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12" />
                    </svg>
                </div>
                <p class="text-sm font-semibold text-slate-900">Paraphrase</p>
            </div>
            <div class="fade-up text-center">
                <div
                    class="inline-flex h-12 w-12 items-center justify-center rounded-xl bg-violet-100 text-violet-600 mb-3 mx-auto">
                    <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                </div>
                <p class="text-sm font-semibold text-slate-900">Struktur Kalimat</p>
            </div>
            <div class="fade-up text-center">
                <div
                    class="inline-flex h-12 w-12 items-center justify-center rounded-xl bg-amber-100 text-amber-600 mb-3 mx-auto">
                    <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M13 10V3L4 14h7v7l9-11h-7z" />
                    </svg>
                </div>
                <p class="text-sm font-semibold text-slate-900">Gaya Akademik</p>
            </div>
            <div class="fade-up text-center">
                <div
                    class="inline-flex h-12 w-12 items-center justify-center rounded-xl bg-pink-100 text-pink-600 mb-3 mx-auto">
                    <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M9 12l2 2 4-4m7 0a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                </div>
                <p class="text-sm font-semibold text-slate-900">Review Hasil</p>
            </div>
        </div>

        <div class="text-center mt-12 fade-up">
            <a href="{{ auth()->check() ? route('user.improvement.index') : route('register') }}"
                class="inline-flex items-center gap-2 rounded-xl bg-blue-600 px-8 py-4 text-base font-bold text-white shadow-lg shadow-blue-500/25 hover:bg-blue-700 transition-all">
                Coba Academic Rewrite
                <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M13 7l5 5m0 0l-5 5m5-5H6" />
                </svg>
            </a>
        </div>
    </section>

    <!-- AI JOURNAL SECTION -->
    <section id="journal" class="border-t border-blue-50 bg-white py-20 sm:py-28">
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
            <div class="mx-auto max-w-3xl text-center mb-16 fade-up">
                <p class="text-sm font-bold uppercase tracking-wider text-teal-600 mb-3">AI Journal Generator</p>
                <h2 class="text-4xl sm:text-5xl font-black tracking-tight text-slate-900 mb-4">
                    Susun Draft Jurnal dengan AI
                </h2>
                <p class="text-lg text-slate-600">
                    Mulai dari ide penelitian hingga draft jurnal yang terstruktur.
                </p>
            </div>

            <div class="grid lg:grid-cols-2 gap-12 items-center">
                <!-- Left: Steps -->
                <div class="space-y-4 fade-up">
                    <div class="flex gap-4">
                        <div
                            class="flex h-12 w-12 items-center justify-center rounded-xl bg-gradient-to-br from-indigo-600 to-indigo-700 text-white font-bold flex-shrink-0">
                            01</div>
                        <div>
                            <h4 class="font-bold text-slate-900">Topik Penelitian</h4>
                            <p class="text-sm text-slate-600 mt-1">Masukkan topik dan deskripsi singkat penelitian Anda
                            </p>
                        </div>
                    </div>
                    <div class="flex gap-4">
                        <div
                            class="flex h-12 w-12 items-center justify-center rounded-xl bg-gradient-to-br from-indigo-600 to-indigo-700 text-white font-bold flex-shrink-0">
                            02</div>
                        <div>
                            <h4 class="font-bold text-slate-900">Informasi Penelitian</h4>
                            <p class="text-sm text-slate-600 mt-1">Upload bahan penelitian, paper, dan referensi</p>
                        </div>
                    </div>
                    <div class="flex gap-4">
                        <div
                            class="flex h-12 w-12 items-center justify-center rounded-xl bg-gradient-to-br from-indigo-600 to-indigo-700 text-white font-bold flex-shrink-0">
                            03</div>
                        <div>
                            <h4 class="font-bold text-slate-900">Struktur Jurnal</h4>
                            <p class="text-sm text-slate-600 mt-1">Pilih format jurnal dan template</p>
                        </div>
                    </div>
                    <div class="flex gap-4">
                        <div
                            class="flex h-12 w-12 items-center justify-center rounded-xl bg-gradient-to-br from-indigo-600 to-indigo-700 text-white font-bold flex-shrink-0">
                            04</div>
                        <div>
                            <h4 class="font-bold text-slate-900">Generate Draft</h4>
                            <p class="text-sm text-slate-600 mt-1">AI membuat draft jurnal otomatis</p>
                        </div>
                    </div>
                    <div class="flex gap-4">
                        <div
                            class="flex h-12 w-12 items-center justify-center rounded-xl bg-gradient-to-br from-indigo-600 to-indigo-700 text-white font-bold flex-shrink-0">
                            05</div>
                        <div>
                            <h4 class="font-bold text-slate-900">Review & Edit</h4>
                            <p class="text-sm text-slate-600 mt-1">Review dan sesuaikan sesuai kebutuhan</p>
                        </div>
                    </div>
                </div>

                <!-- Right: Journal Structure Preview -->
                <div class="fade-up">
                    <div class="rounded-2xl border border-slate-200 bg-white p-8 shadow-lg">
                        <h3 class="text-lg font-bold text-slate-900 mb-6">Struktur Jurnal yang Dihasilkan:</h3>
                        <ul class="space-y-2 text-sm text-slate-700">
                            <li class="flex items-start gap-2">
                                <span class="text-blue-600 font-bold flex-shrink-0">✓</span>
                                <span>Judul Penelitian</span>
                            </li>
                            <li class="flex items-start gap-2">
                                <span class="text-blue-600 font-bold flex-shrink-0">✓</span>
                                <span>Abstrak & Kata Kunci</span>
                            </li>
                            <li class="flex items-start gap-2">
                                <span class="text-blue-600 font-bold flex-shrink-0">✓</span>
                                <span>Pendahuluan</span>
                            </li>
                            <li class="flex items-start gap-2">
                                <span class="text-blue-600 font-bold flex-shrink-0">✓</span>
                                <span>Tinjauan Pustaka (Literature Review)</span>
                            </li>
                            <li class="flex items-start gap-2">
                                <span class="text-blue-600 font-bold flex-shrink-0">✓</span>
                                <span>Metodologi Penelitian</span>
                            </li>
                            <li class="flex items-start gap-2">
                                <span class="text-blue-600 font-bold flex-shrink-0">✓</span>
                                <span>Hasil Penelitian</span>
                            </li>
                            <li class="flex items-start gap-2">
                                <span class="text-blue-600 font-bold flex-shrink-0">✓</span>
                                <span>Pembahasan</span>
                            </li>
                            <li class="flex items-start gap-2">
                                <span class="text-blue-600 font-bold flex-shrink-0">✓</span>
                                <span>Kesimpulan & Saran</span>
                            </li>
                            <li class="flex items-start gap-2">
                                <span class="text-blue-600 font-bold flex-shrink-0">✓</span>
                                <span>Daftar Pustaka</span>
                            </li>
                        </ul>

                        <div class="mt-8 p-4 rounded-lg bg-blue-50 border border-blue-200">
                            <p class="text-xs text-slate-700">
                                <span class="font-bold">Catatan Penting:</span> Konten yang dihasilkan AI tetap perlu
                                diperiksa, divalidasi, dan disesuaikan oleh penulis sebelum digunakan.
                            </p>
                        </div>
                    </div>
                </div>
            </div>

            <div class="text-center mt-12 fade-up">
                <a href="{{ auth()->check() ? route('user.journal.create') : route('register') }}"
                    class="inline-flex items-center gap-2 rounded-xl bg-blue-600 px-8 py-4 text-base font-bold text-white shadow-lg shadow-blue-500/25 hover:bg-blue-700 transition-all">
                    Mulai Buat Jurnal
                    <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M13 7l5 5m0 0l-5 5m5-5H6" />
                    </svg>
                </a>
            </div>
        </div>
    </section>

    <!-- DETECTION TYPES SECTION -->
    <section id="detection-types" class="relative mx-auto max-w-7xl px-4 py-20 sm:px-6 sm:py-28 lg:px-8">
        <div class="mx-auto max-w-3xl text-center mb-16 fade-up">
            <h2 class="text-4xl sm:text-5xl font-black tracking-tight text-slate-900 mb-4">
                Apa yang Diperiksa?
            </h2>
        </div>

        <div class="grid gap-6 md:grid-cols-4">
            <div
                class="fade-up rounded-2xl border border-slate-200 bg-white p-6 shadow-sm hover:shadow-md transition-all text-center">
                <div
                    class="inline-flex h-12 w-12 items-center justify-center rounded-xl bg-blue-100 text-blue-600 mb-4">
                    <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                </div>
                <h3 class="font-bold text-slate-900 mb-2">Kemiripan Teks Langsung</h3>
                <p class="text-sm text-slate-600">Deteksi salinan teks yang sama persis</p>
            </div>

            <div
                class="fade-up rounded-2xl border border-slate-200 bg-white p-6 shadow-sm hover:shadow-md transition-all text-center">
                <div
                    class="inline-flex h-12 w-12 items-center justify-center rounded-xl bg-teal-100 text-teal-600 mb-4">
                    <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                </div>
                <h3 class="font-bold text-slate-900 mb-2">Kemiripan Frasa</h3>
                <p class="text-sm text-slate-600">Identifikasi ungkapan dan frasa serupa</p>
            </div>

            <div
                class="fade-up rounded-2xl border border-slate-200 bg-white p-6 shadow-sm hover:shadow-md transition-all text-center">
                <div
                    class="inline-flex h-12 w-12 items-center justify-center rounded-xl bg-violet-100 text-violet-600 mb-4">
                    <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                </div>
                <h3 class="font-bold text-slate-900 mb-2">Kemiripan Makna (Semantik)</h3>
                <p class="text-sm text-slate-600">Analisis kesamaan makna dan konteks</p>
            </div>

            <div
                class="fade-up rounded-2xl border border-slate-200 bg-white p-6 shadow-sm hover:shadow-md transition-all text-center">
                <div
                    class="inline-flex h-12 w-12 items-center justify-center rounded-xl bg-amber-100 text-amber-600 mb-4">
                    <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                </div>
                <h3 class="font-bold text-slate-900 mb-2">Kemiripan Lintas Bahasa</h3>
                <p class="text-sm text-slate-600">Deteksi terjemahan dan adaptasi lintas bahasa</p>
            </div>
        </div>
    </section>

    <!-- SOURCES SECTION -->
    <section class="border-t border-blue-50 bg-white py-20 sm:py-28">
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
            <div class="mx-auto max-w-3xl text-center mb-16 fade-up">
                <h2 class="text-4xl sm:text-5xl font-black tracking-tight text-slate-900 mb-4">
                    Sumber Pemeriksaan
                </h2>
                <p class="text-lg text-slate-600">
                    Database komprehensif dari berbagai sumber akademik global
                </p>
            </div>

            <div class="grid gap-6 md:grid-cols-5">
                <div
                    class="fade-up rounded-2xl border border-slate-200 bg-white p-6 shadow-sm text-center hover:shadow-md transition-all">
                    <div
                        class="inline-flex h-12 w-12 items-center justify-center rounded-xl bg-blue-100 text-blue-600 mb-4">
                        <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.972 1.972 0 013 12V7a4 4 0 014-4z" />
                        </svg>
                    </div>
                    <h3 class="font-bold text-slate-900 text-sm">Repository Akademik</h3>
                </div>

                <div
                    class="fade-up rounded-2xl border border-slate-200 bg-white p-6 shadow-sm text-center hover:shadow-md transition-all">
                    <div
                        class="inline-flex h-12 w-12 items-center justify-center rounded-xl bg-teal-100 text-teal-600 mb-4">
                        <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M12 6.253v13m0-13C6.248 6.253 2 10.998 2 16.5S6.248 26.747 12 26.747s10-4.745 10-10.247S17.752 6.253 12 6.253z" />
                        </svg>
                    </div>
                    <h3 class="font-bold text-slate-900 text-sm">Jurnal Ilmiah</h3>
                </div>

                <div
                    class="fade-up rounded-2xl border border-slate-200 bg-white p-6 shadow-sm text-center hover:shadow-md transition-all">
                    <div
                        class="inline-flex h-12 w-12 items-center justify-center rounded-xl bg-violet-100 text-violet-600 mb-4">
                        <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M13 10V3L4 14h7v7l9-11h-7z" />
                        </svg>
                    </div>
                    <h3 class="font-bold text-slate-900 text-sm">Publikasi Online</h3>
                </div>

                <div
                    class="fade-up rounded-2xl border border-slate-200 bg-white p-6 shadow-sm text-center hover:shadow-md transition-all">
                    <div
                        class="inline-flex h-12 w-12 items-center justify-center rounded-xl bg-amber-100 text-amber-600 mb-4">
                        <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
                        </svg>
                    </div>
                    <h3 class="font-bold text-slate-900 text-sm">Dokumen Publik</h3>
                </div>

                <div
                    class="fade-up rounded-2xl border border-slate-200 bg-white p-6 shadow-sm text-center hover:shadow-md transition-all">
                    <div
                        class="inline-flex h-12 w-12 items-center justify-center rounded-xl bg-pink-100 text-pink-600 mb-4">
                        <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                        </svg>
                    </div>
                    <h3 class="font-bold text-slate-900 text-sm">Indeks Web</h3>
                </div>
            </div>
        </div>
    </section>


    <!-- PRICING SECTION -->
    <section id="pricing" class="relative mx-auto max-w-7xl px-4 py-20 sm:px-6 sm:py-28 lg:px-8">
        <div class="mx-auto max-w-3xl text-center mb-16 fade-up">
            <p class="text-sm font-bold uppercase tracking-wider text-teal-600 mb-3">Paket & Harga</p>
            <h2 class="text-4xl sm:text-5xl font-black tracking-tight text-slate-900 mb-4">
                Pilih Paket Sesuai Kebutuhan Anda
            </h2>
            <p class="text-lg text-slate-600">
                Transparan tanpa biaya tersembunyi. Upgrade kapan saja saat dibutuhkan.
            </p>
        </div>

        <div class="grid gap-6 lg:grid-cols-3 lg:items-center">
            <!-- Basic Starter -->
            <div class="fade-up rounded-2xl border border-slate-200 bg-white p-8 sm:p-10 shadow-sm">
                <h3 class="text-xl font-bold text-slate-900">Basic Starter</h3>
                <p class="mt-2 text-sm text-slate-600">Cocok untuk cek dokumen harian & tugas kuliah</p>
                <div class="mt-6 flex items-baseline gap-1">
                    <span class="text-4xl font-black text-slate-900">Rp 0</span>
                    <span class="text-sm text-slate-600">/ selamanya</span>
                </div>
                <ul class="mt-8 space-y-3 text-sm text-slate-700">
                    <li class="flex items-center gap-2">
                        <svg class="h-5 w-5 text-teal-600" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd"
                                d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z"
                                clip-rule="evenodd" />
                        </svg>
                        <span>3 Dokumen per hari</span>
                    </li>
                    <li class="flex items-center gap-2">
                        <svg class="h-5 w-5 text-teal-600" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd"
                                d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z"
                                clip-rule="evenodd" />
                        </svg>
                        <span>Pengecekan database dasar</span>
                    </li>
                    <li class="flex items-center gap-2">
                        <svg class="h-5 w-5 text-teal-600" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd"
                                d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z"
                                clip-rule="evenodd" />
                        </svg>
                        <span>Laporan similarity standar</span>
                    </li>
                </ul>
                <a href="{{ route('register') }}"
                    class="mt-8 block w-full rounded-lg border border-slate-300 bg-slate-50 py-3 text-center text-sm font-bold text-slate-800 hover:bg-slate-100 transition-all">
                    Mulai Gratis
                </a>
            </div>

            <!-- Academic Pro (Highlighted) -->
            <div
                class="fade-up relative rounded-2xl border-2 border-blue-600 bg-white p-8 sm:p-10 shadow-xl shadow-blue-100 lg:-translate-y-2">
                <div
                    class="absolute -top-4 left-1/2 -translate-x-1/2 rounded-full bg-blue-600 px-4 py-1.5 text-xs font-black text-white uppercase tracking-wider">
                    Paling Populer</div>
                <h3 class="text-xl font-bold text-slate-900">Academic Pro</h3>
                <p class="mt-2 text-sm text-slate-600">Untuk mahasiswa akhir, dosen & penulis jurnal aktif</p>
                <div class="mt-6 flex items-baseline gap-1">
                    <span class="text-5xl font-black text-slate-900">Rp 49.000</span>
                    <span class="text-sm text-slate-600">/ bulan</span>
                </div>
                <ul class="mt-8 space-y-3 text-sm text-slate-700">
                    <li class="flex items-center gap-2">
                        <svg class="h-5 w-5 text-blue-600" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd"
                                d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z"
                                clip-rule="evenodd" />
                        </svg>
                        <span class="font-semibold">Unlimited Pengecekan Dokumen</span>
                    </li>
                    <li class="flex items-center gap-2">
                        <svg class="h-5 w-5 text-blue-600" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd"
                                d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z"
                                clip-rule="evenodd" />
                        </svg>
                        <span>Analisis Database Lengkap</span>
                    </li>
                    <li class="flex items-center gap-2">
                        <svg class="h-5 w-5 text-blue-600" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd"
                                d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z"
                                clip-rule="evenodd" />
                        </svg>
                        <span>AI Academic Rewrite</span>
                    </li>
                    <li class="flex items-center gap-2">
                        <svg class="h-5 w-5 text-blue-600" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd"
                                d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z"
                                clip-rule="evenodd" />
                        </svg>
                        <span>AI Paraphrase</span>
                    </li>
                    <li class="flex items-center gap-2">
                        <svg class="h-5 w-5 text-blue-600" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd"
                                d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z"
                                clip-rule="evenodd" />
                        </svg>
                        <span>AI Journal Generator</span>
                    </li>
                    <li class="flex items-center gap-2">
                        <svg class="h-5 w-5 text-blue-600" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd"
                                d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z"
                                clip-rule="evenodd" />
                        </svg>
                        <span>Ekspor Laporan PDF</span>
                    </li>
                    <li class="flex items-center gap-2">
                        <svg class="h-5 w-5 text-blue-600" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd"
                                d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z"
                                clip-rule="evenodd" />
                        </svg>
                        <span>Ekspor Dokumen</span>
                    </li>
                </ul>
                <a href="{{ route('register') }}"
                    class="mt-8 block w-full rounded-lg bg-blue-600 py-3.5 text-center text-sm font-bold text-white shadow-lg shadow-blue-500/25 hover:bg-blue-700 transition-all">
                    Upgrade ke Pro Sekarang
                </a>
            </div>

            <!-- Campus & Enterprise -->
            <div class="fade-up rounded-2xl border border-slate-200 bg-white p-8 sm:p-10 shadow-sm">
                <h3 class="text-xl font-bold text-slate-900">Campus & Enterprise</h3>
                <p class="mt-2 text-sm text-slate-600">Untuk laboratorium, dewan jurnal & universitas</p>
                <div class="mt-6 flex items-baseline gap-1">
                    <span class="text-2xl font-black text-slate-900">Hubungi Kami</span>
                </div>
                <ul class="mt-8 space-y-3 text-sm text-slate-700">
                    <li class="flex items-center gap-2">
                        <svg class="h-5 w-5 text-teal-600" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd"
                                d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z"
                                clip-rule="evenodd" />
                        </svg>
                        <span>Multi-User License</span>
                    </li>
                    <li class="flex items-center gap-2">
                        <svg class="h-5 w-5 text-teal-600" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd"
                                d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z"
                                clip-rule="evenodd" />
                        </svg>
                        <span>Integrasi LMS</span>
                    </li>
                    <li class="flex items-center gap-2">
                        <svg class="h-5 w-5 text-teal-600" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd"
                                d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z"
                                clip-rule="evenodd" />
                        </svg>
                        <span>Dedicated Repository</span>
                    </li>
                    <li class="flex items-center gap-2">
                        <svg class="h-5 w-5 text-teal-600" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd"
                                d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z"
                                clip-rule="evenodd" />
                        </svg>
                        <span>API Access</span>
                    </li>
                    <li class="flex items-center gap-2">
                        <svg class="h-5 w-5 text-teal-600" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd"
                                d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z"
                                clip-rule="evenodd" />
                        </svg>
                        <span>SLA Dukungan 24/7</span>
                    </li>
                </ul>
                <a href="{{ route('register') }}"
                    class="mt-8 block w-full rounded-lg border border-slate-300 bg-slate-50 py-3 text-center text-sm font-bold text-slate-800 hover:bg-slate-100 transition-all">
                    Konsultasi dengan Tim
                </a>
            </div>
        </div>
    </section>

    <!-- FAQ SECTION -->
    <section id="faq" class="border-t border-slate-200 bg-slate-50/50 py-20 sm:py-28">
        <div class="mx-auto max-w-3xl px-4 sm:px-6 lg:px-8">
            <div class="text-center mb-16 fade-up">
                <p class="text-sm font-bold uppercase tracking-wider text-blue-600 mb-3">FAQ</p>
                <h2 class="text-4xl sm:text-5xl font-black tracking-tight text-slate-900 mb-4">
                    Pertanyaan yang Sering Diajukan
                </h2>
            </div>

            <div class="space-y-4" x-data="{ activeAccordion: null }">
                <!-- FAQ 1 -->
                <div class="fade-up rounded-xl border border-slate-200 bg-white overflow-hidden">
                    <button @click="activeAccordion = activeAccordion === 1 ? null : 1"
                        class="w-full px-6 py-4 flex items-center justify-between hover:bg-slate-50 transition-colors">
                        <h3 class="text-left font-bold text-slate-900">Apa itu similarity score?</h3>
                        <svg class="h-5 w-5 text-slate-600 transition-transform"
                            :class="activeAccordion === 1 ? 'rotate-180' : ''" fill="none" stroke="currentColor"
                            viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M19 14l-7 7m0 0l-7-7m7 7V3" />
                        </svg>
                    </button>
                    <div class="accordion-body" :class="activeAccordion === 1 ? 'open' : ''">
                        <p class="px-6 py-4 text-sm text-slate-700 leading-relaxed">
                            Similarity score adalah persentase yang menunjukkan seberapa banyak teks dalam dokumen Anda
                            yang memiliki kesamaan dengan sumber yang ada di database kami. Score 0% berarti tidak ada
                            kesamaan, sementara 100% berarti dokumen sepenuhnya sama.
                        </p>
                    </div>
                </div>

                <!-- FAQ 2 -->
                <div class="fade-up rounded-xl border border-slate-200 bg-white overflow-hidden">
                    <button @click="activeAccordion = activeAccordion === 2 ? null : 2"
                        class="w-full px-6 py-4 flex items-center justify-between hover:bg-slate-50 transition-colors">
                        <h3 class="text-left font-bold text-slate-900">Apakah similarity score sama dengan plagiarisme?
                        </h3>
                        <svg class="h-5 w-5 text-slate-600 transition-transform"
                            :class="activeAccordion === 2 ? 'rotate-180' : ''" fill="none" stroke="currentColor"
                            viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M19 14l-7 7m0 0l-7-7m7 7V3" />
                        </svg>
                    </button>
                    <div class="accordion-body" :class="activeAccordion === 2 ? 'open' : ''">
                        <p class="px-6 py-4 text-sm text-slate-700 leading-relaxed">
                            Tidak. Similarity score hanya menunjukkan persentase kesamaan teks, tetapi tidak otomatis
                            berarti plagiarisme. Kutipan langsung dengan referensi yang benar, penggunaan istilah umum,
                            dan parafrase yang tepat adalah hal normal dalam tulisan akademik. Evaluasi plagiarisme
                            harus mempertimbangkan konteks, sumber, dan kebijakan institusi.
                        </p>
                    </div>
                </div>

                <!-- FAQ 3 -->
                <div class="fade-up rounded-xl border border-slate-200 bg-white overflow-hidden">
                    <button @click="activeAccordion = activeAccordion === 3 ? null : 3"
                        class="w-full px-6 py-4 flex items-center justify-between hover:bg-slate-50 transition-colors">
                        <h3 class="text-left font-bold text-slate-900">Apakah NaskahKu menyimpan dokumen saya?</h3>
                        <svg class="h-5 w-5 text-slate-600 transition-transform"
                            :class="activeAccordion === 3 ? 'rotate-180' : ''" fill="none" stroke="currentColor"
                            viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M19 14l-7 7m0 0l-7-7m7 7V3" />
                        </svg>
                    </button>
                    <div class="accordion-body" :class="activeAccordion === 3 ? 'open' : ''">
                        <p class="px-6 py-4 text-sm text-slate-700 leading-relaxed">
                            Tidak. NaskahKu tidak menyimpan dokumen Anda dalam database publikasi. Dokumen Anda hanya
                            diproses untuk analisis dan tidak akan diindeks atau dipublikasikan. Anda memiliki kontrol
                            penuh untuk menghapus dokumen kapan saja dari sistem kami.
                        </p>
                    </div>
                </div>

                <!-- FAQ 4 -->
                <div class="fade-up rounded-xl border border-slate-200 bg-white overflow-hidden">
                    <button @click="activeAccordion = activeAccordion === 4 ? null : 4"
                        class="w-full px-6 py-4 flex items-center justify-between hover:bg-slate-50 transition-colors">
                        <h3 class="text-left font-bold text-slate-900">Berapa lama proses pengecekan?</h3>
                        <svg class="h-5 w-5 text-slate-600 transition-transform"
                            :class="activeAccordion === 4 ? 'rotate-180' : ''" fill="none" stroke="currentColor"
                            viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M19 14l-7 7m0 0l-7-7m7 7V3" />
                        </svg>
                    </button>
                    <div class="accordion-body" :class="activeAccordion === 4 ? 'open' : ''">
                        <p class="px-6 py-4 text-sm text-slate-700 leading-relaxed">
                            Rata-rata pengecekan membutuhkan waktu 10 hingga 25 detik tergantung pada panjang dokumen
                            dan jumlah kata. Dokumen yang lebih panjang mungkin memerlukan waktu sedikit lebih lama
                            untuk pemindaian mendalam.
                        </p>
                    </div>
                </div>

                <!-- FAQ 5 -->
                <div class="fade-up rounded-xl border border-slate-200 bg-white overflow-hidden">
                    <button @click="activeAccordion = activeAccordion === 5 ? null : 5"
                        class="w-full px-6 py-4 flex items-center justify-between hover:bg-slate-50 transition-colors">
                        <h3 class="text-left font-bold text-slate-900">Apakah laporan dapat diunduh?</h3>
                        <svg class="h-5 w-5 text-slate-600 transition-transform"
                            :class="activeAccordion === 5 ? 'rotate-180' : ''" fill="none" stroke="currentColor"
                            viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M19 14l-7 7m0 0l-7-7m7 7V3" />
                        </svg>
                    </button>
                    <div class="accordion-body" :class="activeAccordion === 5 ? 'open' : ''">
                        <p class="px-6 py-4 text-sm text-slate-700 leading-relaxed">
                            Ya. Laporan hasil pemeriksaan dapat diunduh dalam format PDF dengan detail lengkap termasuk
                            similarity score, sumber yang ditemukan, dan highlight bagian yang memiliki kesamaan.
                            Laporan ini dapat digunakan untuk keperluan administratif atau sidang.
                        </p>
                    </div>
                </div>

                <!-- FAQ 6 -->
                <div class="fade-up rounded-xl border border-slate-200 bg-white overflow-hidden">
                    <button @click="activeAccordion = activeAccordion === 6 ? null : 6"
                        class="w-full px-6 py-4 flex items-center justify-between hover:bg-slate-50 transition-colors">
                        <h3 class="text-left font-bold text-slate-900">Format file apa saja yang didukung?</h3>
                        <svg class="h-5 w-5 text-slate-600 transition-transform"
                            :class="activeAccordion === 6 ? 'rotate-180' : ''" fill="none" stroke="currentColor"
                            viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M19 14l-7 7m0 0l-7-7m7 7V3" />
                        </svg>
                    </button>
                    <div class="accordion-body" :class="activeAccordion === 6 ? 'open' : ''">
                        <p class="px-6 py-4 text-sm text-slate-700 leading-relaxed">
                            NaskahKu mendukung file dalam format Microsoft Word (.docx), PDF (.pdf), dan Plain Text
                            (.txt) dengan ukuran maksimal 50MB per dokumen. File dalam format lain dapat dikonversi ke
                            salah satu format tersebut terlebih dahulu sebelum diupload.
                        </p>
                    </div>
                </div>

                <!-- FAQ 7 -->
                <div class="fade-up rounded-xl border border-slate-200 bg-white overflow-hidden">
                    <button @click="activeAccordion = activeAccordion === 7 ? null : 7"
                        class="w-full px-6 py-4 flex items-center justify-between hover:bg-slate-50 transition-colors">
                        <h3 class="text-left font-bold text-slate-900">Bagaimana cara membaca hasil laporan?</h3>
                        <svg class="h-5 w-5 text-slate-600 transition-transform"
                            :class="activeAccordion === 7 ? 'rotate-180' : ''" fill="none" stroke="currentColor"
                            viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M19 14l-7 7m0 0l-7-7m7 7V3" />
                        </svg>
                    </button>
                    <div class="accordion-body" :class="activeAccordion === 7 ? 'open' : ''">
                        <p class="px-6 py-4 text-sm text-slate-700 leading-relaxed">
                            Laporan menampilkan similarity score keseluruhan, daftar sumber yang ditemukan dengan
                            persentase kesamaan, dan highlight teks yang memiliki kesamaan. Anda dapat mengklik setiap
                            sumber untuk melihat perbandingan teks yang lebih detail. Gunakan informasi ini untuk
                            mengidentifikasi bagian yang perlu diperbaiki.
                        </p>
                    </div>
                </div>

                <!-- FAQ 8 -->
                <div class="fade-up rounded-xl border border-slate-200 bg-white overflow-hidden">
                    <button @click="activeAccordion = activeAccordion === 8 ? null : 8"
                        class="w-full px-6 py-4 flex items-center justify-between hover:bg-slate-50 transition-colors">
                        <h3 class="text-left font-bold text-slate-900">Bagaimana cara menggunakan AI Academic Rewrite?
                        </h3>
                        <svg class="h-5 w-5 text-slate-600 transition-transform"
                            :class="activeAccordion === 8 ? 'rotate-180' : ''" fill="none" stroke="currentColor"
                            viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M19 14l-7 7m0 0l-7-7m7 7V3" />
                        </svg>
                    </button>
                    <div class="accordion-body" :class="activeAccordion === 8 ? 'open' : ''">
                        <p class="px-6 py-4 text-sm text-slate-700 leading-relaxed">
                            Setelah mendapatkan hasil pemeriksaan, pilih bagian dengan similarity tinggi, lalu gunakan
                            fitur AI Rewrite untuk menghasilkan versi baru teks tersebut. Anda dapat memilih jenis
                            rewrite (Rewrite, Paraphrase, Struktur Kalimat, atau Gaya Akademik), review hasilnya, dan
                            apply jika sesuai. Tinjau perubahan dan pastikan maknanya tetap tersimpan.
                        </p>
                    </div>
                </div>

                <!-- FAQ 9 -->
                <div class="fade-up rounded-xl border border-slate-200 bg-white overflow-hidden">
                    <button @click="activeAccordion = activeAccordion === 9 ? null : 9"
                        class="w-full px-6 py-4 flex items-center justify-between hover:bg-slate-50 transition-colors">
                        <h3 class="text-left font-bold text-slate-900">Bagaimana cara membuat draft jurnal dengan AI?
                        </h3>
                        <svg class="h-5 w-5 text-slate-600 transition-transform"
                            :class="activeAccordion === 9 ? 'rotate-180' : ''" fill="none" stroke="currentColor"
                            viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M19 14l-7 7m0 0l-7-7m7 7V3" />
                        </svg>
                    </button>
                    <div class="accordion-body" :class="activeAccordion === 9 ? 'open' : ''">
                        <p class="px-6 py-4 text-sm text-slate-700 leading-relaxed">
                            Buka fitur AI Journal Generator, masukkan topik penelitian Anda, upload bahan referensi,
                            pilih struktur jurnal yang diinginkan, lalu klik Generate. AI akan membuat draft jurnal
                            dengan bagian-bagian standar seperti abstrak, pendahuluan, metodologi, hasil, pembahasan,
                            dan kesimpulan. Tinjau dan edit hasil untuk menyesuaikan dengan kebutuhan Anda sebelum
                            finalisasi.
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- FINAL CTA -->
    <section class="relative border-t border-slate-200 bg-white py-20 sm:py-28">
        <div class="mx-auto max-w-4xl px-4 text-center sm:px-6 lg:px-8 fade-up">
            <h2 class="text-4xl sm:text-5xl font-black tracking-tight text-slate-900 mb-4">
                Mulai Kelola Naskah Anda
            </h2>
            <p class="text-lg text-slate-600 mb-8 max-w-2xl mx-auto">
                Cek kemiripan, perbaiki naskah, dan susun draft jurnal dalam satu platform. Ratusan mahasiswa dan
                peneliti telah mempercayai NaskahKu.
            </p>
            <div class="flex flex-col sm:flex-row gap-3 justify-center">
                @auth
                <a href="{{ route('user.plagiarism.index') }}"
                    class="inline-flex items-center justify-center rounded-lg bg-blue-600 px-7 py-4 text-base font-bold text-white shadow-lg shadow-blue-500/25 hover:bg-blue-700 transition-all">
                    Cek Similarity
                </a>
                <a href="{{ route('user.journal.create') }}"
                    class="inline-flex items-center justify-center rounded-lg border border-slate-300 bg-white px-7 py-4 text-base font-bold text-slate-900 hover:bg-slate-50 transition-all shadow-sm">
                    Buat Jurnal
                </a>
                @else
                <a href="{{ route('register') }}"
                    class="inline-flex items-center justify-center rounded-lg bg-blue-600 px-7 py-4 text-base font-bold text-white shadow-lg shadow-blue-500/25 hover:bg-blue-700 transition-all">
                    Cek Similarity
                </a>
                <a href="{{ route('register') }}"
                    class="inline-flex items-center justify-center rounded-lg border border-slate-300 bg-white px-7 py-4 text-base font-bold text-slate-900 hover:bg-slate-50 transition-all shadow-sm">
                    Buat Jurnal
                </a>
                @endauth
            </div>
        </div>
    </section>

    <!-- FOOTER -->
    <footer class="border-t border-blue-100 bg-white text-slate-600 py-16">
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
            <div class="grid gap-12 md:grid-cols-5 mb-8">
                <!-- Brand -->
                <div class="md:col-span-1">
                    <div class="flex items-center gap-2 mb-3">
                        <img src="{{ asset('images/naskahkulogo.png') }}" alt="NaskahKu"
                            class="h-8 w-8 rounded-lg ring-1 ring-slate-200" />
                        <span class="font-black text-slate-900 text-lg">NaskahKu PRO</span>
                    </div>
                    <p class="text-sm text-slate-500">
                        Platform cerdas untuk menganalisis, memperbaiki, dan menyiapkan naskah akademik.
                    </p>
                </div>

                <!-- Product -->
                <div>
                    <h3 class="font-bold text-slate-900 text-sm uppercase tracking-wider mb-4">Produk</h3>
                    <ul class="space-y-2 text-sm">
                        <li><a href="{{ auth()->check() ? route('user.plagiarism.index') : route('register') }}"
                                class="hover:text-blue-600 transition-colors">Cek Similarity</a></li>
                        <li><a href="{{ auth()->check() ? route('user.improvement.index') : route('register') }}"
                                class="hover:text-blue-600 transition-colors">AI Academic Rewrite</a></li>
                        <li><a href="{{ auth()->check() ? route('user.journal.create') : route('register') }}"
                                class="hover:text-blue-600 transition-colors">AI Journal Generator</a></li>
                        <li><a href="#" class="hover:text-blue-600 transition-colors">Contoh Laporan</a></li>
                    </ul>
                </div>

                <!-- Company -->
                <div>
                    <h3 class="font-bold text-slate-900 text-sm uppercase tracking-wider mb-4">Perusahaan</h3>
                    <ul class="space-y-2 text-sm">
                        <li><a href="#workflow" class="hover:text-blue-600 transition-colors">Cara Kerja</a></li>
                        <li><a href="#pricing" class="hover:text-blue-600 transition-colors">Harga</a></li>
                        <li><a href="#faq" class="hover:text-blue-600 transition-colors">FAQ</a></li>
                        <li><a href="#" class="hover:text-blue-600 transition-colors">Pusat Bantuan</a></li>
                    </ul>
                </div>

                <!-- Legal -->
                <div>
                    <h3 class="font-bold text-slate-900 text-sm uppercase tracking-wider mb-4">Legal</h3>
                    <ul class="space-y-2 text-sm">
                        <li><a href="#" class="hover:text-blue-600 transition-colors">Kebijakan Privasi</a></li>
                        <li><a href="#" class="hover:text-blue-600 transition-colors">Syarat & Ketentuan</a></li>
                        <li><a href="#" class="hover:text-blue-600 transition-colors">Cookie Policy</a></li>
                    </ul>
                </div>

                <!-- Social (placeholder) -->
                <div>
                    <h3 class="font-bold text-slate-900 text-sm uppercase tracking-wider mb-4">Ikuti Kami</h3>
                    <div class="space-y-2 text-sm">
                        <p class="text-slate-500">Dapatkan update terbaru tentang fitur dan tips.</p>
                    </div>
                </div>
            </div>

            <div class="border-t border-blue-100 pt-8 text-center text-xs text-slate-600">
                <p>&copy; {{ date('Y') }} NaskahKu Pro. Hak Cipta Dilindungi Undang-Undang.</p>
            </div>
        </div>
    </footer>

</div>
@endsection
