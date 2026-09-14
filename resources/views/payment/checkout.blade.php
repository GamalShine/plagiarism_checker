@extends($layout ?? 'layouts.user')

@section('title', 'Pembayaran Cek Plagiarisme')
@section('page-title', 'Pembayaran')
@section('page-subtitle', 'Selesaikan pembayaran untuk meCek Plagiarisme')

@section('content')
<div class="max-w-5xl mx-auto" data-payment-page data-order-id="{{ $payment->order_id }}"
    data-snap-token="{{ $payment->snap_token }}" data-payment-link-url="{{ config('midtrans.payment_link_url') }}"
    data-finish-route="{{ route('user.payment.finish', $payment->order_id) }}"
    data-pending-route="{{ route('user.payment.pending', $payment->order_id) }}"
    data-error-route="{{ route('user.payment.error', $payment->order_id) }}">
    @if($errors->any())
    <div class="rounded-2xl border border-red-200 bg-red-50 p-4 mb-5 flex items-start gap-3">
        <svg class="w-5 h-5 text-red-500 shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
        </svg>
        <p class="text-sm text-red-700">{{ $errors->first() }}</p>
    </div>
    @endif

    <div class="grid grid-cols-1 lg:grid-cols-[1.15fr_0.85fr] gap-6">
        <div class="pc-card p-6 md:p-8">
            <div class="flex items-center justify-between mb-6">
                <div>
                    <p class="text-xs uppercase tracking-[0.2em] font-semibold" style="color: var(--pc-text-subtle);">
                        Pembayaran</p>
                    <h2 class="mt-2 text-2xl font-black">Bayar Sekarang</h2>
                </div>
                <div class="rounded-full border px-3 py-1.5 text-xs font-semibold"
                    style="border-color: var(--pc-border); color: var(--pc-primary); background: rgba(99,102,241,0.08);">
                    Aman & cepat
                </div>
            </div>

            <div class="rounded-3xl border p-5 mb-6"
                style="background: linear-gradient(135deg, rgba(99,102,241,0.08), rgba(139,92,246,0.07)); border-color: rgba(99,102,241,0.18);">
                <div class="flex items-center justify-between gap-4 flex-wrap">
                    <div>
                        <p class="text-xs uppercase tracking-[0.18em]" style="color: var(--pc-text-subtle);">Total
                            tagihan</p>
                        <p class="mt-2 text-4xl font-black" style="color: var(--pc-primary);">
                            Rp {{ number_format($payment->amount, 0, ',', '.') }}
                        </p>
                    </div>
                    <div class="rounded-2xl bg-white/70 dark:bg-slate-900/40 px-3 py-2 text-right border"
                        style="border-color: rgba(99,102,241,0.16);">
                        <p class="text-[10px] uppercase tracking-[0.18em]" style="color: var(--pc-text-subtle);">Order
                            ID</p>
                        <p class="mt-1 font-mono text-sm font-semibold">{{ $payment->order_id }}</p>
                    </div>
                </div>
            </div>

            <div class="space-y-4">
                <div class="flex items-start gap-3 rounded-2xl border p-4"
                    style="border-color: var(--pc-border); background: var(--pc-bg-subtle);">
                    <div class="w-10 h-10 rounded-xl flex items-center justify-center"
                        style="background: rgba(16,185,129,0.1); color: #10b981;">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                    </div>
                    <div>
                        <p class="font-semibold">Pembayaran aman dan praktis</p>
                        <p class="text-sm" style="color: var(--pc-text-muted);">Silakan lanjutkan ke proses pembayaran
                            untuk memilih metode yang tersedia.</p>
                    </div>
                </div>

                <div class="flex flex-wrap gap-2">
                    @foreach(['GoPay','OVO','Dana','ShopeePay','Bank Transfer','QRIS'] as $method)
                    <span class="px-3 py-1.5 rounded-full border text-xs font-semibold"
                        style="border-color: var(--pc-border); background: var(--pc-bg-subtle); color: var(--pc-text-muted);">
                        {{ $method }}
                    </span>
                    @endforeach
                </div>
            </div>

            <button type="button" data-pay-button
                class="mt-8 pc-btn-primary pc-btn-lg w-full flex items-center justify-center gap-2">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M3 10h18M7 15h10M9 5h6a2 2 0 012 2v2H7V7a2 2 0 012-2z" />
                </svg>
                <span>Bayar Sekarang</span>
            </button>
        </div>

        <aside class="pc-card p-6 md:p-8">
            <p class="text-xs uppercase tracking-[0.2em] font-semibold" style="color: var(--pc-text-subtle);">Rincian
            </p>
            <h3 class="mt-2 text-xl font-black">Produk yang dibeli</h3>

            <div class="mt-6 space-y-4 text-sm">
                <div class="flex items-center justify-between gap-3">
                    <span style="color: var(--pc-text-muted);">Layanan</span>
                    <span class="font-semibold">Cek Plagiarisme</span>
                </div>
                <div class="flex items-center justify-between gap-3">
                    <span style="color: var(--pc-text-muted);">Dokumen</span>
                    <span
                        class="font-medium text-right max-w-[180px]">{{ Str::limit($payment->original_filename, 24) }}</span>
                </div>
                <div class="flex items-center justify-between gap-3">
                    <span style="color: var(--pc-text-muted);">Status</span>
                    <span class="font-medium text-amber-500">Pending</span>
                </div>
            </div>

            <div class="mt-6 border-t pt-5" style="border-color: var(--pc-border);">
                <div class="flex items-center justify-between">
                    <span class="text-base font-semibold">Total</span>
                    <span class="text-2xl font-black" style="color: var(--pc-primary);">
                        Rp {{ number_format($payment->amount, 0, ',', '.') }}
                    </span>
                </div>
            </div>

            <div class="mt-8 rounded-2xl border p-4"
                style="border-color: var(--pc-border); background: var(--pc-bg-subtle);">
                <p class="text-xs uppercase tracking-[0.2em] font-semibold" style="color: var(--pc-text-subtle);">
                    Catatan</p>
                <ul class="mt-3 space-y-2 text-sm" style="color: var(--pc-text-muted);">
                    <li>• Pembayaran akan diproses secara aman</li>
                    <li>• Setelah berhasil, sistem akan otomatis lanjut ke hasil cek</li>
                    <li>• Jika dibatalkan, kamu bisa mencoba lagi</li>
                </ul>
            </div>

            <a href="{{ route('user.plagiarism.index') }}" class="mt-6 block text-center text-xs pc-link">
                ← Kembali ke halaman cek plagiarisme
            </a>
        </aside>
    </div>
</div>
@endsection

@push('scripts')
<script src="https://app.sandbox.midtrans.com/snap/snap.js" data-client-key="{{ config('midtrans.client_key') }}">
</script>
<script src="{{ Vite::asset('resources/js/payment.js') }}"></script>
@endpush