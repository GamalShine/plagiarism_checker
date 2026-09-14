<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Midtrans Configuration
    |--------------------------------------------------------------------------
    |
    | Server Key  : dipakai untuk server-side API calls (jangan expose ke client)
    | Client Key  : dipakai di front-end (Snap.js)
    | is_production: false = Sandbox, true = Production
    |
    */

    'server_key'        => env('MIDTRANS_SERVER_KEY', ''),
    'client_key'        => env('MIDTRANS_CLIENT_KEY', ''),
    'is_production'     => env('MIDTRANS_IS_PRODUCTION', false),
    'payment_link_url'  => env('MIDTRANS_PAYMENT_LINK_URL', ''),

    /*
    |--------------------------------------------------------------------------
    | Harga per cek plagiasi (IDR)
    |--------------------------------------------------------------------------
    | Minimal Midtrans Sandbox: IDR 10.000
    */
    'check_price'       => env('MIDTRANS_CHECK_PRICE', 10000),
];
