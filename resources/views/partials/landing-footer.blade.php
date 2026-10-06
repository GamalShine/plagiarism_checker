<footer id="footer" class="border-t border-slate-200 bg-white" style="padding: 34px 0 16px;">
    <div class="mx-auto max-w-[1180px] px-5 sm:px-6 lg:px-8">
        <div class="grid gap-10 sm:grid-cols-2 lg:grid-cols-[1.7fr_1fr_1fr_1.2fr]">
            <div>
                <div class="flex items-center gap-2.5">
                    <img src="{{ asset('images/naskahceklogo.png') }}" alt="NaskahCek"
                        class="h-9 w-9 rounded-xl object-cover">
                    <span class="text-lg font-black">NaskahCek</span>
                </div>
                <p class="mt-4 max-w-sm text-xs leading-6 text-slate-500">
                    Platform untuk membantu pemeriksaan, perbaikan, dan persiapan naskah akademik secara lebih praktis.
                </p>
            </div>

            <div>
                <h3 class="text-xs font-black uppercase tracking-wider text-slate-900">Produk</h3>
                <ul class="mt-4 space-y-2.5 text-xs text-slate-500">
                    <li><a href="{{ route('welcome') }}#fitur" class="hover:text-blue-600">Fitur</a></li>
                    <li><a href="{{ route('welcome') }}#harga" class="hover:text-blue-600">Harga</a></li>
                    <li><a href="{{ route('welcome') }}#sumber" class="hover:text-blue-600">Sumber</a></li>
                </ul>
            </div>

            <div>
                <h3 class="text-xs font-black uppercase tracking-wider text-slate-900">Bantuan</h3>
                <ul class="mt-4 space-y-2.5 text-xs text-slate-500">
                    <li><a href="{{ route('welcome') }}#faq" class="hover:text-blue-600">FAQ</a></li>
                    <li><a href="{{ route('welcome') }}#faq" class="hover:text-blue-600">Panduan Pengguna</a></li>
                    <li><a href="{{ route('welcome') }}#faq" class="hover:text-blue-600">Ketentuan</a></li>
                </ul>
            </div>

            <div>
                <h3 class="text-xs font-black uppercase tracking-wider text-slate-900">Hubungi Kami</h3>
                <ul class="mt-4 space-y-2.5 text-xs text-slate-500">
                    <li><a href="mailto:mynaskah94@gmail.com"
                            class="transition hover:text-blue-600">mynaskah94@gmail.com</a></li>
                    <li><a href="https://wa.me/6281295436152" target="_blank" rel="noopener noreferrer"
                            class="transition hover:text-blue-600">+62 812-9543-6152</a></li>
                </ul>
                <div class="mt-4 flex gap-2">
                    <a href="https://id.shp.ee/5BWYh2xP" target="_blank" rel="noopener noreferrer"
                        aria-label="Shopee NaskahCek"
                        class="flex h-8 w-8 items-center justify-center rounded-full border border-slate-200 transition hover:border-orange-300">
                        <svg class="h-4 w-4" viewBox="0 0 24 24" aria-hidden="true">
                            <path d="M6 8h12l1 13H5L6 8Z" fill="#ee4d2d" />
                            <path d="M9 8V6a3 3 0 0 1 6 0v2" fill="none" stroke="#ee4d2d" stroke-width="1.7" />
                            <path d="M14.5 11.2c-.45-.35-1-.54-1.65-.54-.9 0-1.45.42-1.45 1.02 0 1.55 3.65.76 3.65 3.14 0 1.1-.98 1.84-2.42 1.84-.94 0-1.8-.3-2.5-.85" fill="none" stroke="#fff" stroke-linecap="round" stroke-width="1.4" />
                        </svg>
                    </a>
                    <a href="https://www.instagram.com/naskahcek" target="_blank" rel="noopener noreferrer"
                        aria-label="Instagram NaskahCek"
                        class="flex h-8 w-8 items-center justify-center rounded-full border border-slate-200 text-pink-600 transition hover:border-pink-300">
                        <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true">
                            <rect x="3.5" y="3.5" width="17" height="17" rx="5" />
                            <circle cx="12" cy="12" r="4" />
                            <circle cx="17.7" cy="6.5" r=".9" fill="currentColor" stroke="none" />
                        </svg>
                    </a>
                    <span role="img" aria-label="TikTok"
                        class="flex h-8 w-8 items-center justify-center rounded-full border border-slate-200 text-slate-900">
                        <svg class="h-4 w-4" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true">
                            <path d="M19.6 8.2a7.1 7.1 0 0 1-4.2-1.4v8.1a5.6 5.6 0 1 1-4.8-5.5v3.1a2.5 2.5 0 1 0 1.7 2.4V2.5h3.1c.2 2 1.4 3.6 3.3 4.3.3.1.6.2.9.2v1.2Z" />
                        </svg>
                    </span>
                </div>
            </div>
        </div>

        <div class="border-t border-slate-100 text-center text-[11px] text-slate-400"
            style="margin-top: 16px; padding-top: 8px;">
            &copy; {{ date('Y') }} NaskahCek. Hak Cipta Dilindungi.
        </div>
    </div>
</footer>
