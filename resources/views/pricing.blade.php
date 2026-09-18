@extends('layouts.landing')

@section('title', 'Paket Harga — NaskahCek')

@section('content')
<div class="min-h-screen bg-[#f7faff] pt-[72px] text-slate-900">
    <nav class="landing-nav fixed left-0 right-0 top-0 z-50 w-full border-b border-slate-200 bg-white shadow-sm">
        <div class="mx-auto flex h-[72px] max-w-[1180px] items-center justify-between px-5 sm:px-6 lg:px-8">
            <a href="{{ route('welcome') }}" class="flex items-center gap-2.5">
                <img src="{{ asset('images/naskahceklogo.png') }}" alt="NaskahCek" class="h-9 w-9 rounded-xl object-cover">
                <span class="text-[17px] font-extrabold tracking-[-0.02em] text-slate-900">NaskahCek</span>
            </a>
            <div class="hidden items-center gap-1 md:flex">
                <a href="{{ route('free.check.index') }}" class="rounded-lg px-3.5 py-2 text-[13px] font-medium text-slate-600 transition hover:bg-slate-50 hover:text-blue-600">Cek Plagiasi Turnitin</a>
                <a href="{{ route('pricing') }}" class="rounded-lg bg-blue-50 px-3.5 py-2 text-[13px] font-medium text-blue-600 transition hover:bg-blue-100">Paket Harga</a>
                <a href="{{ route('templates.index') }}" class="rounded-lg px-3.5 py-2 text-[13px] font-medium text-slate-600 transition hover:bg-slate-50 hover:text-blue-600">Template Jurnal</a>
                <a href="{{ route('welcome') }}#faq" class="rounded-lg px-3.5 py-2 text-[13px] font-medium text-slate-600 transition hover:bg-slate-50 hover:text-blue-600">Bantuan</a>
            </div>
            <div class="hidden items-center gap-2 md:flex">
                <a href="{{ route('login') }}" class="inline-flex items-center rounded-xl bg-blue-600 px-5 py-2.5 text-[13px] font-bold text-white transition hover:bg-blue-700">Masuk</a>
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
            <div class="flex flex-col gap-1">
                <a href="{{ route('free.check.index') }}"
                    class="rounded-xl px-3 py-3 text-sm font-medium text-slate-700 transition hover:bg-blue-50 hover:text-blue-600">Cek
                    Plagiasi Turnitin</a>
                <a href="{{ route('pricing') }}"
                    class="rounded-xl bg-blue-50 px-3 py-3 text-sm font-medium text-blue-600 transition hover:bg-blue-100">Paket
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

    <main class="relative overflow-hidden py-16 sm:py-24">
        <div class="pointer-events-none absolute -left-40 top-0 h-96 w-96 rounded-full bg-blue-200/50 blur-3xl"></div>
        <div class="pointer-events-none absolute -right-40 top-48 h-96 w-96 rounded-full bg-teal-100/60 blur-3xl"></div>
        <div class="relative mx-auto max-w-[1400px] px-5 sm:px-6 lg:px-8">
            <div class="mx-auto max-w-2xl text-center">
                <p class="text-[10px] font-black uppercase tracking-[0.25em] text-blue-600">Paket NaskahCek</p>
                <h1 class="mt-3 text-4xl font-black tracking-[-0.04em] text-slate-950 sm:text-5xl">Pilih paket yang paling sesuai</h1>
                <p class="mt-4 text-sm leading-6 text-slate-600 sm:text-base">Mulai dari kebutuhan ringan sampai kebutuhan banyak dokumen, semua tersedia dengan cara yang sederhana.</p>
            </div>

            <div class="mx-auto mt-12 grid max-w-[1120px] gap-4 xl:grid-cols-4">
                <div class="flex h-full flex-col rounded-2xl border border-slate-200 bg-white p-4 shadow-sm transition hover:-translate-y-0.5 hover:shadow-lg sm:p-5">
                    <div class="flex items-center gap-2">
                        <span class="flex h-5 w-5 items-center justify-center rounded-full bg-emerald-500 text-[10px] font-bold text-white">✓</span>
                        <h2 class="text-[14px] font-black tracking-[-0.02em] text-slate-900">Hemat 3x Cek Plagiasi Turnitin</h2>
                    </div>
                    <p class="mt-4 text-[11px] font-semibold text-slate-500">(7 Hari)</p>
                    <p class="mt-2 text-[13px] leading-5 text-slate-600">Bug kamu yang lagi ngebut nyelesain tugas biar selesai tepat waktu</p>
                    <p class="mt-7 text-[2rem] font-black tracking-[-0.03em] text-slate-900">Rp 20.000</p>
                    <p class="mt-1 text-sm font-semibold text-slate-500">Kuota</p>
                    <div class="mt-4 flex items-center gap-2 text-[13px] font-medium text-slate-700">
                        <span class="flex h-4 w-4 items-center justify-center rounded-full border border-slate-700 bg-slate-800 text-[10px] font-bold text-white">✓</span>
                        3x cek plagiasi
                    </div>

                    <div class="mt-6 border-t border-slate-200 pt-4">
                        <p class="mb-3 text-sm font-bold text-slate-700">Benefit</p>
                        <ul class="space-y-2 text-[13px] text-slate-600">
                            <li class="flex items-start gap-2"><span class="mt-0.5 flex h-4 w-4 items-center justify-center rounded-full bg-emerald-500 text-[10px] font-bold text-white">✓</span> Skip menu pembayaran</li>
                            <li class="flex items-start gap-2"><span class="mt-0.5 flex h-4 w-4 items-center justify-center rounded-full bg-emerald-500 text-[10px] font-bold text-white">✓</span> Bisa cek sampai 800 halaman/file</li>
                            <li class="flex items-start gap-2"><span class="mt-0.5 flex h-4 w-4 items-center justify-center rounded-full bg-emerald-500 text-[10px] font-bold text-white">✓</span> Dapat token 3x cek plagiasi</li>
                            <li class="flex items-start gap-2"><span class="mt-0.5 flex h-4 w-4 items-center justify-center rounded-full bg-emerald-500 text-[10px] font-bold text-white">✓</span> Hasil langsung bisa di download</li>
                        </ul>
                    </div>

                    <a href="{{ route('register', ['package' => 'hemat-3']) }}" class="mt-auto flex min-h-10 w-full items-center justify-center rounded-xl bg-orange-500 px-4 py-2.5 text-[13px] font-bold text-white shadow-lg shadow-orange-200 transition hover:bg-orange-600">Beli Paket</a>
                </div>

                <div class="flex h-full flex-col rounded-2xl border border-slate-200 bg-white p-4 shadow-sm transition hover:-translate-y-0.5 hover:shadow-lg sm:p-5">
                    <h2 class="text-[14px] font-black tracking-[-0.02em] text-slate-900">Praktis 10x Cek Plagiasi Turnitin</h2>
                    <p class="mt-4 text-[11px] font-semibold text-slate-500">(14 Hari)</p>
                    <p class="mt-2 text-[13px] leading-5 text-slate-600">Buat kamu deadliners yang lagi ngerajin revisian dan tugas</p>
                    <p class="mt-7 text-[2rem] font-black tracking-[-0.03em] text-slate-900">Rp 80.000</p>
                    <p class="mt-1 text-sm font-semibold text-slate-500">Kuota</p>
                    <div class="mt-4 flex items-center gap-2 text-[13px] font-medium text-slate-700">
                        <span class="flex h-4 w-4 items-center justify-center rounded-full border border-slate-700 bg-slate-800 text-[10px] font-bold text-white">✓</span>
                        10x cek plagiasi
                    </div>

                    <div class="mt-6 border-t border-slate-200 pt-4">
                        <p class="mb-3 text-sm font-bold text-slate-700">Benefit</p>
                        <ul class="space-y-2 text-[13px] text-slate-600">
                            <li class="flex items-start gap-2"><span class="mt-0.5 flex h-4 w-4 items-center justify-center rounded-full bg-emerald-500 text-[10px] font-bold text-white">✓</span> Skip menu pembayaran</li>
                            <li class="flex items-start gap-2"><span class="mt-0.5 flex h-4 w-4 items-center justify-center rounded-full bg-emerald-500 text-[10px] font-bold text-white">✓</span> Bisa cek sampai 800 halaman/file</li>
                            <li class="flex items-start gap-2"><span class="mt-0.5 flex h-4 w-4 items-center justify-center rounded-full bg-emerald-500 text-[10px] font-bold text-white">✓</span> Dapat token 10x cek plagiasi</li>
                            <li class="flex items-start gap-2"><span class="mt-0.5 flex h-4 w-4 items-center justify-center rounded-full bg-emerald-500 text-[10px] font-bold text-white">✓</span> Hasil langsung bisa di download</li>
                        </ul>
                    </div>

                    <a href="{{ route('register', ['package' => 'praktis-10']) }}" class="mt-auto flex min-h-10 w-full items-center justify-center rounded-xl bg-orange-500 px-4 py-2.5 text-[13px] font-bold text-white shadow-lg shadow-orange-200 transition hover:bg-orange-600">Beli Paket</a>
                </div>

                <div class="flex h-full flex-col rounded-2xl border border-slate-200 bg-white p-4 shadow-sm transition hover:-translate-y-0.5 hover:shadow-lg sm:p-5">
                    <h2 class="text-[14px] font-black tracking-[-0.02em] text-slate-900">Pro 30x Cek Plagiasi Turnitin</h2>
                    <p class="mt-4 text-[11px] font-semibold text-slate-500">(3 Bulan)</p>
                    <p class="mt-2 text-[13px] leading-5 text-slate-600">Buat kamu mahasiswa akhir yang lagi ngerjain skripsi biar ga bolak balik cek plagiasi</p>
                    <p class="mt-7 text-[2rem] font-black tracking-[-0.03em] text-slate-900">Rp 200.000</p>
                    <p class="mt-1 text-sm font-semibold text-slate-500">Kuota</p>
                    <div class="mt-4 flex items-center gap-2 text-[13px] font-medium text-slate-700">
                        <span class="flex h-4 w-4 items-center justify-center rounded-full border border-slate-700 bg-slate-800 text-[10px] font-bold text-white">✓</span>
                        30x cek plagiasi
                    </div>

                    <div class="mt-6 border-t border-slate-200 pt-4">
                        <p class="mb-3 text-sm font-bold text-slate-700">Benefit</p>
                        <ul class="space-y-2 text-[13px] text-slate-600">
                            <li class="flex items-start gap-2"><span class="mt-0.5 flex h-4 w-4 items-center justify-center rounded-full bg-emerald-500 text-[10px] font-bold text-white">✓</span> Skip menu pembayaran</li>
                            <li class="flex items-start gap-2"><span class="mt-0.5 flex h-4 w-4 items-center justify-center rounded-full bg-emerald-500 text-[10px] font-bold text-white">✓</span> Bisa cek sampai 800 halaman/file</li>
                            <li class="flex items-start gap-2"><span class="mt-0.5 flex h-4 w-4 items-center justify-center rounded-full bg-emerald-500 text-[10px] font-bold text-white">✓</span> Dapat token 30x cek plagiasi</li>
                            <li class="flex items-start gap-2"><span class="mt-0.5 flex h-4 w-4 items-center justify-center rounded-full bg-emerald-500 text-[10px] font-bold text-white">✓</span> Hasil langsung bisa di download</li>
                        </ul>
                    </div>

                    <a href="{{ route('register', ['package' => 'pro-30']) }}" class="mt-auto flex min-h-10 w-full items-center justify-center rounded-xl bg-orange-500 px-4 py-2.5 text-[13px] font-bold text-white shadow-lg shadow-orange-200 transition hover:bg-orange-600">Beli Paket</a>
                </div>

                <div class="flex h-full flex-col rounded-2xl border border-slate-200 bg-white p-4 shadow-sm transition hover:-translate-y-0.5 hover:shadow-lg sm:p-5">
                    <h2 class="text-[14px] font-black tracking-[-0.02em] text-slate-900">Ultimato 100x Cek Plagiasi Turnitin</h2>
                    <p class="mt-4 text-[11px] font-semibold text-slate-500">(6 Bulan)</p>
                    <p class="mt-2 text-[13px] leading-5 text-slate-600">Solusi buat kamu yang pengen cek buanyak dokumen</p>
                    <p class="mt-7 text-[2rem] font-black tracking-[-0.03em] text-slate-900">Rp 800.000</p>
                    <p class="mt-1 text-sm font-semibold text-slate-500">Kuota</p>
                    <div class="mt-4 flex items-center gap-2 text-[13px] font-medium text-slate-700">
                        <span class="flex h-4 w-4 items-center justify-center rounded-full border border-slate-700 bg-slate-800 text-[10px] font-bold text-white">✓</span>
                        100x cek plagiasi
                    </div>

                    <div class="mt-6 border-t border-slate-200 pt-4">
                        <p class="mb-3 text-sm font-bold text-slate-700">Benefit</p>
                        <ul class="space-y-2 text-[13px] text-slate-600">
                            <li class="flex items-start gap-2"><span class="mt-0.5 flex h-4 w-4 items-center justify-center rounded-full bg-emerald-500 text-[10px] font-bold text-white">✓</span> Skip menu pembayaran</li>
                            <li class="flex items-start gap-2"><span class="mt-0.5 flex h-4 w-4 items-center justify-center rounded-full bg-emerald-500 text-[10px] font-bold text-white">✓</span> Bisa cek sampai 800 halaman/file</li>
                            <li class="flex items-start gap-2"><span class="mt-0.5 flex h-4 w-4 items-center justify-center rounded-full bg-emerald-500 text-[10px] font-bold text-white">✓</span> Dapat token 100x cek plagiasi</li>
                            <li class="flex items-start gap-2"><span class="mt-0.5 flex h-4 w-4 items-center justify-center rounded-full bg-emerald-500 text-[10px] font-bold text-white">✓</span> Hasil langsung bisa di download</li>
                        </ul>
                    </div>

                    <a href="{{ route('register', ['package' => 'ultimato-100']) }}" class="mt-auto flex min-h-10 w-full items-center justify-center rounded-xl bg-orange-500 px-4 py-2.5 text-[13px] font-bold text-white shadow-lg shadow-orange-200 transition hover:bg-orange-600">Beli Paket</a>
                </div>
            </div>
        </div>
    </main>

    <footer class="border-t border-slate-200 bg-white py-8">
        <div class="mx-auto flex max-w-[1120px] flex-col items-center justify-between gap-3 px-5 text-xs text-slate-400 sm:flex-row sm:px-6 lg:px-8">
            <p>&copy; {{ date('Y') }} NaskahCek. Hak cipta dilindungi.</p>
            <a href="{{ route('welcome') }}" class="font-semibold text-slate-500 hover:text-blue-600">Kembali ke beranda</a>
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
</script>
@endsection
