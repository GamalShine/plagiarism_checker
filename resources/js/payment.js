document.addEventListener('DOMContentLoaded', () => {
    const paymentRoot = document.querySelector('[data-payment-page]');

    if (!paymentRoot) {
        return;
    }

    const snapToken = paymentRoot.dataset.snapToken;
    const paymentLinkUrl = paymentRoot.dataset.paymentLinkUrl;
    const finishRoute = paymentRoot.dataset.finishRoute;
    const pendingRoute = paymentRoot.dataset.pendingRoute;
    const errorRoute = paymentRoot.dataset.errorRoute;
    const payButton = paymentRoot.querySelector('[data-pay-button]');

    if (!payButton) {
        return;
    }

    const resetButton = () => {
        payButton.disabled = false;
        payButton.innerHTML = `
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h10M9 5h6a2 2 0 012 2v2H7V7a2 2 0 012-2z"/>
            </svg>
            <span>Bayar Sekarang</span>
        `;
    };

    payButton.addEventListener('click', function () {
        if (!snapToken && !paymentLinkUrl) {
            if (window.Swal) {
                Swal.fire({
                    icon: 'error',
                    title: 'Pembayaran belum siap',
                    text: 'Pastikan konfigurasi pembayaran sudah benar di file .env.',
                    confirmButtonText: 'OK'
                });
            } else {
                alert('Pembayaran belum siap. Pastikan konfigurasi pembayaran sudah benar di file .env.');
            }
            return;
        }

        if (!window.snap || typeof window.snap.pay !== 'function') {
            if (paymentLinkUrl) {
                window.location.href = paymentLinkUrl;
                return;
            }

            if (window.Swal) {
                Swal.fire({
                    icon: 'error',
                    title: 'Pembayaran belum siap',
                    text: 'Pastikan konfigurasi pembayaran sudah benar di file .env.',
                    confirmButtonText: 'OK'
                });
            } else {
                alert('Pembayaran belum siap. Pastikan konfigurasi pembayaran sudah benar di file .env.');
            }
            return;
        }

        payButton.disabled = true;
        payButton.innerHTML = `
            <svg class="animate-spin w-5 h-5" fill="none" viewBox="0 0 24 24" aria-hidden="true">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
            </svg>
            <span>Membuka pembayaran...</span>
        `;

        window.snap.pay(snapToken, {
            onSuccess: function () {
                window.location.href = finishRoute;
            },
            onPending: function () {
                window.location.href = pendingRoute;
            },
            onError: function () {
                window.location.href = errorRoute;
            },
            onClose: function () {
                resetButton();
            }
        });
    });
});
