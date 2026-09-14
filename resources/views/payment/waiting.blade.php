@extends($layout ?? 'layouts.user')

@section('title', 'Menunggu Konfirmasi Pembayaran')
@section('page-title', 'Menunggu Pembayaran')
@section('page-subtitle', 'Kami sedang menunggu konfirmasi pembayaran dari provider')

@section('content')
<div class="max-w-md mx-auto" x-data="paymentWaiting()" x-init="startPolling()">
    <div class="pc-card p-8 text-center">
        {{-- Animated icon --}}
        <div class="w-20 h-20 mx-auto mb-6 relative">
            <div class="w-20 h-20 rounded-full border-4 border-amber-200 animate-ping absolute inset-0 opacity-40"></div>
            <div class="w-20 h-20 rounded-full flex items-center justify-center relative"
                 style="background: linear-gradient(135deg, #f59e0b22, #fbbf2422);">
                <svg class="w-9 h-9 text-amber-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                          d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
            </div>
        </div>

        <h2 class="text-xl font-bold mb-2">Pembayaran Dalam Proses</h2>
        <p class="text-sm mb-1" style="color: var(--pc-text-muted);">
            Order ID: <span class="font-mono font-semibold">{{ $payment->order_id }}</span>
        </p>
        <p class="text-sm mb-6" style="color: var(--pc-text-muted);">
            Halaman ini akan otomatis refresh saat pembayaran dikonfirmasi.
        </p>

        {{-- Status indicator --}}
        <div class="rounded-2xl border p-4 mb-6 text-left"
             style="background: var(--pc-bg-subtle); border-color: var(--pc-border);">
            <div class="flex items-center gap-3">
                <div class="w-2.5 h-2.5 rounded-full bg-amber-400 animate-pulse shrink-0"></div>
                <div>
                    <p class="text-sm font-semibold">Menunggu konfirmasi</p>
                    <p class="text-xs" style="color: var(--pc-text-muted);" x-text="statusText"></p>
                </div>
            </div>
        </div>

        <p class="text-xs mb-6" style="color: var(--pc-text-subtle);">
            Jika sudah selesai bayar tapi halaman belum redirect, klik tombol di bawah.
        </p>

        <form method="POST" action="{{ route('user.payment.confirm', $payment->order_id) }}" class="mb-3">
            @csrf
            <button type="submit" class="pc-btn-primary w-full flex items-center justify-center gap-2">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                </svg>
                Saya Sudah Bayar, Lanjutkan
            </button>
        </form>

        <button @click="checkNow()" :disabled="checking"
                class="pc-btn-secondary w-full flex items-center justify-center gap-2 mb-3">
            <svg x-show="!checking" class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                      d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
            </svg>
            <svg x-show="checking" class="animate-spin w-4 h-4" fill="none" viewBox="0 0 24 24">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"/>
            </svg>
            <span x-text="checking ? 'Memeriksa...' : 'Cek Status Sekarang'"></span>
        </button>

        <a href="{{ route('user.plagiarism.index') }}" class="pc-btn-link text-sm">
            ← Kembali ke Cek Plagiarisme
        </a>
    </div>
</div>
@endsection

@push('scripts')
<script>
function paymentWaiting() {
    return {
        checking: false,
        statusUrl: '{{ route("user.payment.status", $payment->order_id) }}',
        statusText: 'Sistem polling setiap 3 detik...',
        pollInterval: null,

        startPolling() {
            let seconds = 0;
            this.pollInterval = setInterval(async () => {
                seconds += 3;
                this.statusText = `Memeriksa status... (${seconds} detik lalu)`;
                await this.poll();
            }, 3000);
        },

        async checkNow() {
            this.checking = true;
            await this.poll();
            this.checking = false;
        },

        async poll() {
            try {
                const res  = await fetch(this.statusUrl);
                const data = await res.json();

                if (data.plagiarism_status === 'completed' && data.result_url) {
                    clearInterval(this.pollInterval);
                    window.location.href = data.result_url;
                } else if (data.status === 'failed' || data.plagiarism_status === 'failed') {
                    clearInterval(this.pollInterval);
                    window.location.href = '{{ route("user.payment.error", $payment->order_id) }}';
                } else if (data.status === 'paid') {
                    this.statusText = 'Pembayaran diterima, pengecekan plagiarisme sedang berjalan...';
                }
            } catch (e) { /* retry silently */ }
        }
    }
}
</script>
@endpush
