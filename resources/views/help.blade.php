@extends('layouts.landing')

@section('title', 'Bantuan — NaskahCek')

@section('content')
<div class="min-h-screen bg-[#f7faff] text-slate-900" style="padding-top: 52px;">
    <nav class="landing-nav fixed left-0 right-0 z-50 w-full border-b border-slate-200 bg-white shadow-sm">
        <div class="mx-auto flex h-[72px] max-w-[1180px] items-center justify-between px-5 sm:px-6 lg:px-8">
            <a href="{{ route('welcome') }}" class="flex items-center gap-2.5">
                <img src="{{ asset('images/naskahceklogo.png') }}" alt="NaskahCek" class="h-9 w-9 rounded-xl object-cover">
                <span class="text-[17px] font-extrabold tracking-[-0.02em] text-slate-900">NaskahCek</span>
            </a>

            <div class="hidden items-center gap-1 md:flex">
                <a href="{{ route('free.check.index') }}"
                    class="rounded-lg px-3.5 py-2 text-[13px] font-medium text-slate-600 transition hover:bg-slate-50 hover:text-blue-600">Cek
                    Plagiasi Turnitin</a>
                <a href="{{ route('pricing') }}"
                    class="rounded-lg px-3.5 py-2 text-[13px] font-medium text-slate-600 transition hover:bg-slate-50 hover:text-blue-600">Paket
                    Harga</a>
                <a href="{{ route('templates.index') }}"
                    class="rounded-lg px-3.5 py-2 text-[13px] font-medium text-slate-600 transition hover:bg-slate-50 hover:text-blue-600">Template
                    Jurnal</a>
                <a href="{{ route('help') }}"
                    class="rounded-lg bg-blue-50 px-3.5 py-2 text-[13px] font-medium text-blue-600 transition hover:bg-blue-100">Bantuan</a>
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
            <div class="flex flex-col gap-1 py-2">
                <a href="{{ route('free.check.index') }}"
                    class="rounded-xl px-3 py-3 text-sm font-medium text-slate-700 transition hover:bg-blue-50 hover:text-blue-600">Cek
                    Plagiasi Turnitin</a>
                <a href="{{ route('pricing') }}"
                    class="rounded-xl px-3 py-3 text-sm font-medium text-slate-700 transition hover:bg-blue-50 hover:text-blue-600">Paket
                    Harga</a>
                <a href="{{ route('templates.index') }}"
                    class="rounded-xl px-3 py-3 text-sm font-medium text-slate-700 transition hover:bg-blue-50 hover:text-blue-600">Template
                    Jurnal</a>
                <a href="{{ route('help') }}"
                    class="rounded-xl bg-blue-50 px-3 py-3 text-sm font-medium text-blue-600 transition hover:bg-blue-100">Bantuan</a>
                <a href="{{ route('login') }}"
                    class="mb-3 mt-2 inline-flex items-center justify-center rounded-xl bg-blue-600 px-5 py-3 text-sm font-bold text-white transition hover:bg-blue-700">Masuk</a>
            </div>
        </div>
    </nav>

    <main class="mx-auto flex max-w-[1180px] items-center justify-center px-5 py-16 sm:px-6 lg:px-8" style="min-height: calc(100vh - 52px);">
        <section class="w-full max-w-2xl rounded-3xl border border-slate-200 bg-white shadow-[0_18px_50px_rgba(15,23,42,0.08)]"
            style="padding: clamp(20px, 4vw, 40px);">
            <div class="text-center">
                <p class="text-[10px] font-black uppercase tracking-[0.25em] text-blue-600">Pusat Bantuan</p>
                <h1 class="mt-3 text-3xl font-black tracking-tight text-slate-950">Ada yang bisa kami bantu?</h1>
                <p class="mt-3 text-sm leading-6 text-slate-600">Laporkan kendala teknis, bug, error, masalah akun atau pembayaran, maupun sampaikan pertanyaan dan saran. Tim kami siap menindaklanjuti.</p>
            </div>

            <form id="help-contact-form" action="{{ route('help.reports.store') }}" method="POST" class="mt-8 space-y-5">
                @csrf
                <div class="grid gap-5 sm:grid-cols-2">
                    <div>
                        <label for="contact-name" class="mb-2 block text-left text-sm font-semibold text-slate-700">Nama</label>
                        <input id="contact-name" name="name" type="text" autocomplete="name" required
                            class="w-full rounded-xl border border-slate-300 px-4 py-3 text-sm text-slate-900 outline-none transition placeholder:text-slate-400 focus:border-blue-500 focus:ring-4 focus:ring-blue-100"
                            placeholder="Nama Anda">
                    </div>
                    <div>
                        <label for="contact-email" class="mb-2 block text-left text-sm font-semibold text-slate-700">Email</label>
                        <input id="contact-email" name="email" type="email" autocomplete="email" required
                            class="w-full rounded-xl border border-slate-300 px-4 py-3 text-sm text-slate-900 outline-none transition placeholder:text-slate-400 focus:border-blue-500 focus:ring-4 focus:ring-blue-100"
                            placeholder="nama@email.com">
                    </div>
                </div>
                <div>
                    <label for="contact-category" class="mb-2 block text-left text-sm font-semibold text-slate-700">Jenis laporan</label>
                    <select id="contact-category" name="category" required
                        class="w-full rounded-xl border border-slate-300 bg-white px-4 py-3 text-sm text-slate-900 outline-none transition focus:border-blue-500 focus:ring-4 focus:ring-blue-100">
                        <option value="" disabled selected>Pilih jenis laporan</option>
                        <option value="Kendala teknis">Kendala teknis</option>
                        <option value="Bug atau error">Bug atau error</option>
                        <option value="Masalah akun atau pembayaran">Masalah akun atau pembayaran</option>
                        <option value="Pertanyaan">Pertanyaan</option>
                        <option value="Saran lainnya">Saran lainnya</option>
                    </select>
                </div>
                <div>
                    <label for="contact-message" class="mb-2 block text-left text-sm font-semibold text-slate-700">Ceritakan detailnya</label>
                    <textarea id="contact-message" name="message" rows="5" required
                        class="w-full resize-y rounded-xl border border-slate-300 px-4 py-3 text-sm text-slate-900 outline-none transition placeholder:text-slate-400 focus:border-blue-500 focus:ring-4 focus:ring-blue-100"
                        placeholder="Jelaskan apa yang terjadi. Jika melaporkan bug/error, sertakan langkah untuk mengulanginya dan pesan error yang muncul..."></textarea>
                </div>

                <div class="grid gap-3">
                    <button type="submit" data-channel="admin"
                        class="inline-flex min-h-12 items-center justify-center gap-2 rounded-xl border border-slate-300 bg-white px-5 py-3 text-sm font-bold text-slate-700 transition hover:bg-slate-50 focus:outline-none focus:ring-4 focus:ring-slate-200">
                        <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 5h16v14H4zM4 7l8 6 8-6" />
                        </svg>
                        Kirim
                    </button>
                    <div class="grid gap-3 sm:grid-cols-2">
                        <button type="submit" data-channel="email"
                            class="inline-flex min-h-12 items-center justify-center gap-2 rounded-xl bg-blue-600 px-5 py-3 text-sm font-bold text-white transition hover:bg-blue-700 focus:outline-none focus:ring-4 focus:ring-blue-200">
                            <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l9 6 9-6M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                            </svg>
                            Kirim lewat Email
                        </button>
                        <button type="submit" data-channel="whatsapp"
                            class="inline-flex min-h-12 items-center justify-center gap-2 rounded-xl bg-emerald-600 px-5 py-3 text-sm font-bold text-white transition hover:bg-emerald-700 focus:outline-none focus:ring-4 focus:ring-emerald-200">
                            <svg class="h-5 w-5" fill="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                <path d="M20.52 3.48A11.86 11.86 0 0012.08 0C5.5 0 .15 5.35.15 11.93c0 2.1.55 4.15 1.6 5.96L0 24l6.28-1.65a11.9 11.9 0 005.8 1.48h.01c6.58 0 11.93-5.35 11.93-11.93 0-3.19-1.24-6.19-3.5-8.42zM12.09 21.8h-.01a9.9 9.9 0 01-5.04-1.38l-.36-.21-3.73.98.99-3.64-.24-.37a9.87 9.87 0 01-1.52-5.25c0-5.47 4.45-9.92 9.92-9.92a9.86 9.86 0 017.02 2.91 9.86 9.86 0 012.9 7.02c0 5.47-4.45 9.92-9.93 9.92zm5.45-7.43c-.3-.15-1.76-.87-2.03-.97-.27-.1-.47-.15-.67.15-.2.3-.77.97-.94 1.17-.17.2-.35.22-.65.07-.3-.15-1.26-.46-2.4-1.47-.89-.79-1.49-1.76-1.66-2.06-.17-.3-.02-.46.13-.61.13-.13.3-.35.45-.52.15-.17.2-.3.3-.5.1-.2.05-.37-.02-.52-.07-.15-.67-1.62-.92-2.22-.24-.58-.49-.5-.67-.51h-.57c-.2 0-.52.07-.79.37-.27.3-1.04 1.02-1.04 2.49 0 1.47 1.07 2.89 1.22 3.09.15.2 2.1 3.2 5.08 4.49.71.31 1.27.5 1.7.64.71.23 1.36.2 1.87.12.57-.08 1.76-.72 2.01-1.42.25-.7.25-1.3.17-1.42-.07-.12-.27-.2-.57-.35z" />
                            </svg>
                            Kirim lewat WhatsApp
                        </button>
                    </div>
                </div>
                <p id="contact-form-status" class="text-center text-xs text-slate-500" role="status" aria-live="polite">
                    Tombol Kirim menyimpan laporan ke admin. Tombol Email dan WhatsApp juga membuka aplikasi masing-masing.
                </p>
            </form>
        </section>
    </main>

    @include('partials.landing-footer')
</div>

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
    document.getElementById('help-contact-form')?.addEventListener('submit', async function (event) {
        event.preventDefault();

        const form = event.currentTarget;
        const submitter = event.submitter;
        const channel = submitter?.dataset.channel;
        const status = document.getElementById('contact-form-status');
        const buttons = [...form.querySelectorAll('button[type="submit"]')];

        if (!channel) return;

        const data = new FormData(form);
        const name = String(data.get('name') || '').trim();
        const email = String(data.get('email') || '').trim();
        const category = String(data.get('category') || '').trim();
        const message = String(data.get('message') || '').trim();
        data.set('channel', channel);
        const subject = `[${category}] Laporan dari ${name}`;
        const body = `Jenis laporan: ${category}\nNama: ${name}\nEmail: ${email}\n\nDetail:\n${message}`;
        const externalUrl = channel === 'email'
            ? `https://mail.google.com/mail/?view=cm&fs=1&to=${encodeURIComponent('mynaskah94@gmail.com')}&su=${encodeURIComponent(subject)}&body=${encodeURIComponent(body)}`
            : channel === 'whatsapp'
                ? `https://wa.me/6281295436152?text=${encodeURIComponent(body)}`
                : null;
        const externalWindow = externalUrl
            ? window.open(externalUrl, '_blank')
            : null;
        if (externalWindow) externalWindow.opener = null;
        buttons.forEach((button) => button.disabled = true);
        if (status) status.textContent = 'Menyimpan laporan...';

        try {
            const response = await fetch(form.action, {
                method: 'POST',
                headers: {
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest',
                    'X-CSRF-TOKEN': String(data.get('_token') || ''),
                },
                body: data,
            });
            const result = await response.json();

            if (!response.ok) {
                const firstError = result.errors ? Object.values(result.errors).flat()[0] : null;
                throw new Error(firstError || result.message || 'Laporan gagal disimpan. Coba lagi.');
            }

            if (channel === 'admin') {
                if (status) status.textContent = 'Laporan berhasil dikirim dan tersimpan di menu Laporan admin.';
                form.reset();
                buttons.forEach((button) => button.disabled = false);
                if (window.Swal) {
                    Swal.fire({
                        icon: 'success',
                        title: 'Laporan berhasil dikirim!',
                        text: 'Laporan Anda sudah tersimpan dan dapat dilihat oleh admin.',
                        confirmButtonText: 'Oke',
                        confirmButtonColor: '#2563eb',
                    });
                }
            } else if (channel === 'email') {
                buttons.forEach((button) => button.disabled = false);
                if (externalWindow) {
                    if (status) status.textContent = 'Laporan tersimpan di panel admin. Gmail dibuka dengan pesan Anda.';
                    form.reset();
                } else if (status) {
                    status.textContent = 'Laporan tersimpan di panel admin, tetapi browser memblokir tab Gmail. Izinkan pop-up lalu coba lagi.';
                }
                if (window.Swal) {
                    Swal.fire({
                        icon: externalWindow ? 'success' : 'warning',
                        title: externalWindow ? 'Email siap dikirim!' : 'Laporan tersimpan',
                        text: externalWindow
                            ? 'Gmail sudah dibuka dengan isi pesan Anda. Tekan Kirim di Gmail untuk mengirim email.'
                            : 'Browser memblokir tab Gmail. Izinkan pop-up untuk situs ini, lalu coba kembali.',
                        confirmButtonText: 'Oke',
                        confirmButtonColor: '#2563eb',
                    });
                }
            } else {
                buttons.forEach((button) => button.disabled = false);
                if (externalWindow) {
                    if (status) status.textContent = 'Laporan tersimpan di panel admin. WhatsApp dibuka dengan isi pesan Anda.';
                    form.reset();
                } else if (status) {
                    status.textContent = 'Laporan tersimpan di panel admin, tetapi browser memblokir tab WhatsApp. Izinkan pop-up lalu coba lagi.';
                }
                if (window.Swal) {
                    Swal.fire({
                        icon: externalWindow ? 'success' : 'warning',
                        title: externalWindow ? 'WhatsApp siap dikirim!' : 'Laporan tersimpan',
                        text: externalWindow
                            ? 'WhatsApp sudah dibuka dengan isi pesan Anda. Tekan Kirim di WhatsApp untuk mengirim pesan.'
                            : 'Browser memblokir tab WhatsApp. Izinkan pop-up untuk situs ini, lalu coba kembali.',
                        confirmButtonText: 'Oke',
                        confirmButtonColor: '#16a34a',
                    });
                }
            }
        } catch (error) {
            if (status) status.textContent = error.message || 'Laporan gagal disimpan. Periksa koneksi lalu coba lagi.';
            buttons.forEach((button) => button.disabled = false);
        }
    });
</script>
@endpush
@endsection
