document.addEventListener(`DOMContentLoaded`,()=>{let e=document.querySelector(`[data-payment-page]`);if(!e)return;let t=e.dataset.snapToken,n=e.dataset.paymentLinkUrl,r=e.dataset.finishRoute,i=e.dataset.pendingRoute,a=e.dataset.errorRoute,o=e.querySelector(`[data-pay-button]`);if(!o)return;let s=()=>{o.disabled=!1,o.innerHTML=`
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h10M9 5h6a2 2 0 012 2v2H7V7a2 2 0 012-2z"/>
            </svg>
            <span>Bayar Sekarang</span>
        `};o.addEventListener(`click`,function(){if(!t&&!n){window.Swal?Swal.fire({icon:`error`,title:`Pembayaran belum siap`,text:`Pastikan konfigurasi pembayaran sudah benar di file .env.`,confirmButtonText:`OK`}):alert(`Pembayaran belum siap. Pastikan konfigurasi pembayaran sudah benar di file .env.`);return}if(!window.snap||typeof window.snap.pay!=`function`){if(n){window.location.href=n;return}window.Swal?Swal.fire({icon:`error`,title:`Pembayaran belum siap`,text:`Pastikan konfigurasi pembayaran sudah benar di file .env.`,confirmButtonText:`OK`}):alert(`Pembayaran belum siap. Pastikan konfigurasi pembayaran sudah benar di file .env.`);return}o.disabled=!0,o.innerHTML=`
            <svg class="animate-spin w-5 h-5" fill="none" viewBox="0 0 24 24" aria-hidden="true">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
            </svg>
            <span>Membuka pembayaran...</span>
        `,window.snap.pay(t,{onSuccess:function(){window.location.href=r},onPending:function(){window.location.href=i},onError:function(){window.location.href=a},onClose:function(){s()}})})});