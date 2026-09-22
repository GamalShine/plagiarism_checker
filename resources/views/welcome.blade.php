@extends('layouts.user')

@section('title', 'Beranda')

@section('content')
<div class="relative overflow-hidden">
    {{-- Hero --}}
    <section class="w-full pt-12 sm:pt-16 pb-16 sm:pb-20 text-center">
        <div class="inline-flex items-center gap-2 px-4 py-2 rounded-full text-sm font-semibold mb-8 border" style="background: var(--pc-primary-soft); color: var(--pc-primary); border-color: rgb(79 70 229 / 0.2);">
            <span class="relative flex h-2 w-2">
                <span class="animate-ping absolute inline-flex h-full w-full rounded-full opacity-75" style="background: var(--pc-primary);"></span>
                <span class="relative inline-flex rounded-full h-2 w-2" style="background: var(--pc-primary);"></span>
            </span>
            Platform Plagiarisme Akademik
        </div>

        <h1 class="text-4xl sm:text-5xl lg:text-6xl font-extrabold tracking-tight leading-tight mb-6">
            Cek Plagiasi<br>
            <span class="pc-gradient-text">Akurat & Terpercaya</span>
        </h1>

        <p class="text-lg sm:text-xl max-w-2xl mx-auto leading-relaxed mb-10" style="color: var(--pc-text-muted);">
            Deteksi kemiripan dari Google Scholar, Elsevier, Crossref, OpenAlex, dan web. Dilengkapi parafrase cerdas dan generator jurnal akademik.
        </p>

        <div class="flex flex-col sm:flex-row gap-3 justify-center">
            @auth
                @if(auth()->user()->isAdmin())
                <a href="{{ route('admin.dashboard') }}" class="pc-btn-primary pc-btn-lg">Masuk Dashboard</a>
                @else
                <a href="{{ route('user.dashboard') }}" class="pc-btn-primary pc-btn-lg">Masuk Dashboard</a>
                @endif
                <a href="{{ route('user.plagiarism.index') }}" class="pc-btn-secondary pc-btn-lg">Cek Plagiasi</a>
            @else
                <a href="{{ route('register') }}" class="pc-btn-primary pc-btn-lg">Mulai Gratis</a>
                <a href="{{ route('login') }}" class="pc-btn-secondary pc-btn-lg">Login</a>
            @endauth
        </div>
    </section>

    {{-- Features --}}
    <section class="w-full pb-16 sm:pb-20">
        <div class="grid grid-cols-1 md:grid-cols-3 gap-5">
            <div class="pc-card p-8">
                <div class="w-12 h-12 rounded-2xl flex items-center justify-center mb-5" style="background: var(--pc-primary-soft); color: var(--pc-primary);">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                </div>
                <h3 class="text-lg font-bold mb-2">Multi-Source Checker</h3>
                <p class="text-sm leading-relaxed" style="color: var(--pc-text-muted);">
                    Pengecekan ke Web, Google Scholar, Elsevier, OpenAlex, dan Crossref dengan persentase kemiripan detail per sumber.
                </p>
            </div>

            <div class="pc-card p-8">
                <div class="w-12 h-12 rounded-2xl flex items-center justify-center mb-5" style="background: var(--pc-accent-soft); color: var(--pc-accent);">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                </div>
                <h3 class="text-lg font-bold mb-2">Smart Parafrase</h3>
                <p class="text-sm leading-relaxed" style="color: var(--pc-text-muted);">
                    Analisis kalimat plagiat dan saran parafrase otomatis untuk menurunkan tingkat similarity dokumen Anda.
                </p>
            </div>

            <div class="pc-card p-8">
                <div class="w-12 h-12 rounded-2xl flex items-center justify-center mb-5 bg-amber-100 text-amber-600 dark:bg-amber-900/30 dark:text-amber-400">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/></svg>
                </div>
                <h3 class="text-lg font-bold mb-2">Journal Generator</h3>
                <p class="text-sm leading-relaxed" style="color: var(--pc-text-muted);">
                    Buat jurnal akademik format standar atau modern. Export langsung ke PDF dan DOCX.
                </p>
            </div>
        </div>
    </section>

    {{-- Stats bar --}}
    <section class="border-t" style="border-color: var(--pc-border); background: var(--pc-surface);">
        <div class="w-full py-10 sm:py-12 grid grid-cols-2 md:grid-cols-4 gap-6 sm:gap-8 text-center">
            <div>
                <p class="text-3xl font-extrabold pc-gradient-text">7+</p>
                <p class="text-sm mt-1" style="color: var(--pc-text-muted);">Sumber Database</p>
            </div>
            <div>
                <p class="text-3xl font-extrabold pc-gradient-text">PDF</p>
                <p class="text-sm mt-1" style="color: var(--pc-text-muted);">DOCX & TXT</p>
            </div>
            <div>
                <p class="text-3xl font-extrabold pc-gradient-text">Real-time</p>
                <p class="text-sm mt-1" style="color: var(--pc-text-muted);">Analisis Dokumen</p>
            </div>
            <div>
                <p class="text-3xl font-extrabold pc-gradient-text">100%</p>
                <p class="text-sm mt-1" style="color: var(--pc-text-muted);">Gratis Digunakan</p>
            </div>
        </div>
    </section>
</div>
@endsection
