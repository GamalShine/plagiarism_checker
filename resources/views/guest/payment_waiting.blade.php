@extends('layouts.guest_check')

@section('title', 'Menunggu Pembayaran')
@section('page-title', 'Menunggu Pembayaran')
@section('page-subtitle', 'Pengecekan akan dimulai setelah pembayaran dikonfirmasi')

@section('content')
<div class="mx-auto max-w-lg px-5 py-12" x-data="guestPaymentWaiting()" x-init="start()">
    <div class="pc-card p-8 text-center">
        <div class="mx-auto mb-6 flex h-20 w-20 items-center justify-center rounded-full bg-amber-50 text-amber-500 dark:bg-amber-950/30">
            <svg class="h-10 w-10 animate-pulse" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
            </svg>
        </div>
        <h2 class="text-xl font-bold">Pembayaran Dalam Proses</h2>
        <p class="mt-2 text-sm text-slate-500" x-text="message"></p>
        <div class="mt-6 h-2 overflow-hidden rounded-full bg-slate-200 dark:bg-slate-700">
            <div class="h-full w-1/2 animate-pulse rounded-full bg-amber-500"></div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
function guestPaymentWaiting() {
    return {
        message: 'Menunggu konfirmasi pembayaran dari DOKU...',
        timer: null,
        start() {
            this.timer = setInterval(async () => {
                const response = await fetch(@json(route('guest.payment.status', $token)), { headers: { Accept: 'application/json' } });
                if (!response.ok) return;
                const data = await response.json();
                if (data.plagiarism_status === 'completed' && data.result_url) {
                    clearInterval(this.timer);
                    window.location.href = data.result_url;
                } else if (data.plagiarism_status === 'failed' || data.status === 'failed') {
                    clearInterval(this.timer);
                    this.message = 'Pembayaran diterima, tetapi proses pengecekan gagal. Silakan hubungi admin.';
                } else if (data.status === 'paid') {
                    this.message = 'Pembayaran diterima. Dokumen sedang dianalisis...';
                }
            }, 3000);
        }
    };
}
</script>
@endpush
