@extends('layouts.landing')

@section('title', 'NaskahCek — Periksa, Perbaiki, dan Siapkan Naskah Akademik')

@section('content')

{{-- NAVBAR (FIXED TOP) --}}
<nav class="fixed left-0 right-0 top-0 z-50 w-full border-b border-slate-200 bg-white shadow-sm">
    <div class="mx-auto flex h-[72px] max-w-[1320px] items-center justify-between px-5 sm:px-6 lg:px-8">
        <a href="#" class="flex items-center gap-2.5">
            <img src="{{ asset('images/naskahceklogo.png') }}" alt="NaskahCek" class="h-9 w-9 rounded-xl object-cover">
            <span class="text-[17px] font-extrabold tracking-[-0.02em] text-slate-900">NaskahCek</span>
        </a>

        <div class="hidden items-center gap-1 md:flex">
            <a href="{{ route('free.check.index') }}"
                class="rounded-lg px-3.5 py-2 text-[13px] font-semibold text-slate-600 transition hover:bg-slate-50 hover:text-blue-600">Cek
                Plagiasi Turnitin</a>
            <a href="{{ route('pricing') }}"
                class="rounded-lg px-3.5 py-2 text-[13px] font-semibold text-slate-600 transition hover:bg-slate-50 hover:text-blue-600">Paket
                Harga</a>
            <a href="{{ route('templates.index') }}"
                class="rounded-lg px-3.5 py-2 text-[13px] font-semibold text-slate-600 transition hover:bg-slate-50 hover:text-blue-600">Template
                Jurnal</a>
            <a href="#faq"
                class="rounded-lg px-3.5 py-2 text-[13px] font-semibold text-slate-600 transition hover:bg-slate-50 hover:text-blue-600">Bantuan</a>
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
        <div class="flex flex-col gap-1">
            <a href="{{ route('free.check.index') }}"
                class="rounded-xl px-3 py-3 text-sm font-semibold text-slate-700 transition hover:bg-blue-50 hover:text-blue-600">Cek
                Plagiasi Turnitin</a>
            <a href="{{ route('pricing') }}"
                class="rounded-xl px-3 py-3 text-sm font-semibold text-slate-700 transition hover:bg-blue-50 hover:text-blue-600">Paket
                Harga</a>
            <a href="{{ route('templates.index') }}"
                class="rounded-xl px-3 py-3 text-sm font-semibold text-slate-700 transition hover:bg-blue-50 hover:text-blue-600">Template
                Jurnal</a>
            <a href="#faq"
                class="rounded-xl px-3 py-3 text-sm font-semibold text-slate-700 transition hover:bg-blue-50 hover:text-blue-600">Bantuan</a>
            <a href="{{ route('login') }}"
                class="mt-2 mb-3 inline-flex items-center justify-center rounded-xl bg-blue-600 px-5 py-3 text-sm font-bold text-white transition hover:bg-blue-700">Masuk</a>
        </div>
    </div>
</nav>

<main class="pt-0">

    {{-- HERO --}}
    <section class="relative overflow-hidden bg-[#f5f5f5]">
        <div
            class="mx-auto mt-4 grid max-w-[1180px] items-center gap-8 px-5 pb-16 pt-4 sm:px-6 lg:mt-5 lg:grid-cols-[0.96fr_1.04fr] lg:px-8 lg:pb-24 lg:pt-8">
            <div class="max-w-[560px] justify-self-start">
                <h1
                    class="text-[40px] font-black leading-[1.08] tracking-[-0.04em] text-slate-950 sm:text-[52px] lg:text-[58px]">
                    Periksa, Perbaiki,<br>
                    dan Siapkan Naskah<br>
                    Akademik Anda
                </h1>

                <p class="mt-6 max-w-[530px] text-[15px] leading-7 text-slate-600 sm:text-[16px]">
                    NaskahCek membantu Anda memastikan naskah lebih rapi, mudah dipahami,
                    dan siap digunakan untuk kebutuhan akademik maupun publikasi.
                </p>

                <div class="mt-7 space-y-3 text-[14px] font-medium text-slate-700">
                    <div class="flex items-center gap-3">
                        <span
                            class="flex h-5 w-5 items-center justify-center rounded-full bg-blue-600 text-[11px] font-black text-white">✓</span>
                        <span>Cek similarity dan temukan sumber yang relevan</span>
                    </div>
                    <div class="flex items-center gap-3">
                        <span
                            class="flex h-5 w-5 items-center justify-center rounded-full bg-blue-600 text-[11px] font-black text-white">✓</span>
                        <span>Dapatkan saran perbaikan dengan bantuan AI</span>
                    </div>
                    <div class="flex items-center gap-3">
                        <span
                            class="flex h-5 w-5 items-center justify-center rounded-full bg-blue-600 text-[11px] font-black text-white">✓</span>
                        <span>Kelola naskah dalam satu tempat</span>
                    </div>
                </div>

                <div class="mt-8 flex flex-col gap-3 sm:flex-row">
                    <a href="{{ auth()->check() ? (auth()->user()->isAdmin() ? route('admin.dashboard') : route('user.plagiarism.index')) : route('register') }}"
                        class="inline-flex items-center justify-center rounded-xl bg-blue-600 px-6 py-3.5 text-[14px] font-bold text-white shadow-lg shadow-blue-200 transition hover:bg-blue-700">
                        Mulai Pemeriksaan
                    </a>
                    <a href="#fitur"
                        class="inline-flex items-center justify-center rounded-xl border border-slate-300 bg-white px-6 py-3.5 text-[14px] font-bold text-slate-800 transition hover:border-blue-300 hover:bg-blue-50">
                        Lihat Fitur
                    </a>
                </div>
            </div>

            <div class="relative mt-6 w-full lg:mt-10 lg:justify-self-end lg:pl-4">
                <div
                    class="absolute left-1/2 top-1/2 h-[115%] w-[110%] -translate-x-1/2 -translate-y-1/2 rounded-full bg-[#dfeeff]">
                </div>
                <div
                    class="relative mx-auto w-full max-w-[520px] overflow-hidden rounded-[22px] border border-slate-200 bg-white shadow-[0_18px_40px_rgba(15,23,42,0.08)] ring-1 ring-blue-100">
                    <div class="flex items-center justify-between bg-blue-600 px-4 py-3.5 text-white">
                        <div>
                            <p class="text-[10px] font-bold uppercase tracking-[0.18em] text-blue-100">Hasil Pemeriksaan
                            </p>
                            <p class="mt-1 text-sm font-bold">Laporan Originalitas Naskah</p>
                        </div>
                        <span
                            class="rounded-full bg-white px-2.5 py-1 text-[10px] font-semibold text-blue-700">Selesai</span>
                    </div>

                    <div class="space-y-5 p-4 sm:p-5">
                        <div class="rounded-2xl border border-blue-100 bg-blue-50 p-4">
                            <div class="flex items-end justify-between gap-3">
                                <div>
                                    <p class="text-[10px] font-bold uppercase tracking-wider text-slate-700">Tingkat
                                        Kemiripan</p>
                                    <p class="mt-1 text-xs text-slate-700">Similarity score</p>
                                </div>
                                <span class="text-3xl font-black tracking-tight text-blue-600">18%</span>
                            </div>
                            <div class="mt-3 h-2.5 overflow-hidden rounded-full bg-white">
                                <div class="h-full w-[18%] rounded-full bg-blue-600"></div>
                            </div>
                        </div>

                        <div class="grid grid-cols-2 gap-2.5">
                            <div class="rounded-xl border border-slate-200 bg-white p-3">
                                <p class="text-[9px] font-bold uppercase tracking-wider text-slate-700">Kata</p>
                                <p class="mt-1 text-lg font-black text-slate-900">12,456</p>
                            </div>
                            <div class="rounded-xl border border-slate-200 bg-white p-3">
                                <p class="text-[9px] font-bold uppercase tracking-wider text-slate-700">Karakter</p>
                                <p class="mt-1 text-lg font-black text-slate-900">89,012</p>
                            </div>
                            <div class="rounded-xl border border-slate-200 bg-white p-3">
                                <p class="text-[9px] font-bold uppercase tracking-wider text-slate-700">Halaman</p>
                                <p class="mt-1 text-lg font-black text-slate-900">23</p>
                            </div>
                            <div class="rounded-xl border border-slate-200 bg-white p-3">
                                <p class="text-[9px] font-bold uppercase tracking-wider text-slate-700">Sumber</p>
                                <p class="mt-1 text-lg font-black text-slate-900">156</p>
                            </div>
                        </div>

                        <div class="rounded-2xl border border-slate-200 bg-slate-50 p-3.5">
                            <div class="flex items-start justify-between gap-3">
                                <div>
                                    <p class="text-[9px] font-bold uppercase tracking-wider text-slate-400">File
                                        diperiksa</p>
                                    <p class="mt-1 text-[13px] font-bold text-slate-800">skripsi_final.docx</p>
                                    <p class="mt-0.5 text-[11px] text-slate-700">Diunggah 26 Mei 2026 · 2.4 MB</p>
                                </div>
                                <div
                                    class="shrink-0 rounded-lg bg-emerald-100 px-2.5 py-1.5 text-[10px] font-bold text-emerald-700">
                                    ✓ Aman</div>
                            </div>
                        </div>

                        <div
                            class="flex items-start gap-2 rounded-xl border border-emerald-200 bg-emerald-50 px-3 py-3 text-[11px] font-semibold leading-5 text-emerald-700">
                            <span
                                class="mt-0.5 flex h-4 w-4 items-center justify-center rounded-full bg-emerald-600 text-[9px] font-black text-white">✓</span>
                            <span>Tidak ditemukan indikasi similarity yang signifikan.</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    {{-- FEATURE INTRO --}}
    <section id="fitur" class="bg-[#f7faff] py-20 sm:py-24">
        <div class="mx-auto max-w-[1180px] px-5 sm:px-6 lg:px-8">
            <div class="mx-auto max-w-4xl text-center">
                <p class="text-[10px] font-black uppercase tracking-[0.25em] text-blue-600">Semua yang Anda butuhkan</p>
                <h2 class="mt-3 text-3xl font-black leading-tight tracking-[-0.035em] text-slate-950 sm:text-[38px]">
                    Mengecek, memperbaiki, dan menyiapkan<br class="hidden sm:block"> naskah akademik dengan lebih rapi
                </h2>
                <p class="mx-auto mt-4 max-w-2xl text-sm leading-6 text-slate-500">
                    Setiap fitur dirancang untuk mendukung proses penulisan akademik Anda, dari pemeriksaan awal hingga
                    naskah siap dipertanggungjawabkan.
                </p>
            </div>

            <div class="mt-12 grid grid-cols-2 gap-4 lg:grid-cols-5">
                @php
                $features = [
                [
                'title' => 'Pemeriksaan Similarity',
                'desc' => 'Deteksi kemiripan naskah dengan sumber online secara cepat dan akurat.',
                'iconBg' => 'bg-blue-600 text-white group-hover:bg-blue-700 group-hover:text-white',
                'icon' => '<svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                </svg>'
                ],
                [
                'title' => 'Perbaikan AI',
                'desc' => 'Dapatkan saran perbaikan pada struktur, bahasa, dan gaya tulisan.',
                'iconBg' => 'bg-violet-600 text-white group-hover:bg-violet-700 group-hover:text-white',
                'icon' => '<svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M13 10V3L4 14h7v7l9-11h-7z" />
                </svg>'
                ],
                [
                'title' => 'Manajemen Naskah',
                'desc' => 'Kelola berbagai versi naskah dalam satu tempat dengan lebih teratur.',
                'iconBg' => 'bg-emerald-600 text-white group-hover:bg-emerald-700 group-hover:text-white',
                'icon' => '<svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10" />
                </svg>'
                ],
                [
                'title' => 'Laporan Lengkap',
                'desc' => 'Dapatkan ringkasan hasil yang mudah dibaca dan dipahami.',
                'iconBg' => 'bg-amber-600 text-white group-hover:bg-amber-700 group-hover:text-white',
                'icon' => '<svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
                </svg>'
                ],
                [
                'title' => 'Keamanan Data',
                'desc' => 'Naskah Anda dikelola dengan kontrol akses yang lebih aman.',
                'iconBg' => 'bg-rose-600 text-white group-hover:bg-rose-700 group-hover:text-white',
                'icon' => '<svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" />
                </svg>'
                ],
                ];
                @endphp

                @foreach ($features as $feature)
                <div
                    class="group flex flex-col items-center rounded-2xl border border-slate-200 bg-white p-6 text-center shadow-sm transition duration-200 hover:-translate-y-0.5 hover:border-slate-300 hover:bg-slate-50">
                    <div
                        class="flex h-12 w-12 items-center justify-center rounded-2xl {{ $feature['iconBg'] }} transition duration-200">
                        {!! $feature['icon'] !!}
                    </div>
                    <h3 class="mt-4 text-[13px] font-extrabold text-slate-900">{{ $feature['title'] }}</h3>
                    <p class="mt-2 text-[11px] leading-5 text-slate-500">{{ $feature['desc'] }}</p>
                </div>
                @endforeach
            </div>

            <div
                class="mt-6 flex flex-wrap items-center justify-center gap-x-6 gap-y-3 rounded-2xl border border-slate-200 bg-white px-5 py-4 text-[11px] font-semibold text-slate-500 shadow-sm">
                <span class="flex items-center gap-2"><span class="text-blue-600">✓</span> Akurat & terpercaya</span>
                <span class="hidden h-4 w-px bg-slate-200 sm:block"></span>
                <span class="flex items-center gap-2"><span class="text-blue-600">✓</span> Sumber relevan</span>
                <span class="hidden h-4 w-px bg-slate-200 sm:block"></span>
                <span class="flex items-center gap-2"><span class="text-blue-600">✓</span> Rekomendasi cerdas</span>
                <span class="hidden h-4 w-px bg-slate-200 sm:block"></span>
                <span class="flex items-center gap-2"><span class="text-blue-600">✓</span> Mudah digunakan</span>
            </div>
        </div>
    </section>
    </div>
    </section>

    {{-- MANAGEMENT --}}
    <section class="py-20 sm:py-24">
        <div class="mx-auto grid max-w-[1080px] items-center gap-12 px-5 sm:px-6 lg:grid-cols-[0.9fr_1.1fr] lg:px-8">
            <div>
                <p class="text-[10px] font-black uppercase tracking-[0.25em] text-blue-600">Kelola dengan mudah</p>
                <h2 class="mt-3 text-3xl font-black leading-tight tracking-[-0.035em] text-slate-950 sm:text-[39px]">
                    Kelola Naskah Akademik<br> dalam Satu Platform
                </h2>
                <p class="mt-4 max-w-md text-sm leading-6 text-slate-500">
                    Unggah, kelola, dan lacak perkembangan naskah Anda tanpa harus berpindah-pindah aplikasi.
                </p>
            </div>

            <div class="space-y-3">
                @foreach ([
                ['title'=>'Dashboard Naskah','desc'=>'Pantau status dan hasil pemeriksaan naskah
                Anda.','iconBg'=>'bg-blue-600 text-white','cardBg'=>'border border-blue-200
                bg-blue-50/80','style'=>'background-color:#eff6ff;border-color:#bfdbfe;','icon'=>'<svg class="h-5 w-5"
                    fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8"
                        d="M4 13h6V4H4v9Zm0 7h6v-4H4v4Zm10 0h6v-9h-6v9Zm0-16v4h6V4h-6Z" />
                </svg>'],
                ['title'=>'Versi & Riwayat','desc'=>'Lihat perubahan dan bandingkan versi
                naskah.','iconBg'=>'bg-amber-500 text-white','cardBg'=>'border border-amber-200
                bg-amber-50/80','style'=>'background-color:#fff7ed;border-color:#fdba74;','icon'=>'<svg class="h-5 w-5"
                    fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8"
                        d="M12 8v4l2.5 2.5M21 12a9 9 0 1 1-2.64-6.36M21 4v5h-5" />
                </svg>'],
                ['title'=>'Kolaborasi','desc'=>'Ajak tim untuk bekerja bersama dalam satu
                naskah.','iconBg'=>'bg-emerald-600 text-white','cardBg'=>'border border-emerald-200
                bg-emerald-50/80','style'=>'background-color:#ecfdf5;border-color:#86efac;','icon'=>'<svg
                    class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8"
                        d="M16 20v-1.5a3.5 3.5 0 0 0-3.5-3.5h-5A3.5 3.5 0 0 0 4 18.5V20m6-8a3.5 3.5 0 1 0 0-7 3.5 3.5 0 0 0 0 7Zm6-6.5a3 3 0 0 1 0 5.83M20 20v-1.5a3.5 3.5 0 0 0-2.5-3.35" />
                </svg>']
                ] as $item)
                <div class="group flex items-center gap-4 rounded-2xl p-4 shadow-sm transition-all duration-200 hover:-translate-y-0.5 hover:shadow-md {{ $item['cardBg'] }}"
                    style="{{ $item['style'] }}">
                    <div
                        class="flex h-11 w-11 shrink-0 items-center justify-center rounded-xl {{ $item['iconBg'] }} shadow-sm transition duration-200">
                        {!! $item['icon'] !!}</div>
                    <div class="min-w-0 flex-1">
                        <h3 class="text-sm font-extrabold text-slate-900">{{ $item['title'] }}</h3>
                        <p class="mt-1 text-xs leading-5 text-slate-700">{{ $item['desc'] }}</p>
                    </div>
                    <span
                        class="flex h-8 w-8 shrink-0 items-center justify-center rounded-full bg-white/70 text-slate-700 shadow-sm ring-1 ring-white/80 transition duration-200 group-hover:bg-slate-900 group-hover:text-white group-hover:ring-slate-900"
                        aria-hidden="true">
                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="m9 5 7 7-7 7" />
                        </svg>
                    </span>
                </div>
                @endforeach
            </div>
        </div>
    </section>

    {{-- HOW IT WORKS --}}
    <section class="bg-[#f4f8ff] py-20 sm:py-24">
        <div class="mx-auto w-full max-w-[1100px] min-w-0 px-5 sm:px-6 lg:w-[54vw] lg:px-0">
            <div class="text-center">
                <p class="text-[10px] font-black uppercase tracking-[0.25em] text-blue-600">Cara kerja</p>
                <h2 class="mt-3 text-3xl font-black leading-tight tracking-[-0.035em] text-slate-950 sm:text-[38px]">
                    Perjalanan Naskah Anda
                </h2>
                <p class="mx-auto mt-4 max-w-[720px] text-sm leading-6 text-slate-600">
                    Dari dokumen mentah hingga naskah yang lebih siap digunakan, semua prosesnya mudah dan cepat.
                </p>
            </div>

            <div
                class="relative mt-12 grid grid-cols-2 items-stretch justify-center gap-3 lg:flex lg:items-center lg:gap-3">
                @php
                $steps = [
                [
                'title' => 'Unggah',
                'desc' => 'Unggah file naskah Anda dalam format dokumen.',
                'badge' => 'bg-[#75A9EC]',
                'svg' => '<svg width="220" height="150" viewBox="0 0 220 150" xmlns="http://www.w3.org/2000/svg">
                    <ellipse cx="110" cy="130" rx="80" ry="12" fill="#EAF3FF" />
                    <path d="M52 94 C33 82 30 68 35 56 C52 66 61 82 52 94Z" fill="#5ED4BD" />
                    <path d="M60 93 C53 76 58 62 70 54 C74 70 71 85 60 93Z" fill="#8BE0D0" />
                    <path d="M60 20 Q60 13 68 13 H122 L145 36 V103 Q145 111 137 111 H68 Q60 111 60 103Z" fill="white"
                        stroke="#DCE8F7" stroke-width="3" />
                    <path d="M122 13 V31 Q122 35 126 35 H145" fill="#EEF5FF" />
                    <rect x="76" y="46" width="52" height="7" rx="3" fill="#A9C8F5" />
                    <rect x="76" y="62" width="62" height="7" rx="3" fill="#BDD5F5" />
                    <rect x="76" y="78" width="46" height="7" rx="3" fill="#A9C8F5" />
                    <rect x="76" y="94" width="58" height="7" rx="3" fill="#D0DFF2" />
                    <path
                        d="M140 85 C140 77 146 70 154 70 C157 62 164 57 174 57 C184 57 192 64 193 74 C201 75 208 81 208 89 C208 99 200 106 190 106 H154 C146 106 140 97 140 89Z"
                        fill="#367EDC" />
                    <path d="M154 85 L171 68 L188 85" fill="none" stroke="white" stroke-width="5" stroke-linecap="round"
                        stroke-linejoin="round" />
                    <path d="M171 69 V96" stroke="white" stroke-width="5" stroke-linecap="round" />
                </svg>'
                ],
                [
                'title' => 'Periksa',
                'desc' => 'Sistem memeriksa similarity dan sumber naskah.',
                'badge' => 'bg-[#2FC9A8]',
                'svg' => '<svg width="220" height="150" viewBox="0 0 220 150" xmlns="http://www.w3.org/2000/svg">
                    <ellipse cx="110" cy="130" rx="78" ry="12" fill="#EAF3FF" />
                    <path d="M58 20 Q58 13 66 13 H123 L145 36 V104 Q145 112 137 112 H67 Q58 112 58 104Z" fill="white"
                        stroke="#DCE8F7" stroke-width="3" />
                    <path d="M123 13 V31 Q123 35 127 35 H145" fill="#EEF5FF" />
                    <rect x="75" y="46" width="50" height="7" rx="3" fill="#8EB9F0" />
                    <rect x="75" y="62" width="60" height="7" rx="3" fill="#B5D0F2" />
                    <rect x="75" y="78" width="43" height="7" rx="3" fill="#8EB9F0" />
                    <rect x="75" y="94" width="53" height="7" rx="3" fill="#CFDFF2" />
                    <circle cx="151" cy="82" r="24" fill="#E5F0FF" stroke="#347DDB" stroke-width="6" />
                    <line x1="168" y1="100" x2="188" y2="119" stroke="#263F68" stroke-width="8"
                        stroke-linecap="round" />
                    <circle cx="171" cy="30" r="15" fill="#40CDB0" />
                    <path d="M163 30 L168 35 L180 23" fill="none" stroke="white" stroke-width="4" stroke-linecap="round"
                        stroke-linejoin="round" />
                </svg>'
                ],
                [
                'title' => 'Perbaiki',
                'desc' => 'Dapatkan saran perbaikan berbasis AI.',
                'badge' => 'bg-[#8C55DF]',
                'svg' => '<svg width="220" height="150" viewBox="0 0 220 150" xmlns="http://www.w3.org/2000/svg">
                    <ellipse cx="110" cy="130" rx="78" ry="12" fill="#EAF3FF" />
                    <path d="M58 20 Q58 13 66 13 H123 L145 36 V104 Q145 112 137 112 H67 Q58 112 58 104Z" fill="white"
                        stroke="#DCE8F7" stroke-width="3" />
                    <path d="M123 13 V31 Q123 35 127 35 H145" fill="#EEF5FF" />
                    <rect x="74" y="44" width="58" height="9" rx="4" fill="#FFD54F" />
                    <rect x="74" y="62" width="50" height="7" rx="3" fill="#A9C8F5" />
                    <rect x="74" y="78" width="62" height="7" rx="3" fill="#BFD5F0" />
                    <rect x="74" y="94" width="42" height="7" rx="3" fill="#A9C8F5" />
                    <g transform="rotate(35 152 82)">
                        <rect x="142" y="47" width="18" height="68" rx="8" fill="#367EDC" />
                        <rect x="142" y="47" width="18" height="12" rx="6" fill="#79ADEF" />
                        <path d="M142 115 L152 131 L162 115Z" fill="#F2C7A5" />
                        <path d="M152 131 L148 124 L156 124Z" fill="#263F68" />
                    </g>
                </svg>'
                ],
                [
                'title' => 'Siap Publikasi',
                'desc' => 'Naskah lebih siap untuk jurnal atau pengajuan.',
                'badge' => 'bg-[#42B8D4]',
                'svg' => '<svg width="220" height="150" viewBox="0 0 220 150" xmlns="http://www.w3.org/2000/svg">
                    <ellipse cx="110" cy="130" rx="78" ry="12" fill="#EAF3FF" />
                    <path d="M62 20 Q62 13 70 13 H132 L155 36 V104 Q155 112 147 112 H71 Q62 112 62 104Z" fill="white"
                        stroke="#DCE8F7" stroke-width="3" />
                    <path d="M132 13 V31 Q132 35 136 35 H155" fill="#EEF5FF" />
                    <rect x="79" y="47" width="52" height="7" rx="3" fill="#A9C8F5" />
                    <rect x="79" y="63" width="62" height="7" rx="3" fill="#BFD5F0" />
                    <rect x="79" y="79" width="44" height="7" rx="3" fill="#A9C8F5" />
                    <rect x="79" y="95" width="56" height="7" rx="3" fill="#D0DFF2" />
                    <circle cx="165" cy="92" r="24" fill="#42CDB0" />
                    <path d="M152 92 L160 100 L178 82" fill="none" stroke="white" stroke-width="5"
                        stroke-linecap="round" stroke-linejoin="round" />
                </svg>'
                ],
                ];
                @endphp

                @foreach ($steps as $index => $step)
                @if ($index > 0)
                <div class="hidden items-center justify-center lg:flex">
                    <svg width="58" height="30" viewBox="0 0 70 30" aria-hidden="true">
                        <path d="M5 15 H57" stroke="#8DB5E7" stroke-width="3" stroke-linecap="round" />
                        <path d="M47 6 L58 15 L47 24" fill="none" stroke="#8DB5E7" stroke-width="3"
                            stroke-linecap="round" stroke-linejoin="round" />
                    </svg>
                </div>
                @endif

                <div class="w-full lg:max-w-[190px] lg:flex-none">
                    <div
                        class="flex h-full min-h-[210px] flex-col justify-start rounded-[18px] border border-[#edf2fb] bg-white/90 p-2.75 shadow-[0_10px_20px_rgba(90,124,165,0.07)] backdrop-blur-sm lg:min-h-[232px]">
                        <div
                            class="mb-2 flex h-[84px] shrink-0 items-center justify-center overflow-hidden rounded-[14px] bg-[#f5f8ff] sm:h-[104px] lg:mb-3">
                            {!! $step['svg'] !!}
                        </div>

                        <div
                            class="mx-auto mb-2.5 flex h-7 w-7 shrink-0 items-center justify-center rounded-full border-[2.5px] border-white text-[9px] font-black text-white shadow-[0_6px_14px_rgba(56,105,170,0.18)] {{ $step['badge'] }}">
                            {{ str_pad($index + 1, 2, '0', STR_PAD_LEFT) }}
                        </div>

                        <h3 class="text-center text-[13px] font-extrabold leading-none text-slate-900">
                            {{ $step['title'] }}</h3>
                        <p
                            class="mx-auto mt-1.5 max-w-[136px] flex-1 text-center text-[10.5px] leading-[1.35] text-slate-500">
                            {{ $step['desc'] }}
                        </p>
                    </div>
                </div>
                @endforeach
            </div>
        </div>
    </section>

    {{-- AI REWRITE --}}
    <section class="py-20 sm:py-24">
        <div class="mx-auto grid max-w-[1080px] items-center gap-10 px-5 sm:px-6 lg:grid-cols-[0.7fr_1.3fr] lg:px-8">
            <div>
                <p class="text-[10px] font-black uppercase tracking-[0.25em] text-blue-600">Bantuan AI</p>
                <h2 class="mt-3 text-3xl font-black leading-tight tracking-[-0.035em] text-slate-950 sm:text-[39px]">
                    Perbaiki Naskah<br>dengan Bantuan AI
                </h2>
                <p class="mt-4 max-w-sm text-sm leading-6 text-slate-500">
                    Dapatkan saran perbaikan instan untuk membantu meningkatkan kualitas tulisan Anda.
                </p>
                <button type="button" onclick="showComingSoonAlert()"
                    class="mt-7 inline-flex items-center gap-2 rounded-xl bg-blue-600 px-5 py-3 text-xs font-bold text-white transition hover:bg-blue-700">
                    <span>Coba Perbaikan AI</span>
                    <span
                        class="rounded-full bg-white px-2 py-0.5 text-[9px] font-black uppercase tracking-wider text-blue-700 border border-white">Soon</span>
                </button>
            </div>

            <div
                class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-xl lg:max-w-[680px] lg:justify-self-end">
                <div class="flex items-center justify-between bg-slate-950 px-4 py-3 text-white">
                    <span class="text-[11px] font-bold">Asisten Perbaikan AI</span>
                    <span class="rounded-md bg-slate-700 px-2 py-1 text-[9px] text-white">Tampilkan
                        perubahan</span>
                </div>
                <div class="grid gap-3 p-4 sm:grid-cols-2">
                    <div class="min-h-[220px] rounded-xl border border-slate-400 bg-slate-300 p-4">
                        <div class="flex items-center justify-between">
                            <span class="text-[9px] font-black uppercase tracking-wider text-slate-900">Original</span>
                            <span class="text-[9px] text-slate-900">×</span>
                        </div>
                        <p class="mt-3 text-xs leading-5 text-slate-900">
                            Penelitian ini bertujuan untuk mengetahui faktor yang mempengaruhi kepuasan pengguna
                            dalam menggunakan layanan aplikasi transportasi online.
                        </p>
                        <div class="mt-4 rounded-lg bg-white px-3 py-2 text-right text-xs font-black text-red-500">
                            42%</div>
                    </div>

                    <div class="min-h-[220px] rounded-xl border border-emerald-500 bg-emerald-200 p-4">
                        <div class="flex items-center justify-between">
                            <span class="text-[9px] font-black uppercase tracking-wider text-emerald-800">Saran
                                Perbaikan</span>
                            <span class="text-[9px] text-emerald-800">×</span>
                        </div>
                        <p class="mt-3 text-xs leading-5 text-slate-700">
                            Studi ini dilakukan untuk mengidentifikasi dan menganalisis faktor yang memengaruhi
                            tingkat kepuasan pengguna terhadap layanan aplikasi transportasi online.
                        </p>
                        <div class="mt-4 rounded-lg bg-white px-3 py-2 text-right text-xs font-black text-emerald-600">
                            8%</div>
                    </div>
                </div>
                <div class="flex gap-5 border-t border-slate-200 px-4 py-3 text-[9px] font-semibold text-slate-700">
                    <span>ⓘ Penjelasan</span>
                    <span>✦ Alternatif</span>
                    <span>✓ Simpan perubahan</span>
                </div>
            </div>
        </div>
    </section>

    {{-- SOURCES / JOURNAL --}}
    <section id="sumber" class="bg-[#f7faff] py-20 sm:py-24">
        <div class="mx-auto max-w-[1080px] px-5 sm:px-6 lg:px-8">
            <div class="grid items-center gap-10 lg:grid-cols-[0.72fr_1.28fr]">
                <div>
                    <p class="text-[10px] font-black uppercase tracking-[0.25em] text-blue-600">Sumber terpercaya
                    </p>
                    <h2 class="mt-3 text-3xl font-black tracking-[-0.035em] text-slate-950 sm:text-[36px]">Susun
                        Draft Jurnal dengan AI</h2>
                    <p class="mt-3 max-w-sm text-sm leading-6 text-slate-500">
                        Gunakan referensi dan panduan penulisan untuk membantu menyiapkan naskah akademik.
                    </p>
                    <a href="{{ route('templates.index') }}"
                        class="mt-6 inline-flex items-center gap-2 rounded-xl bg-blue-600 px-5 py-3 text-xs font-bold text-white transition hover:bg-blue-700">
                        <span>Lihat Template Jurnal</span>
                        <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5"
                                d="M14 5l7 7m0 0l-7 7m7-7H3" />
                        </svg>
                    </a>
                </div>

                <div class="grid grid-cols-2 gap-3 sm:grid-cols-5">
                    @foreach ([
                    ['src'=>'images/scopus.png','alt'=>'Scopus'],
                    ['src'=>'images/doajj.png','alt'=>'DOAJ'],
                    ['src'=>'images/googlescholarr.png','alt'=>'Google Scholar'],
                    ['src'=>'images/garudaa.jpg','alt'=>'Garuda'],
                    ['src'=>'images/sintaa.jpg','alt'=>'SINTA'],
                    ] as $source)
                    <div
                        class="group flex h-24 items-center justify-center rounded-lg border border-slate-300 bg-white p-2 shadow-sm transition-all duration-200 hover:-translate-y-0.5 hover:border-slate-400 hover:bg-slate-50">
                        <img src="{{ asset($source['src']) }}" alt="{{ $source['alt'] }}"
                            class="max-h-16 w-auto max-w-[88%] object-contain transition-transform duration-200 group-hover:scale-[1.03]">
                    </div>
                    @endforeach
                </div>
            </div>
        </div>
    </section>

    {{-- PRICING --}}
    <section id="harga" class="py-20 sm:py-24">
        <div class="mx-auto max-w-[1400px] px-5 sm:px-6 lg:px-8">
            <div class="mx-auto max-w-2xl text-center">
                <p class="text-[10px] font-black uppercase tracking-[0.25em] text-blue-600">Paket & Harga</p>
                <h2 class="mt-3 text-3xl font-black tracking-[-0.035em] text-slate-950 sm:text-[38px]">Pilih Paket
                    Sesuai Kebutuhan Anda</h2>
                <p class="mt-3 text-sm leading-6 text-slate-500">Pilih paket pengecekan plagiasi yang paling sesuai
                    dengan kebutuhan Anda.</p>
            </div>
            <div class="mx-auto mt-10 grid max-w-[1120px] gap-4 xl:grid-cols-4">
                <div
                    class="flex h-full flex-col rounded-2xl border border-slate-200 bg-white p-4 shadow-sm transition hover:-translate-y-0.5 hover:shadow-lg sm:p-5">
                    <div class="flex items-center justify-between">
                        <div class="flex items-center gap-2">
                            <span
                                class="flex h-5 w-5 items-center justify-center rounded-full bg-emerald-500 text-[10px] font-bold text-white">✓</span>
                            <h3 class="text-[14px] font-black tracking-[-0.02em] text-slate-900">Hemat 3x Cek Plagiasi
                                Turnitin</h3>
                        </div>
                    </div>
                    <p class="mt-4 text-[11px] font-semibold text-slate-500">(7 Hari)</p>
                    <p class="mt-2 text-[13px] leading-5 text-slate-600">Bug kamu yang lagi ngebut nyelesain tugas biar
                        selesai tepat waktu</p>
                    <p class="mt-7 text-[2rem] font-black tracking-[-0.03em] text-slate-900">Rp 20.000</p>
                    <p class="mt-1 text-sm font-semibold text-slate-500">Kuota</p>
                    <div class="mt-4 flex items-center gap-2 text-[13px] font-medium text-slate-700">
                        <span
                            class="flex h-4 w-4 items-center justify-center rounded-full border border-slate-700 bg-slate-800 text-[10px] font-bold text-white">✓</span>
                        3x cek plagiasi
                    </div>

                    <div class="mt-6 border-t border-slate-200 pt-4">
                        <p class="mb-3 text-sm font-bold text-slate-700">Benefit</p>
                        <ul class="space-y-2 text-[13px] text-slate-600">
                            <li class="flex items-start gap-2"><span
                                    class="mt-0.5 flex h-4 w-4 items-center justify-center rounded-full bg-emerald-500 text-[10px] font-bold text-white">✓</span>
                                Skip menu pembayaran</li>
                            <li class="flex items-start gap-2"><span
                                    class="mt-0.5 flex h-4 w-4 items-center justify-center rounded-full bg-emerald-500 text-[10px] font-bold text-white">✓</span>
                                Bisa cek sampai 800 halaman/file</li>
                            <li class="flex items-start gap-2"><span
                                    class="mt-0.5 flex h-4 w-4 items-center justify-center rounded-full bg-emerald-500 text-[10px] font-bold text-white">✓</span>
                                Dapat token 3x cek plagiasi</li>
                            <li class="flex items-start gap-2"><span
                                    class="mt-0.5 flex h-4 w-4 items-center justify-center rounded-full bg-emerald-500 text-[10px] font-bold text-white">✓</span>
                                Hasil langsung bisa di download</li>
                        </ul>
                    </div>

                    <a href="{{ route('register', ['package' => 'hemat-3']) }}"
                        onclick="showLoginRequiredAlert(event, 'hemat-3')"
                        class="mt-auto flex min-h-10 w-full items-center justify-center rounded-xl bg-orange-500 px-4 py-2.5 text-[13px] font-bold text-white transition hover:bg-orange-600">Beli
                        Paket</a>
                </div>

                <div
                    class="flex h-full flex-col rounded-2xl border border-slate-200 bg-white p-4 shadow-sm transition hover:-translate-y-0.5 hover:shadow-lg sm:p-5">
                    <div class="flex items-center justify-between">
                        <h3 class="text-[14px] font-black tracking-[-0.02em] text-slate-900">Praktis 10x Cek Plagiasi
                            Turnitin</h3>
                    </div>
                    <p class="mt-4 text-[11px] font-semibold text-slate-500">(14 Hari)</p>
                    <p class="mt-2 text-[13px] leading-5 text-slate-600">Buat kamu deadliners yang lagi ngerajin
                        revisian dan tugas</p>
                    <p class="mt-7 text-[2rem] font-black tracking-[-0.03em] text-slate-900">Rp 80.000</p>
                    <p class="mt-1 text-sm font-semibold text-slate-500">Kuota</p>
                    <div class="mt-4 flex items-center gap-2 text-[13px] font-medium text-slate-700">
                        <span
                            class="flex h-4 w-4 items-center justify-center rounded-full border border-slate-700 bg-slate-800 text-[10px] font-bold text-white">✓</span>
                        10x cek plagiasi
                    </div>

                    <div class="mt-6 border-t border-slate-200 pt-4">
                        <p class="mb-3 text-sm font-bold text-slate-700">Benefit</p>
                        <ul class="space-y-2 text-[13px] text-slate-600">
                            <li class="flex items-start gap-2"><span
                                    class="mt-0.5 flex h-4 w-4 items-center justify-center rounded-full bg-emerald-500 text-[10px] font-bold text-white">✓</span>
                                Skip menu pembayaran</li>
                            <li class="flex items-start gap-2"><span
                                    class="mt-0.5 flex h-4 w-4 items-center justify-center rounded-full bg-emerald-500 text-[10px] font-bold text-white">✓</span>
                                Bisa cek sampai 800 halaman/file</li>
                            <li class="flex items-start gap-2"><span
                                    class="mt-0.5 flex h-4 w-4 items-center justify-center rounded-full bg-emerald-500 text-[10px] font-bold text-white">✓</span>
                                Dapat token 10x cek plagiasi</li>
                            <li class="flex items-start gap-2"><span
                                    class="mt-0.5 flex h-4 w-4 items-center justify-center rounded-full bg-emerald-500 text-[10px] font-bold text-white">✓</span>
                                Hasil langsung bisa di download</li>
                        </ul>
                    </div>

                    <a href="{{ route('register', ['package' => 'praktis-10']) }}"
                        onclick="showLoginRequiredAlert(event, 'praktis-10')"
                        class="mt-auto flex min-h-10 w-full items-center justify-center rounded-xl bg-orange-500 px-4 py-2.5 text-[13px] font-bold text-white transition hover:bg-orange-600">Beli
                        Paket</a>
                </div>

                <div
                    class="flex h-full flex-col rounded-2xl border border-slate-200 bg-white p-4 shadow-sm transition hover:-translate-y-0.5 hover:shadow-lg sm:p-5">
                    <div class="flex items-center justify-between">
                        <h3 class="text-[14px] font-black tracking-[-0.02em] text-slate-900">Pro 30x Cek Plagiasi
                            Turnitin</h3>
                    </div>
                    <p class="mt-4 text-[11px] font-semibold text-slate-500">(3 Bulan)</p>
                    <p class="mt-2 text-[13px] leading-5 text-slate-600">Buat kamu mahasiswa akhir yang lagi ngerjain
                        skripsi biar ga bolak balik cek plagiasi</p>
                    <p class="mt-7 text-[2rem] font-black tracking-[-0.03em] text-slate-900">Rp 200.000</p>
                    <p class="mt-1 text-sm font-semibold text-slate-500">Kuota</p>
                    <div class="mt-4 flex items-center gap-2 text-[13px] font-medium text-slate-700">
                        <span
                            class="flex h-4 w-4 items-center justify-center rounded-full border border-slate-700 bg-slate-800 text-[10px] font-bold text-white">✓</span>
                        30x cek plagiasi
                    </div>

                    <div class="mt-6 border-t border-slate-200 pt-4">
                        <p class="mb-3 text-sm font-bold text-slate-700">Benefit</p>
                        <ul class="space-y-2 text-[13px] text-slate-600">
                            <li class="flex items-start gap-2"><span
                                    class="mt-0.5 flex h-4 w-4 items-center justify-center rounded-full bg-emerald-500 text-[10px] font-bold text-white">✓</span>
                                Skip menu pembayaran</li>
                            <li class="flex items-start gap-2"><span
                                    class="mt-0.5 flex h-4 w-4 items-center justify-center rounded-full bg-emerald-500 text-[10px] font-bold text-white">✓</span>
                                Bisa cek sampai 800 halaman/file</li>
                            <li class="flex items-start gap-2"><span
                                    class="mt-0.5 flex h-4 w-4 items-center justify-center rounded-full bg-emerald-500 text-[10px] font-bold text-white">✓</span>
                                Dapat token 30x cek plagiasi</li>
                            <li class="flex items-start gap-2"><span
                                    class="mt-0.5 flex h-4 w-4 items-center justify-center rounded-full bg-emerald-500 text-[10px] font-bold text-white">✓</span>
                                Hasil langsung bisa di download</li>
                        </ul>
                    </div>

                    <a href="{{ route('register', ['package' => 'pro-30']) }}"
                        onclick="showLoginRequiredAlert(event, 'pro-30')"
                        class="mt-auto flex min-h-10 w-full items-center justify-center rounded-xl bg-orange-500 px-4 py-2.5 text-[13px] font-bold text-white transition hover:bg-orange-600">Beli
                        Paket</a>
                </div>

                <div
                    class="flex h-full flex-col rounded-2xl border border-slate-200 bg-white p-4 shadow-sm transition hover:-translate-y-0.5 hover:shadow-lg sm:p-5">
                    <div class="flex items-center justify-between">
                        <h3 class="text-[14px] font-black tracking-[-0.02em] text-slate-900">Ultimato 100x Cek Plagiasi
                            Turnitin</h3>
                    </div>
                    <p class="mt-4 text-[11px] font-semibold text-slate-500">(6 Bulan)</p>
                    <p class="mt-2 text-[13px] leading-5 text-slate-600">Solusi buat kamu yang pengen cek buanyak
                        dokumen</p>
                    <p class="mt-7 text-[2rem] font-black tracking-[-0.03em] text-slate-900">Rp 800.000</p>
                    <p class="mt-1 text-sm font-semibold text-slate-500">Kuota</p>
                    <div class="mt-4 flex items-center gap-2 text-[13px] font-medium text-slate-700">
                        <span
                            class="flex h-4 w-4 items-center justify-center rounded-full border border-slate-700 bg-slate-800 text-[10px] font-bold text-white">✓</span>
                        100x cek plagiasi
                    </div>

                    <div class="mt-6 border-t border-slate-200 pt-4">
                        <p class="mb-3 text-sm font-bold text-slate-700">Benefit</p>
                        <ul class="space-y-2 text-[13px] text-slate-600">
                            <li class="flex items-start gap-2"><span
                                    class="mt-0.5 flex h-4 w-4 items-center justify-center rounded-full bg-emerald-500 text-[10px] font-bold text-white">✓</span>
                                Skip menu pembayaran</li>
                            <li class="flex items-start gap-2"><span
                                    class="mt-0.5 flex h-4 w-4 items-center justify-center rounded-full bg-emerald-500 text-[10px] font-bold text-white">✓</span>
                                Bisa cek sampai 800 halaman/file</li>
                            <li class="flex items-start gap-2"><span
                                    class="mt-0.5 flex h-4 w-4 items-center justify-center rounded-full bg-emerald-500 text-[10px] font-bold text-white">✓</span>
                                Dapat token 100x cek plagiasi</li>
                            <li class="flex items-start gap-2"><span
                                    class="mt-0.5 flex h-4 w-4 items-center justify-center rounded-full bg-emerald-500 text-[10px] font-bold text-white">✓</span>
                                Hasil langsung bisa di download</li>
                        </ul>
                    </div>

                    <a href="{{ route('register', ['package' => 'ultimato-100']) }}"
                        onclick="showLoginRequiredAlert(event, 'ultimato-100')"
                        class="mt-auto flex min-h-10 w-full items-center justify-center rounded-xl bg-orange-500 px-4 py-2.5 text-[13px] font-bold text-white transition hover:bg-orange-600">Beli
                        Paket</a>
                </div>
            </div>
        </div>
    </section>

    {{-- FAQ --}}
    <section id="faq" class="relative overflow-hidden bg-slate-50 py-24 sm:py-32">
        <div class="relative mx-auto max-w-[1180px] px-5 sm:px-6 lg:px-8" x-data="{ open: 1 }">
            <div class="mx-auto max-w-2xl text-center">
                <p class="text-[10px] font-black uppercase tracking-[0.25em] text-blue-600">
                    PUSAT BANTUAN & FAQ
                </p>
                <h2
                    class="mt-4 text-3xl font-extrabold tracking-tight text-slate-900 sm:text-4xl lg:text-[44px] lg:leading-[1.15]">
                    Pertanyaan yang Sering Diajukan
                </h2>
                <p class="mt-4 text-[15px] leading-relaxed text-slate-600">
                    Segala hal yang perlu Anda ketahui tentang akurasi pemeriksaan, keamanan dokumen, dan format laporan
                    di NaskahCek.
                </p>
            </div>

            @php
            $faqs = [
            1 => [
            'q' => 'Bagaimana cara kerja pengecekan plagiarisme di NaskahCek?',
            'a' => 'Sistem mengekstrak teks dari naskah, memecahnya menjadi potongan frasa (n-grams), lalu
            membandingkannya secara komprehensif dengan repositori akademik, jurnal ilmiah, dan miliaran artikel web
            secara real-time.'
            ],
            2 => [
            'q' => 'Format file dokumen apa saja yang didukung?',
            'a' => 'NaskahCek mendukung format dokumen standar akademik seperti Microsoft Word (.docx, .doc) dan PDF
            (.pdf). Teks diekstrak otomatis tanpa mengubah struktur asli dokumen.'
            ],
            3 => [
            'q' => 'Apakah naskah saya aman dan tidak masuk repositori publik?',
            'a' => 'Dokumen Anda 100% aman dan privat. NaskahCek tidak mempublikasikan file Anda ke database terbuka,
            sehingga naskah Anda tidak akan terdeteksi plagiat atas nama karya lain di kemudian hari.'
            ],
            4 => [
            'q' => 'Berapa lama waktu yang dibutuhkan untuk 1x pemeriksaan?',
            'a' => 'Pemeriksaan standar selesai dalam rentang 15 hingga 60 detik tergantung pada jumlah halaman dan
            kepadatan sitasi dokumen Anda.'
            ],
            5 => [
            'q' => 'Apakah laporan hasil pemeriksaan bisa diunduh?',
            'a' => 'Bisa. Anda dapat mengunduh laporan PDF resmi yang berisi skor similarity, highlight teks berwarna,
            dan daftar lengkap tautan sumber yang terdeteksi.'
            ],
            6 => [
            'q' => 'Dari mana saja pangkalan data pembanding yang digunakan?',
            'a' => 'Pemeriksaan mencakup database repositori kampus, jurnal bereputasi (DOAJ, Crossref, OAI-PMH), serta
            indeks miliaran publikasi web global.'
            ],
            7 => [
            'q' => 'Bisakah mengecualikan Daftar Pustaka dan Kutipan Langsung?',
            'a' => 'Ya, sistem menyediakan filter pintar untuk mengabaikan (exclude) daftar pustaka, kutipan bertanda
            petik, dan halaman judul agar skor kemiripan lebih objektif.'
            ],
            8 => [
            'q' => 'Bagaimana jika tingkat kemiripan naskah saya masih tinggi?',
            'a' => 'Laporan kami menandai kalimat mirip beserta sumber aslinya secara transparan, sehingga Anda dapat
            dengan mudah melakukan parafrase atau menyempurnakan sitasi.'
            ],
            ];
            @endphp

            <div class="mt-14 grid items-start gap-5 lg:grid-cols-2">
                @foreach ($faqs as $id => $faq)
                <div class="overflow-hidden rounded-2xl border border-slate-200 bg-white transition-all duration-200 hover:border-slate-300 hover:bg-slate-50"
                    :class="open === {{ $id }} ? 'border-blue-200 bg-blue-50/40' : ''">
                    <button @click="open = open === {{ $id }} ? null : {{ $id }}"
                        class="flex w-full items-start justify-between gap-4 p-6 text-left focus:outline-none">
                        <span class="text-[15px] font-bold text-slate-900 leading-snug transition-colors"
                            :class="open === {{ $id }} ? 'text-blue-600' : ''">
                            {{ $faq['q'] }}
                        </span>
                        <span
                            class="mt-0.5 flex h-7 w-7 shrink-0 items-center justify-center rounded-xl bg-slate-100 text-slate-500 transition-all duration-200"
                            :class="open === {{ $id }} ? 'rotate-180 bg-blue-100 text-blue-600' : ''">
                            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5"
                                    d="M19 9l-7 7-7-7" />
                            </svg>
                        </span>
                    </button>
                    <div x-show="open === {{ $id }}" x-collapse>
                        <div
                            class="border-t border-slate-100 px-6 pb-6 pt-3 text-[13.5px] leading-relaxed text-slate-600">
                            {{ $faq['a'] }}
                        </div>
                    </div>
                </div>
                @endforeach
            </div>

            {{-- Help banner footer inside FAQ --}}
            <div class="mt-12 rounded-2xl border border-slate-200 bg-white p-5 shadow-sm sm:p-6">
                <div class="flex flex-col items-center justify-between gap-4 sm:flex-row">
                    <div class="text-center sm:text-left">
                        <h4 class="text-sm font-bold text-slate-900">Masih punya pertanyaan lain?</h4>
                        <p class="mt-1 text-xs text-slate-500">Tim kami siap membantu Anda kapan saja.</p>
                    </div>
                    <a href="#footer"
                        class="inline-flex items-center gap-2 rounded-xl bg-white px-5 py-2.5 text-xs font-bold text-slate-800 shadow-sm border border-slate-200 transition hover:bg-slate-50 hover:border-slate-300">
                        <svg class="h-4 w-4 text-blue-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                        </svg>
                        Hubungi Tim Support
                    </a>
                </div>
            </div>
        </div>
    </section>

    {{-- CTA --}}
    <section
        class="w-full bg-gradient-to-br from-blue-600 via-blue-600 to-blue-700 px-6 py-14 text-center text-white sm:px-10 m-0 border-0 rounded-none">
        <div class="mx-auto max-w-4xl">
            <p class="text-[10px] font-black uppercase tracking-[0.25em] text-blue-100">Mulai sekarang</p>
            <h2 class="mx-auto mt-3 max-w-2xl text-3xl font-black leading-tight tracking-[-0.03em] sm:text-[42px]">
                Siap meningkatkan kualitas naskah akademik Anda?
            </h2>
            <p class="mx-auto mt-4 max-w-xl text-sm leading-6 text-blue-100">
                Mulai dari pemeriksaan hingga perbaikan naskah dalam satu platform.
            </p>
            <div class="mt-8 flex flex-col justify-center gap-3 sm:flex-row">
                <a href="{{ route('free.check.index') }}"
                    class="inline-flex justify-center rounded-xl bg-white px-6 py-3 text-xs font-black text-blue-700 shadow-lg transition hover:bg-blue-50">Cek
                    Plagiarisme</a>
                <a href="{{ route('pricing') }}"
                    class="inline-flex justify-center rounded-xl border border-white bg-white px-6 py-3 text-xs font-black text-blue-700 transition hover:bg-blue-50">Lihat
                    Paket Harga</a>
            </div>
        </div>
    </section>
</main>

{{-- FOOTER --}}
<footer id="footer" class="border-t border-slate-200 bg-white py-12">
    <div class="mx-auto max-w-[1180px] px-5 sm:px-6 lg:px-8">
        <div class="grid gap-10 sm:grid-cols-2 lg:grid-cols-[1.7fr_1fr_1fr_1.2fr]">
            <div>
                <div class="flex items-center gap-2.5">
                    <img src="{{ asset('images/naskahceklogo.png') }}" alt="NaskahCek"
                        class="h-9 w-9 rounded-xl object-cover">
                    <span class="text-lg font-black">NaskahCek</span>
                </div>
                <p class="mt-4 max-w-sm text-xs leading-6 text-slate-500">
                    Platform untuk membantu pemeriksaan, perbaikan, dan persiapan naskah akademik secara lebih
                    praktis.
                </p>
            </div>

            <div>
                <h3 class="text-xs font-black uppercase tracking-wider text-slate-900">Produk</h3>
                <ul class="mt-4 space-y-2.5 text-xs text-slate-500">
                    <li><a href="#fitur" class="hover:text-blue-600">Fitur</a></li>
                    <li><a href="#harga" class="hover:text-blue-600">Harga</a></li>
                    <li><a href="#sumber" class="hover:text-blue-600">Sumber</a></li>
                </ul>
            </div>

            <div>
                <h3 class="text-xs font-black uppercase tracking-wider text-slate-900">Bantuan</h3>
                <ul class="mt-4 space-y-2.5 text-xs text-slate-500">
                    <li><a href="#faq" class="hover:text-blue-600">FAQ</a></li>
                    <li><a href="#faq" class="hover:text-blue-600">Panduan Pengguna</a></li>
                    <li><a href="#faq" class="hover:text-blue-600">Ketentuan</a></li>
                </ul>
            </div>

            <div>
                <h3 class="text-xs font-black uppercase tracking-wider text-slate-900">Hubungi Kami</h3>
                <ul class="mt-4 space-y-2.5 text-xs text-slate-500">
                    <li>support@naskahcek.id</li>
                    <li>+62 812-3456-7890</li>
                </ul>
                <div class="mt-4 flex gap-2">
                    <span
                        class="flex h-8 w-8 items-center justify-center rounded-full border border-slate-200 text-xs">f</span>
                    <span
                        class="flex h-8 w-8 items-center justify-center rounded-full border border-slate-200 text-xs">◎</span>
                    <span
                        class="flex h-8 w-8 items-center justify-center rounded-full border border-slate-200 text-xs">in</span>
                </div>
            </div>
        </div>

        <div class="mt-10 border-t border-slate-100 pt-6 text-center text-[11px] text-slate-400">
            &copy; {{ date('Y') }} NaskahCek. Hak Cipta Dilindungi.
        </div>
    </div>
</footer>
</div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
@if(session('package_required_alert'))
Swal.fire({
    icon: 'warning',
    title: 'Pilih paket terlebih dahulu',
    html: `
                <div class="grid max-h-[65vh] gap-3 overflow-y-auto p-1 text-left sm:grid-cols-2">
                    <div class="rounded-xl border border-slate-200 p-4">
                        <h3 class="text-sm font-black text-slate-900">Hemat 3x Cek Plagiasi Turnitin</h3>
                        <p class="mt-1 text-xs font-semibold text-slate-500">(7 Hari)</p>
                        <p class="mt-3 text-xs leading-5 text-slate-600">Bug kamu yang lagi ngebut nyelesain tugas biar selesai tepat waktu</p>
                        <p class="mt-4 text-2xl font-black text-slate-900">Rp 20.000</p>
                        <p class="mt-1 text-xs font-semibold text-slate-500">Kuota</p>
                        <ul class="mt-3 space-y-1 text-xs leading-5 text-slate-600">
                            <li>✓ 3x cek plagiasi</li><li>✓ Skip menu pembayaran</li><li>✓ Bisa cek sampai 800 halaman/file</li><li>✓ Dapat token 3x cek plagiasi</li><li>✓ Hasil langsung bisa di download</li>
                        </ul>
                        <button type="button" data-package="hemat-3" class="mt-4 w-full rounded-lg bg-orange-500 px-3 py-2 text-xs font-bold text-white">Beli Paket</button>
                    </div>
                    <div class="rounded-xl border border-slate-200 p-4">
                        <h3 class="text-sm font-black text-slate-900">Praktis 10x Cek Plagiasi Turnitin</h3>
                        <p class="mt-1 text-xs font-semibold text-slate-500">(14 Hari)</p>
                        <p class="mt-3 text-xs leading-5 text-slate-600">Buat kamu deadliners yang lagi ngerajin revisian dan tugas</p>
                        <p class="mt-4 text-2xl font-black text-slate-900">Rp 80.000</p>
                        <p class="mt-1 text-xs font-semibold text-slate-500">Kuota</p>
                        <ul class="mt-3 space-y-1 text-xs leading-5 text-slate-600">
                            <li>✓ 10x cek plagiasi</li><li>✓ Skip menu pembayaran</li><li>✓ Bisa cek sampai 800 halaman/file</li><li>✓ Dapat token 10x cek plagiasi</li><li>✓ Hasil langsung bisa di download</li>
                        </ul>
                        <button type="button" data-package="praktis-10" class="mt-4 w-full rounded-lg bg-orange-500 px-3 py-2 text-xs font-bold text-white">Beli Paket</button>
                    </div>
                    <div class="rounded-xl border border-slate-200 p-4">
                        <h3 class="text-sm font-black text-slate-900">Pro 30x Cek Plagiasi Turnitin</h3>
                        <p class="mt-1 text-xs font-semibold text-slate-500">(3 Bulan)</p>
                        <p class="mt-3 text-xs leading-5 text-slate-600">Buat kamu mahasiswa akhir yang lagi ngerjain skripsi biar ga bolak balik cek plagiasi</p>
                        <p class="mt-4 text-2xl font-black text-slate-900">Rp 200.000</p>
                        <p class="mt-1 text-xs font-semibold text-slate-500">Kuota</p>
                        <ul class="mt-3 space-y-1 text-xs leading-5 text-slate-600">
                            <li>✓ 30x cek plagiasi</li><li>✓ Skip menu pembayaran</li><li>✓ Bisa cek sampai 800 halaman/file</li><li>✓ Dapat token 30x cek plagiasi</li><li>✓ Hasil langsung bisa di download</li>
                        </ul>
                        <button type="button" data-package="pro-30" class="mt-4 w-full rounded-lg bg-orange-500 px-3 py-2 text-xs font-bold text-white">Beli Paket</button>
                    </div>
                    <div class="rounded-xl border border-slate-200 p-4">
                        <h3 class="text-sm font-black text-slate-900">Ultimato 100x Cek Plagiasi Turnitin</h3>
                        <p class="mt-1 text-xs font-semibold text-slate-500">(6 Bulan)</p>
                        <p class="mt-3 text-xs leading-5 text-slate-600">Solusi buat kamu yang pengen cek buanyak dokumen</p>
                        <p class="mt-4 text-2xl font-black text-slate-900">Rp 800.000</p>
                        <p class="mt-1 text-xs font-semibold text-slate-500">Kuota</p>
                        <ul class="mt-3 space-y-1 text-xs leading-5 text-slate-600">
                            <li>✓ 100x cek plagiasi</li><li>✓ Skip menu pembayaran</li><li>✓ Bisa cek sampai 800 halaman/file</li><li>✓ Dapat token 100x cek plagiasi</li><li>✓ Hasil langsung bisa di download</li>
                        </ul>
                        <button type="button" data-package="ultimato-100" class="mt-4 w-full rounded-lg bg-orange-500 px-3 py-2 text-xs font-bold text-white">Beli Paket</button>
                    </div>
                </div>
            `,
    width: 920,
    showConfirmButton: false,
    showCloseButton: true,
    closeButtonAriaLabel: 'Tutup',
    didOpen: function() {
        document.querySelectorAll('[data-package]').forEach(function(button) {
            button.addEventListener('click', function() {
                window.location.href = @json(route('register')) + '?package=' +
                    encodeURIComponent(button.dataset.package);
            });
        });
    },
});
@endif

function showComingSoonAlert() {
    Swal.fire({
        icon: 'info',
        title: 'Fitur masih dalam pengembangan',
        text: 'Coba Perbaikan AI akan segera tersedia untuk Anda.',
        confirmButtonText: 'Mengerti',
        confirmButtonColor: '#2563eb',
    });
}

function showLoginRequiredAlert(event, packageKey) {
    event.preventDefault();

    if (@json(auth()->check())) {
        window.location.href = @json(url('/user/payment/package')) + '/' + encodeURIComponent(packageKey);
        return;
    }

    Swal.fire({
        icon: 'warning',
        title: 'Anda Harus daftar terlebih dahulu',
        confirmButtonText: 'Daftar',
        confirmButtonColor: '#f97316',
        showCancelButton: true,
        cancelButtonText: 'Batal',
        cancelButtonColor: '#64748b',
    }).then((result) => {
        if (result.isConfirmed) {
            window.location.href = @json(route('register')) + '?package=' + encodeURIComponent(packageKey);
        }
    });
}
</script>
@endpush

{{-- NASKAHCEK VISUAL OVERRIDES --}}
<style>
:root {
    scroll-behavior: smooth;
}

/* Overall typography / proportions */
.min-h-screen {
    letter-spacing: -0.01em;
}

nav {
    height: 68px;
}

nav>div {
    height: 68px !important;
}

/* Hero: match the clean, compact reference composition */
main>section:first-child {
    border-bottom: 0 !important;
}

main>section:first-child>div:last-child {
    padding-top: 64px !important;
    padding-bottom: 64px !important;
}

main>section:first-child h1 {
    font-size: clamp(38px, 4vw, 51px) !important;
    line-height: 1.05 !important;
    letter-spacing: -0.045em !important;
}

main>section:first-child p {
    max-width: 500px;
}

/* Feature section */
main .text-slate-300,
main .text-slate-400 {
    color: #475569 !important;
}

main .text-slate-500 {
    color: #334155 !important;
}

main .text-slate-600 {
    color: #1e293b !important;
}

main .text-blue-100 {
    color: #ffffff !important;
}

main .bg-blue-50 {
    background-color: #dbeafe !important;
}

main .bg-blue-100 {
    background-color: #bfdbfe !important;
}

main .bg-amber-50 {
    background-color: #fef3c7 !important;
}

main .bg-amber-100 {
    background-color: #fde68a !important;
}

main .bg-teal-50 {
    background-color: #ccfbf1 !important;
}

main .bg-slate-50 {
    background-color: #e2e8f0 !important;
}

#fitur {
    padding-top: 58px !important;
    padding-bottom: 58px !important;
}

#fitur .grid.lg\:grid-cols-5>div {
    min-height: 150px;
}

#fitur h2 {
    color: #10234b !important;
}

/* Management */
main>section:nth-of-type(3) {
    padding-top: 62px !important;
    padding-bottom: 62px !important;
}

/* Journey */
main>section:nth-of-type(4) {
    padding-top: 56px !important;
    padding-bottom: 56px !important;
}

/* AI preview */
main>section:nth-of-type(5) {
    padding-top: 64px !important;
    padding-bottom: 64px !important;
}

/* Source / journal */
#sumber {
    padding-top: 58px !important;
    padding-bottom: 58px !important;
}

#sumber .grid.sm\:grid-cols-5>div {
    height: 88px !important;
}

/* Pricing */
#harga {
    padding-top: 64px !important;
    padding-bottom: 64px !important;
}

/* FAQ: reference uses two columns */
#faq {
    padding-top: 58px !important;
    padding-bottom: 58px !important;
}

#faq>div>.mt-10 {
    display: grid !important;
    grid-template-columns: repeat(2, minmax(0, 1fr));
    gap: 8px !important;
}

#faq>div>.mt-10>div {
    align-self: start;
}

/* CTA */
main>section:last-of-type {
    padding-top: 18px !important;
    padding-bottom: 18px !important;
}

main>section:last-of-type>div {
    border-radius: 18px !important;
    padding-top: 48px !important;
    padding-bottom: 48px !important;
}

/* Footer */
footer {
    padding-top: 34px !important;
    padding-bottom: 24px !important;
}

/* Better mobile behaviour */
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

    main>section:first-child>div:last-child {
        padding-top: 96px !important;
        padding-bottom: 46px !important;
    }

    main>section:first-child h1 {
        font-size: 38px !important;
    }

    #faq>div>.mt-10 {
        grid-template-columns: 1fr;
    }

    main>section:last-of-type>div {
        border-radius: 0 !important;
    }
}
</style>

<script>
(function() {
    function initNaskahCekInteractions() {
        const menuToggle = document.getElementById('mobile-menu-toggle');
        const mobileMenu = document.getElementById('mobile-menu');
        const openIcon = document.getElementById('mobile-menu-open-icon');
        const closeIcon = document.getElementById('mobile-menu-close-icon');

        if (menuToggle && mobileMenu && openIcon && closeIcon) {
            menuToggle.addEventListener('click', function() {
                const isOpen = mobileMenu.classList.contains('max-h-0');
                mobileMenu.classList.toggle('max-h-0', !isOpen);
                mobileMenu.classList.toggle('max-h-[500px]', isOpen);
                mobileMenu.classList.toggle('pointer-events-none', !isOpen);
                mobileMenu.classList.toggle('opacity-0', !isOpen);
                mobileMenu.classList.toggle('opacity-100', isOpen);
                openIcon.classList.toggle('hidden', !isOpen);
                closeIcon.classList.toggle('hidden', isOpen);
                menuToggle.setAttribute('aria-expanded', String(!isOpen));
                menuToggle.setAttribute('aria-label', isOpen ? 'Buka menu navigasi' :
                    'Tutup menu navigasi');
            });

            mobileMenu.querySelectorAll('a').forEach(function(link) {
                link.addEventListener('click', function() {
                    mobileMenu.classList.add('max-h-0', 'pointer-events-none', 'opacity-0');
                    mobileMenu.classList.remove('max-h-[500px]', 'opacity-100');
                    openIcon.classList.remove('hidden');
                    closeIcon.classList.add('hidden');
                    menuToggle.setAttribute('aria-expanded', 'false');
                    menuToggle.setAttribute('aria-label', 'Buka menu navigasi');
                });
            });
        }

        document.querySelectorAll('a[href^="#"]').forEach(function(link) {
            link.addEventListener('click', function(event) {
                var id = link.getAttribute('href');
                if (!id || id === '#') return;
                var target = document.querySelector(id);
                if (!target) return;

                event.preventDefault();
                var nav = document.querySelector('nav');
                var offset = nav ? nav.offsetHeight + 8 : 8;
                var top = target.getBoundingClientRect().top + window.scrollY - offset;

                window.scrollTo({
                    top: top,
                    behavior: 'smooth'
                });
            });
        });
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', initNaskahCekInteractions);
    } else {
        initNaskahCekInteractions();
    }
})();
</script>