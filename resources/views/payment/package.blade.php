@extends('layouts.user')

@section('title', 'Pembayaran Paket')
@section('page-title', 'Pembayaran Paket')
@section('page-subtitle', 'Selesaikan pembayaran untuk mengaktifkan akses paket Anda')

@section('content')
<div class="mx-auto max-w-2xl">
    @if (session('error'))
        <div class="mb-5 rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700">{{ session('error') }}</div>
    @endif

    <div class="pc-card overflow-hidden">
        <div class="border-b border-slate-200 bg-orange-50 px-6 py-5 sm:px-8">
            <p class="text-xs font-bold uppercase tracking-wider text-orange-600">Paket terpilih</p>
            <h2 class="mt-2 text-xl font-bold text-slate-900">{{ $package['name'] }}</h2>
            <p class="mt-1 text-sm text-slate-600">Aktif selama {{ $package['duration'] }} dengan kuota {{ $package['quota'] }}x cek plagiasi.</p>
        </div>

        <div class="p-6 sm:p-8">
            <div class="flex items-end justify-between border-b border-slate-200 pb-5">
                <span class="text-sm font-semibold text-slate-500">Total pembayaran</span>
                <span class="text-3xl font-black text-slate-900">Rp {{ number_format($package['amount'], 0, ',', '.') }}</span>
            </div>

            <form method="POST" action="{{ route('user.payment.package.pay', $packageKey) }}" class="mt-6">
                @csrf
                <button type="submit" class="pc-btn pc-btn-primary w-full justify-center bg-orange-500 hover:bg-orange-600">
                    Lanjut ke Pembayaran
                </button>
            </form>
            <p class="mt-4 text-center text-xs text-slate-500">Anda akan diarahkan ke halaman pembayaran DOKU.</p>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    @if (session('payment_required_alert'))
        Swal.fire({
            icon: 'warning',
            title: 'Anda belum menyelesaikan pembayaran',
            text: 'Selesaikan pembayaran paket ini terlebih dahulu untuk membuka menu lainnya.',
            confirmButtonText: 'Lanjut Bayar',
            confirmButtonColor: '#f97316',
        });
    @endif

    document.addEventListener('click', function (event) {
        const link = event.target.closest('a');

        if (!link || link.closest('form')) {
            return;
        }

        event.preventDefault();
        event.stopImmediatePropagation();

        Swal.fire({
            icon: 'warning',
            title: 'Anda belum menyelesaikan pembayaran',
            text: 'Selesaikan pembayaran paket ini terlebih dahulu untuk membuka menu lainnya.',
            confirmButtonText: 'Lanjut Bayar',
            confirmButtonColor: '#f97316',
        }).then(function (result) {
            if (result.isConfirmed) {
                document.querySelector('form[action*="/payment/package/"]')?.scrollIntoView({
                    behavior: 'smooth',
                    block: 'center',
                });
            }
        });
    }, true);
</script>
@endpush
