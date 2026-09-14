@extends('layouts.landing')

@section('title', 'Template Jurnal Akademik — NaskahCek')

@section('content')
<div class="min-h-screen bg-[#fcfdff] text-slate-900 selection:bg-blue-600 selection:text-white pt-[72px]"
    x-data="templateViewer()">

    {{-- NAVBAR (FIXED TOP) --}}
    <nav class="fixed left-0 right-0 top-0 z-50 w-full border-b border-slate-200 bg-white shadow-sm">
        <div class="mx-auto flex h-[72px] max-w-[1240px] items-center justify-between px-5 sm:px-6 lg:px-8">
            <a href="{{ route('welcome') }}" class="flex items-center gap-2.5">
                <img src="{{ asset('images/naskahceklogo.png') }}" alt="NaskahCek"
                    class="h-9 w-9 rounded-xl object-cover">
                <span class="text-[17px] font-extrabold tracking-[-0.02em] text-slate-950">NaskahCek</span>
            </a>

            <div class="hidden items-center gap-1 md:flex">
                <a href="{{ route('free.check.index') }}"
                    class="rounded-lg px-3.5 py-2 text-[13px] font-semibold text-slate-600 transition hover:bg-slate-50 hover:text-blue-600">Cek
                    Plagiasi Turnitin</a>
                <a href="{{ route('pricing') }}"
                    class="rounded-lg px-3.5 py-2 text-[13px] font-semibold text-slate-600 transition hover:bg-slate-50 hover:text-blue-600">Paket
                    Harga</a>
                <a href="{{ route('templates.index') }}"
                    class="rounded-lg bg-blue-50 px-3.5 py-2 text-[13px] font-semibold text-blue-600 transition hover:bg-blue-100">Template
                    Jurnal</a>
                <a href="{{ route('welcome') }}#faq"
                    class="rounded-lg px-3.5 py-2 text-[13px] font-semibold text-slate-600 transition hover:bg-slate-50 hover:text-blue-600">Bantuan</a>
            </div>

            <div class="hidden items-center gap-2 md:flex">
                <a href="{{ route('login') }}"
                    class="inline-flex items-center rounded-xl bg-blue-600 px-5 py-2.5 text-[13px] font-bold text-white transition hover:bg-blue-700">Masuk</a>
            </div>

            <button type="button" id="mobile-menu-toggle"
                class="inline-flex h-10 w-10 items-center justify-center rounded-xl border border-slate-200 text-slate-700 transition hover:bg-slate-50 md:hidden"
                aria-controls="mobile-menu" aria-expanded="false" aria-label="Buka menu navigasi">
                <svg id="mobile-menu-open-icon" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                </svg>
                <svg id="mobile-menu-close-icon" class="hidden h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 6l12 12M6 18L18 6" />
                </svg>
            </button>
        </div>

        <div id="mobile-menu"
            class="pointer-events-none absolute left-0 right-0 top-full max-h-0 overflow-hidden border-t border-slate-200 bg-white px-5 opacity-0 shadow-lg transition-all duration-300 ease-out md:hidden">
            <div class="flex flex-col gap-1 pt-3">
                <a href="{{ route('free.check.index') }}"
                    class="rounded-xl px-3 py-3 text-sm font-semibold text-slate-700 transition hover:bg-blue-50 hover:text-blue-600">Cek
                    Plagiasi Turnitin</a>
                <a href="{{ route('pricing') }}"
                    class="rounded-xl px-3 py-3 text-sm font-semibold text-slate-700 transition hover:bg-blue-50 hover:text-blue-600">Paket
                    Harga</a>
                <a href="{{ route('templates.index') }}"
                    class="rounded-xl bg-blue-50 px-3 py-3 text-sm font-semibold text-blue-600 transition hover:bg-blue-100">Template
                    Jurnal</a>
                <a href="{{ route('welcome') }}#faq"
                    class="rounded-xl px-3 py-3 text-sm font-semibold text-slate-700 transition hover:bg-blue-50 hover:text-blue-600">Bantuan</a>
                <a href="{{ route('login') }}"
                    class="mt-2 mb-3 inline-flex items-center justify-center rounded-xl bg-blue-600 px-5 py-3 text-sm font-bold text-white transition hover:bg-blue-700">Masuk</a>
            </div>
        </div>
    </nav>

    <main class="py-14 sm:py-20 relative overflow-hidden">
        {{-- Background Light Mesh Gradients --}}
        <div class="pointer-events-none absolute -left-40 top-0 h-[500px] w-[500px] rounded-full bg-blue-100/50 blur-3xl"></div>
        <div class="pointer-events-none absolute -right-40 top-1/4 h-[500px] w-[500px] rounded-full bg-sky-100/60 blur-3xl"></div>
        <div class="pointer-events-none absolute left-1/3 bottom-10 h-[450px] w-[450px] rounded-full bg-indigo-50/70 blur-3xl"></div>

        <div class="relative mx-auto max-w-[1240px] px-5 sm:px-6 lg:px-8">
            
            {{-- HERO SECTION (MATCHING LANDING PAGE STYLE) --}}
            <div class="grid grid-cols-1 items-center gap-12 lg:grid-cols-12 mb-20">
                <div class="lg:col-span-7">
                    <div class="flex items-center gap-3 text-[10px] font-extrabold uppercase tracking-[0.28em] text-blue-700 sm:text-[11px]">
                        <span class="h-px w-8 bg-blue-600 sm:w-10"></span>
                        <span>Coming Soon</span>
                        <span class="h-px w-5 bg-slate-300 sm:w-8"></span>
                    </div>

                    <h1 class="mt-4 text-[38px] font-black tracking-[-0.04em] text-slate-950 sm:text-[50px] lg:text-[54px] leading-[1.12]">
                        Koleksi Template, <br>
                        <span class="text-blue-600">Format Jurnal Akademik</span> <br>
                        Siap Pakai
                    </h1>

                    <p class="mt-5 max-w-xl text-[15px] sm:text-base leading-relaxed text-slate-600 font-normal">
                        Pratinjau langsung naskah format baku di browser atau unduh berkas aslinya secara gratis untuk mempercepat persiapan publikasi jurnal Anda.
                    </p>

                    <div class="mt-7 space-y-3 text-[14px] font-medium text-slate-700">
                        <div class="flex items-center gap-3">
                            <span class="flex h-5 w-5 items-center justify-center rounded-full bg-blue-600 text-[11px] font-black text-white">✓</span>
                            Format standar Scopus, SINTA 1–6, DOAJ, dan Garuda
                        </div>
                        <div class="flex items-center gap-3">
                            <span class="flex h-5 w-5 items-center justify-center rounded-full bg-blue-600 text-[11px] font-black text-white">✓</span>
                            Pratinjau interaktif DOCX & PDF langsung di browser
                        </div>
                        <div class="flex items-center gap-3">
                            <span class="flex h-5 w-5 items-center justify-center rounded-full bg-blue-600 text-[11px] font-black text-white">✓</span>
                            100% Gratis diunduh untuk kebutuhan riset & publikasi
                        </div>
                    </div>

                    <div class="mt-8 flex flex-wrap items-center gap-2.5">
                        <a href="#scopus" class="rounded-xl border border-slate-200 bg-white px-4 py-2 text-xs font-bold text-slate-700 shadow-sm transition hover:border-blue-300 hover:bg-blue-50/60 hover:text-blue-600">Scopus</a>
                        <a href="#sinta" class="rounded-xl border border-slate-200 bg-white px-4 py-2 text-xs font-bold text-slate-700 shadow-sm transition hover:border-blue-300 hover:bg-blue-50/60 hover:text-blue-600">SINTA 1–6</a>
                        <a href="#doaj" class="rounded-xl border border-slate-200 bg-white px-4 py-2 text-xs font-bold text-slate-700 shadow-sm transition hover:border-blue-300 hover:bg-blue-50/60 hover:text-blue-600">DOAJ</a>
                        <a href="#garuda" class="rounded-xl border border-slate-200 bg-white px-4 py-2 text-xs font-bold text-slate-700 shadow-sm transition hover:border-blue-300 hover:bg-blue-50/60 hover:text-blue-600">Garuda</a>
                    </div>
                </div>

                {{-- HERO DASHBOARD / SIMULASI PEMBUATAN JURNAL --}}
                <div class="relative lg:col-span-5 lg:pl-4">
                    <div class="absolute -inset-8 -z-10 rounded-full bg-blue-100/50 blur-3xl"></div>
                    <div class="flex flex-col justify-between overflow-hidden rounded-[22px] border border-slate-200 bg-white shadow-[0_28px_70px_rgba(15,23,42,0.13)]">
                        {{-- Card Header --}}
                        <div class="flex items-center justify-between bg-blue-600 px-6 py-5 text-white">
                            <div>
                                <p class="text-[11px] font-bold uppercase tracking-[0.18em] text-blue-100">Generator Jurnal</p>
                                <p class="mt-1 text-base font-bold">Simulasi Konversi Naskah</p>
                            </div>
                            <span class="inline-flex items-center gap-1.5 rounded-full bg-white/15 px-3 py-1.5 text-[11px] font-semibold text-white">
                                <span class="h-2 w-2 rounded-full bg-emerald-400 animate-pulse"></span>
                                Live Format
                            </span>
                        </div>

                        {{-- Card Content --}}
                        <div class="flex flex-1 flex-col justify-between p-6 sm:p-7 space-y-4">
                            {{-- Paper Preview Visual --}}
                            <div class="rounded-2xl border border-slate-200/90 bg-slate-50/70 p-5 shadow-xs">
                                <div class="flex items-center justify-between border-b border-slate-200/80 pb-3 mb-3">
                                    <div class="flex items-center gap-2">
                                        <span class="px-2 py-0.5 rounded text-[10px] font-extrabold bg-blue-600 text-white uppercase tracking-wider">SINTA 2</span>
                                        <span class="text-xs font-bold text-slate-700">IEEE Two-Column Format</span>
                                    </div>
                                    <span class="text-[11px] font-semibold text-emerald-600 bg-emerald-50 px-2 py-0.5 rounded border border-emerald-200">Siap Publikasi</span>
                                </div>

                                {{-- Mini Paper Layout Simulation --}}
                                <div class="bg-white rounded-xl border border-slate-200 p-4 font-serif text-justify shadow-2xs">
                                    <div class="text-center mb-3">
                                        <p class="text-xs font-bold text-slate-900 leading-tight">Analisis Optimasi Algoritma Deteksi Plagiarisme Berbasis Deep Learning</p>
                                        <p class="text-[10px] text-slate-500 mt-0.5 italic">Ahmad Fauzi¹, Siti Nurhaliza² · Jurusan Ilmu Komputer</p>
                                    </div>

                                    <div class="border-t border-b border-slate-100 py-1.5 mb-2.5 bg-slate-50/60 px-2 rounded">
                                        <p class="text-[9px] text-slate-600 leading-relaxed"><strong class="font-sans font-bold text-[9px] uppercase tracking-wider">Abstrak — </strong>Penelitian ini menyajikan metode pemrosesan bahasa alami untuk analisis kemiripan teks akademik secara komparatif dengan akurasi 98.4%...</p>
                                    </div>

                                    {{-- Two columns simulation --}}
                                    <div class="grid grid-cols-2 gap-2.5 text-[8.5px] leading-relaxed text-slate-500 font-sans">
                                        <div class="space-y-1">
                                            <p class="font-bold text-slate-800 text-[9px] uppercase">I. Pendahuluan</p>
                                            <div class="space-y-1 text-[8px] leading-snug">
                                                <div class="h-2 bg-slate-200 rounded w-full"></div>
                                                <div class="h-2 bg-slate-200 rounded w-5/6"></div>
                                                <div class="h-2 bg-slate-200 rounded w-full"></div>
                                            </div>
                                        </div>
                                        <div class="space-y-1">
                                            <p class="font-bold text-slate-800 text-[9px] uppercase">II. Metodologi</p>
                                            <div class="space-y-1 text-[8px] leading-snug">
                                                <div class="h-2 bg-slate-200 rounded w-full"></div>
                                                <div class="h-2 bg-blue-100 rounded w-4/5"></div>
                                                <div class="h-2 bg-slate-200 rounded w-full"></div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            {{-- Steps / Process Simulation --}}
                            <div class="grid grid-cols-3 gap-2 text-center">
                                <div class="rounded-xl border border-slate-200 bg-white p-2.5">
                                    <span class="text-blue-600 font-black text-xs block">1. Input</span>
                                    <span class="text-[10px] text-slate-500 font-medium">Data Naskah</span>
                                </div>
                                <div class="rounded-xl border border-blue-200 bg-blue-50/50 p-2.5">
                                    <span class="text-indigo-600 font-black text-xs block">2. Template</span>
                                    <span class="text-[10px] text-indigo-600 font-bold">Auto Format</span>
                                </div>
                                <div class="rounded-xl border border-slate-200 bg-white p-2.5">
                                    <span class="text-emerald-600 font-black text-xs block">3. Export</span>
                                    <span class="text-[10px] text-slate-500 font-medium">PDF & DOCX</span>
                                </div>
                            </div>

                            {{-- Bottom Checklist Item --}}
                            <div class="flex items-center justify-between border-t border-slate-100 pt-3 text-xs">
                                <div class="flex items-center gap-2">
                                    <span class="flex h-5 w-5 items-center justify-center rounded-full bg-emerald-100 text-emerald-700 text-[10px] font-black">✓</span>
                                    <span class="font-bold text-slate-700">Tersedia Format Baku Sesuai OJS</span>
                                </div>
                                <span class="font-bold text-blue-600 hover:underline cursor-pointer text-[11px]">Siap Ekspor →</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            @php
            $categories = [
                [
                    'id' => 'scopus',
                    'name' => 'Scopus & Internasional',
                    'badge' => 'Internasional Bereputasi',
                    'badgeClass' => 'bg-blue-50 text-blue-700 border-blue-200',
                    'logo' => 'images/scopus.png',
                    'desc' => 'Format standar dan panduan struktur penulisan artikel untuk jurnal terindeks Scopus & reputasi global.',
                    'files' => [
                        [
                            'name' => 'Template Jurnal Scopus Standard Format',
                            'format' => 'DOCX',
                            'url' => asset('templatejurnal/scopus/template jurnal scopus.docx'),
                            'size' => 'Word Document (.docx)'
                        ]
                    ]
                ],
                [
                    'id' => 'sinta',
                    'name' => 'SINTA (Science and Technology Index)',
                    'badge' => 'Akreditasi Nasional',
                    'badgeClass' => 'bg-emerald-50 text-emerald-700 border-emerald-200',
                    'logo' => 'images/sintaa.jpg',
                    'desc' => 'Kumpulan format resmi jurnal terakreditasi nasional Kemdikbudristek dari peringkat SINTA 1 hingga SINTA 6.',
                    'files' => [
                        [
                            'name' => 'Template Jurnal SINTA 1 (Tingkat Tertinggi)',
                            'format' => 'DOCX',
                            'url' => asset('templatejurnal/sinta/Template Sinta 1.docx'),
                            'size' => 'Word Document (.docx)'
                        ],
                        [
                            'name' => 'Template Jurnal SINTA 3',
                            'format' => 'DOCX',
                            'url' => asset('templatejurnal/sinta/Template Sinta 3.docx'),
                            'size' => 'Word Document (.docx)'
                        ],
                        [
                            'name' => 'Template Jurnal SINTA 4',
                            'format' => 'DOCX',
                            'url' => asset('templatejurnal/sinta/Template Sinta 4.docx'),
                            'size' => 'Word Document (.docx)'
                        ],
                        [
                            'name' => 'Template Jurnal SINTA 5',
                            'format' => 'DOCX',
                            'url' => asset('templatejurnal/sinta/Template Sinta 5.docx'),
                            'size' => 'Word Document (.docx)'
                        ],
                        [
                            'name' => 'Template Jurnal SINTA 6 (Format PDF)',
                            'format' => 'PDF',
                            'url' => asset('templatejurnal/sinta/Template Sinta 6.pdf'),
                            'size' => 'PDF Document (.pdf)'
                        ],
                    ]
                ],
                [
                    'id' => 'doaj',
                    'name' => 'DOAJ (Directory of Open Access Journals)',
                    'badge' => 'Open Access Global',
                    'badgeClass' => 'bg-amber-50 text-amber-700 border-amber-200',
                    'logo' => 'images/doajj.png',
                    'desc' => 'Panduan struktur manuskrip akses terbuka berstandar internasional tanpa hambatan sitasi.',
                    'files' => [
                        [
                            'name' => 'Template Jurnal DOAJ Standard Article',
                            'format' => 'DOCX',
                            'url' => asset('templatejurnal/doaj/template jurnal doaj.docx'),
                            'size' => 'Word Document (.docx)'
                        ]
                    ]
                ],
                [
                    'id' => 'garuda',
                    'name' => 'Garuda (Garba Rujukan Digital)',
                    'badge' => 'Portal Rujukan Nasional',
                    'badgeClass' => 'bg-purple-50 text-purple-700 border-purple-200',
                    'logo' => 'images/garudaa.jpg',
                    'desc' => 'Format naskah baku untuk publikasi karya ilmiah yang terhubung dengan basis data Garuda Nasional.',
                    'files' => [
                        [
                            'name' => 'Template Jurnal Garuda Kemdikbud',
                            'format' => 'DOCX',
                            'url' => asset('templatejurnal/garuda/Templat Jurnal garuda.docx'),
                            'size' => 'Word Document (.docx)'
                        ]
                    ]
                ],
            ];
            @endphp

            <div class="space-y-12">
                @foreach ($categories as $cat)
                <section id="{{ $cat['id'] }}" class="rounded-3xl border border-slate-200/90 bg-white p-6 sm:p-8 shadow-sm transition hover:shadow-md hover:border-slate-300">
                    
                    {{-- Category Header --}}
                    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-5 pb-6 border-b border-slate-100">
                        <div class="flex items-center gap-4">
                            <div class="flex h-14 w-24 shrink-0 items-center justify-center rounded-2xl border border-slate-200 bg-white p-2.5 shadow-sm">
                                <img src="{{ asset($cat['logo']) }}" alt="{{ $cat['name'] }}" class="max-h-10 max-w-full object-contain">
                            </div>
                            <div>
                                <div class="flex flex-wrap items-center gap-2.5">
                                    <h2 class="text-xl font-bold text-slate-900">{{ $cat['name'] }}</h2>
                                    <span class="rounded-full px-2.5 py-0.5 text-[10px] font-bold border {{ $cat['badgeClass'] }}">
                                        {{ $cat['badge'] }}
                                    </span>
                                </div>
                                <p class="text-xs text-slate-500 mt-1 max-w-xl leading-relaxed">{{ $cat['desc'] }}</p>
                            </div>
                        </div>
                        <span class="inline-flex items-center text-xs font-semibold text-slate-500 bg-slate-50 px-3.5 py-1.5 rounded-xl border border-slate-200/80 self-start sm:self-auto">
                            {{ count($cat['files']) }} Berkas Tersedia
                        </span>
                    </div>

                    {{-- Card Grid --}}
                    <div class="mt-6 grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
                        @foreach ($cat['files'] as $file)
                        <div class="group flex flex-col justify-between rounded-2xl border border-slate-200/90 bg-[#fbfcfe] p-5 transition duration-200 hover:border-blue-300 hover:bg-white hover:shadow-md">
                            <div>
                                <div class="flex items-start justify-between gap-3">
                                    <div class="flex h-11 w-11 shrink-0 items-center justify-center rounded-xl {{ $file['format'] === 'PDF' ? 'bg-rose-50 text-rose-600 border border-rose-200' : 'bg-blue-50 text-blue-600 border border-blue-200' }} font-black text-xs">
                                        {{ $file['format'] }}
                                    </div>
                                    <span class="text-[10px] text-slate-500 font-semibold bg-white px-2.5 py-1 rounded-lg border border-slate-200 shadow-2xs">Siap Pakai</span>
                                </div>

                                <h3 class="mt-4 text-[14px] font-bold text-slate-900 group-hover:text-blue-600 transition-colors leading-snug">
                                    {{ $file['name'] }}
                                </h3>
                                <p class="mt-1.5 text-[11.5px] text-slate-500 font-medium">{{ $file['size'] }}</p>
                            </div>

                            <div class="mt-6 pt-3 border-t border-slate-200/60 flex items-center gap-2.5 justify-end">
                                {{-- Preview Button --}}
                                <button type="button"
                                    @click="openPreview('{{ $file['name'] }}', '{{ $file['format'] }}', '{{ $file['url'] }}')"
                                    class="inline-flex items-center gap-1.5 rounded-xl border border-slate-200 bg-white px-3.5 py-2 text-xs font-bold text-slate-700 shadow-sm transition hover:bg-blue-50 hover:text-blue-600 hover:border-blue-200">
                                    <svg class="h-3.5 w-3.5 text-blue-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                    </svg>
                                    Pratinjau
                                </button>

                                {{-- Download Button --}}
                                <a href="{{ $file['url'] }}" download
                                    class="inline-flex items-center gap-1.5 rounded-xl bg-blue-600 px-3.5 py-2 text-xs font-bold text-white shadow-sm shadow-blue-600/20 transition hover:bg-blue-700 hover:-translate-y-0.5">
                                    <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" />
                                    </svg>
                                    Unduh
                                </a>
                            </div>
                        </div>
                        @endforeach
                    </div>
                </section>
                @endforeach
            </div>

            {{-- Bottom CTA Box --}}
            <div class="mt-16 rounded-3xl bg-gradient-to-br from-blue-600 via-blue-600 to-indigo-700 p-8 sm:p-12 text-center text-white shadow-xl shadow-blue-600/15 relative overflow-hidden">
                <div class="absolute -right-10 -bottom-10 h-64 w-64 rounded-full bg-white/10 blur-2xl pointer-events-none"></div>
                
                <h3 class="text-2xl font-black sm:text-4xl">Sudah Menyesuaikan Naskah dengan Template?</h3>
                <p class="mt-3.5 max-w-2xl mx-auto text-sm sm:text-base text-blue-100 leading-relaxed font-normal">
                    Lakukan pemeriksaan similarity sebelum naskah dikirim ke editor jurnal untuk mencegah penolakan (desk reject) akibat kemiripan kalimat.
                </p>
                <div class="mt-8 flex flex-col sm:flex-row items-center justify-center gap-3.5">
                    <a href="{{ auth()->check() ? route('user.plagiarism.index') : route('register') }}"
                        class="inline-flex items-center justify-center rounded-xl bg-white px-7 py-3.5 text-xs font-black text-blue-700 shadow-lg transition hover:bg-blue-50 hover:-translate-y-0.5">
                        Mulai Cek Similarity Naskah
                    </a>
                    <a href="{{ route('welcome') }}"
                        class="inline-flex items-center justify-center rounded-xl border border-white/40 bg-white/10 px-6 py-3.5 text-xs font-bold text-white transition hover:bg-white/20">
                        Kembali ke Beranda
                    </a>
                </div>
            </div>

        </div>
    </main>

    {{-- MODAL PREVIEW DOKUMEN --}}
    <div x-show="isOpen" 
        x-cloak
        class="fixed inset-0 z-50 overflow-y-auto bg-slate-950/70 backdrop-blur-sm flex items-center justify-center p-3 sm:p-6"
        x-transition:enter="transition ease-out duration-200"
        x-transition:enter-start="opacity-0"
        x-transition:enter-end="opacity-100"
        x-transition:leave="transition ease-in duration-150"
        x-transition:leave-start="opacity-100"
        x-transition:leave-end="opacity-0">
        
        <div class="relative w-full max-w-5xl bg-white rounded-3xl shadow-2xl flex flex-col h-[90vh] overflow-hidden border border-slate-200"
            @click.outside="closePreview()">
            
            {{-- Modal Header --}}
            <div class="flex items-center justify-between px-6 py-4 border-b border-slate-200 bg-white">
                <div class="flex items-center gap-3">
                    <span class="flex h-9 w-9 items-center justify-center rounded-xl font-black text-xs border"
                        :class="currentFormat === 'PDF' ? 'bg-rose-50 text-rose-600 border-rose-200' : 'bg-blue-50 text-blue-600 border-blue-200'"
                        x-text="currentFormat">
                    </span>
                    <div>
                        <h3 class="text-sm sm:text-base font-bold text-slate-900" x-text="currentName"></h3>
                        <p class="text-[11px] text-slate-500">Pratinjau Format Dokumen Template</p>
                    </div>
                </div>

                <div class="flex items-center gap-2.5">
                    <a :href="currentUrl" download
                        class="inline-flex items-center gap-1.5 rounded-xl bg-blue-600 px-4 py-2 text-xs font-bold text-white shadow-sm transition hover:bg-blue-700">
                        <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" />
                        </svg>
                        Unduh
                    </a>
                    <button type="button" @click="closePreview()"
                        class="flex h-9 w-9 items-center justify-center rounded-xl border border-slate-200 bg-slate-50 text-slate-500 hover:bg-slate-100 hover:text-slate-700 transition">
                        ✕
                    </button>
                </div>
            </div>

            {{-- Modal Content Area --}}
            <div class="flex-1 overflow-y-auto bg-slate-100/70 p-4 sm:p-8 flex justify-center relative">
                
                {{-- Loading Spinner --}}
                <div x-show="isLoading" class="absolute inset-0 flex flex-col items-center justify-center bg-slate-100/90 z-20">
                    <div class="h-10 w-10 animate-spin rounded-full border-4 border-blue-600 border-t-transparent"></div>
                    <p class="mt-3 text-xs font-bold text-slate-600">Memuat isi dokumen template...</p>
                </div>

                {{-- PDF Viewer --}}
                <template x-if="currentFormat === 'PDF'">
                    <iframe :src="currentUrl + '#toolbar=1'" class="w-full h-full rounded-2xl border border-slate-200 bg-white shadow-sm"></iframe>
                </template>

                {{-- DOCX Container --}}
                <div x-show="currentFormat === 'DOCX'" class="w-full max-w-4xl">
                    <div id="docx-container" class="bg-white text-slate-900 p-8 sm:p-14 rounded-2xl border border-slate-200 shadow-md min-h-full"></div>
                </div>
            </div>
        </div>
    </div>

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
                        Platform untuk membantu pemeriksaan similarity, perbaikan AI, dan persiapan naskah akademik secara praktis dan terpercaya.
                    </p>
                </div>

                <div>
                    <h3 class="text-xs font-black uppercase tracking-wider text-slate-900">Produk</h3>
                    <ul class="mt-4 space-y-2.5 text-xs text-slate-500">
                        <li><a href="{{ route('welcome') }}#fitur" class="hover:text-blue-600">Fitur</a></li>
                        <li><a href="{{ route('welcome') }}#harga" class="hover:text-blue-600">Harga</a></li>
                        <li><a href="{{ route('templates.index') }}" class="hover:text-blue-600">Template Jurnal</a></li>
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

            <div class="mt-12 flex flex-col items-center justify-between border-t border-slate-100 pt-6 sm:flex-row text-xs text-slate-400">
                <p>&copy; {{ date('Y') }} NaskahCek. Hak cipta dilindungi.</p>
            </div>
        </div>
    </footer>
</div>

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

function templateViewer() {
    return {
        isOpen: false,
        isLoading: false,
        currentName: '',
        currentFormat: '',
        currentUrl: '',
        
        async openPreview(name, format, url) {
            this.currentName = name;
            this.currentFormat = format;
            this.currentUrl = url;
            this.isOpen = true;
            this.isLoading = true;

            if (format === 'DOCX') {
                this.$nextTick(async () => {
                    const container = document.getElementById('docx-container');
                    if (container) {
                        container.innerHTML = '';
                    }

                    try {
                        const response = await fetch(url);
                        const blob = await response.blob();
                        
                        if (window.docx && window.docx.renderAsync) {
                            await window.docx.renderAsync(blob, container);
                        } else {
                            container.innerHTML = '<p class="text-center text-sm text-slate-500 py-10">Gagal memuat parser dokumen.</p>';
                        }
                    } catch (error) {
                        console.error("Error rendering docx:", error);
                        if (container) {
                            container.innerHTML = '<p class="text-center text-sm text-red-500 py-10">Gagal memuat pratinjau dokumen.</p>';
                        }
                    } finally {
                        this.isLoading = false;
                    }
                });
            } else {
                // PDF
                setTimeout(() => {
                    this.isLoading = false;
                }, 400);
            }
        },

        closePreview() {
            this.isOpen = false;
            const container = document.getElementById('docx-container');
            if (container) {
                container.innerHTML = '';
            }
        }
    }
}
</script>
@endsection
