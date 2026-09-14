<x-guest-layout>
    <div class="mb-8">
        <h2 class="text-2xl font-bold tracking-tight">Selamat datang kembali</h2>
        <p class="text-sm mt-1" style="color: var(--pc-text-muted);">Masuk ke akun NaskahCek Pro Anda</p>
    </div>

    <x-auth-session-status class="mb-4" :status="session('status')" />

    <form method="POST" action="{{ route('login') }}" class="space-y-5">
        @csrf

        <div>
            <x-input-label for="email" :value="__('Email')" />
            <x-text-input id="email" class="mt-1.5" type="email" name="email" :value="old('email')" required autofocus
                autocomplete="username" />
            <x-input-error :messages="$errors->get('email')" class="mt-2" />
        </div>

        <div x-data="{ showPassword: false }">
            <x-input-label for="password" :value="__('Password')" />
            <div class="relative mt-1.5">
                <input id="password" x-bind:type="showPassword ? 'text' : 'password'" name="password"
                    class="pc-input pr-11" required autocomplete="current-password">
                <button type="button" @click="showPassword = !showPassword"
                    class="absolute inset-y-0 right-0 flex items-center pr-3 text-slate-500 hover:text-slate-700 focus:outline-none"
                    aria-label="Tampilkan atau sembunyikan password">
                    <svg x-show="!showPassword" class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8"
                            d="M2.036 12.322a1.012 1.012 0 010-.644C3.423 7.18 7.18 4 12 4s8.577 3.18 9.964 7.678a1.012 1.012 0 010 .644C20.577 16.82 16.82 20 12 20s-8.577-3.18-9.964-7.678z" />
                        <circle cx="12" cy="12" r="3" stroke-linecap="round" stroke-linejoin="round"
                            stroke-width="1.8" />
                    </svg>
                    <svg x-show="showPassword" class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"
                        style="display:none;">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8"
                            d="M3 3l18 18M10.5 10.5A3 3 0 0013.5 13.5M9.88 5.08A10.94 10.94 0 0112 5c4.82 0 8.58 3.18 9.96 7.68a1.01 1.01 0 010 .64A17.07 17.07 0 0118.78 14M6.61 6.61A17.3 17.3 0 002.04 12.32a1.01 1.01 0 000 .64A17.66 17.66 0 006.61 17.39" />
                    </svg>
                </button>
            </div>
            <x-input-error :messages="$errors->get('password')" class="mt-2" />
        </div>

        <div class="flex items-center justify-between">
            <label for="remember_me" class="inline-flex items-center gap-2 cursor-pointer">
                <input id="remember_me" type="checkbox"
                    class="rounded border-slate-300 text-blue-600 focus:ring-blue-500" name="remember">
                <span class="text-sm" style="color: var(--pc-text-muted);">{{ __('Remember me') }}</span>
            </label>
            @if (Route::has('password.request'))
            <a class="text-sm pc-link" href="{{ route('password.request') }}">{{ __('Forgot password?') }}</a>
            @endif
        </div>

        <x-primary-button>{{ __('Log in') }}</x-primary-button>

        <p class="text-center text-sm" style="color: var(--pc-text-muted);">
            Belum punya akun?
            <a href="{{ route('register') }}" onclick="showPackageSelection(event)" class="pc-link">Daftar gratis</a>
        </p>
    </form>
</x-guest-layout>

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
    function showPackageSelection(event) {
        event.preventDefault();

        Swal.fire({
            title: 'Paket & Harga',
            html: `
                <div class="grid max-h-[65vh] gap-3 overflow-y-auto p-1 text-left sm:grid-cols-2 xl:grid-cols-4">
                    <div class="flex h-full flex-col rounded-xl border border-slate-200 p-4">
                        <h3 class="text-sm font-black text-slate-900">Hemat 3x Cek Plagiasi Turnitin</h3>
                        <p class="mt-1 text-xs font-semibold text-slate-500">(7 Hari)</p>
                        <p class="mt-3 text-xs leading-5 text-slate-600">Bug kamu yang lagi ngebut nyelesain tugas biar selesai tepat waktu</p>
                        <p class="mt-4 text-2xl font-black text-slate-900">Rp 20.000</p>
                        <p class="mt-1 text-xs font-semibold text-slate-500">Kuota 3x cek plagiasi</p>
                        <p class="mt-2 text-xs leading-5 text-slate-600">✓ Skip menu pembayaran<br>✓ Bisa cek sampai 800 halaman/file<br>✓ Hasil langsung bisa di download</p>
                        <button type="button" data-package="hemat-3" class="mt-auto w-full rounded-lg bg-orange-500 px-3 py-2 text-xs font-bold text-white">Beli Paket</button>
                    </div>
                    <div class="flex h-full flex-col rounded-xl border border-slate-200 p-4">
                        <h3 class="text-sm font-black text-slate-900">Praktis 10x Cek Plagiasi Turnitin</h3>
                        <p class="mt-1 text-xs font-semibold text-slate-500">(14 Hari)</p>
                        <p class="mt-3 text-xs leading-5 text-slate-600">Buat kamu deadliners yang lagi ngerajin revisian dan tugas</p>
                        <p class="mt-4 text-2xl font-black text-slate-900">Rp 80.000</p>
                        <p class="mt-1 text-xs font-semibold text-slate-500">Kuota 10x cek plagiasi</p>
                        <p class="mt-2 text-xs leading-5 text-slate-600">✓ Skip menu pembayaran<br>✓ Bisa cek sampai 800 halaman/file<br>✓ Hasil langsung bisa di download</p>
                        <button type="button" data-package="praktis-10" class="mt-auto w-full rounded-lg bg-orange-500 px-3 py-2 text-xs font-bold text-white">Beli Paket</button>
                    </div>
                    <div class="flex h-full flex-col rounded-xl border border-slate-200 p-4">
                        <h3 class="text-sm font-black text-slate-900">Pro 30x Cek Plagiasi Turnitin</h3>
                        <p class="mt-1 text-xs font-semibold text-slate-500">(3 Bulan)</p>
                        <p class="mt-3 text-xs leading-5 text-slate-600">Buat kamu mahasiswa akhir yang lagi ngerjain skripsi biar ga bolak balik cek plagiasi</p>
                        <p class="mt-4 text-2xl font-black text-slate-900">Rp 200.000</p>
                        <p class="mt-1 text-xs font-semibold text-slate-500">Kuota 30x cek plagiasi</p>
                        <p class="mt-2 text-xs leading-5 text-slate-600">✓ Skip menu pembayaran<br>✓ Bisa cek sampai 800 halaman/file<br>✓ Hasil langsung bisa di download</p>
                        <button type="button" data-package="pro-30" class="mt-auto w-full rounded-lg bg-orange-500 px-3 py-2 text-xs font-bold text-white">Beli Paket</button>
                    </div>
                    <div class="flex h-full flex-col rounded-xl border border-slate-200 p-4">
                        <h3 class="text-sm font-black text-slate-900">Ultimato 100x Cek Plagiasi Turnitin</h3>
                        <p class="mt-1 text-xs font-semibold text-slate-500">(6 Bulan)</p>
                        <p class="mt-3 text-xs leading-5 text-slate-600">Solusi buat kamu yang pengen cek buanyak dokumen</p>
                        <p class="mt-4 text-2xl font-black text-slate-900">Rp 800.000</p>
                        <p class="mt-1 text-xs font-semibold text-slate-500">Kuota 100x cek plagiasi</p>
                        <p class="mt-2 text-xs leading-5 text-slate-600">✓ Skip menu pembayaran<br>✓ Bisa cek sampai 800 halaman/file<br>✓ Hasil langsung bisa di download</p>
                        <button type="button" data-package="ultimato-100" class="mt-auto w-full rounded-lg bg-orange-500 px-3 py-2 text-xs font-bold text-white">Beli Paket</button>
                    </div>
                </div>
            `,
            width: 1200,
            showConfirmButton: false,
            showCloseButton: true,
            closeButtonAriaLabel: 'Tutup',
            didOpen: function () {
                document.querySelectorAll('[data-package]').forEach(function (button) {
                    button.addEventListener('click', function () {
                        window.location.href = @json(route('register')) + '?package=' + encodeURIComponent(button.dataset.package);
                    });
                });
            },
        });
    }
</script>
