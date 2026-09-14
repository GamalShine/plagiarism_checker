<?php

return [
    /*
    |--------------------------------------------------------------------------
    | DOKU Payment Gateway Configuration
    |--------------------------------------------------------------------------
    |
    | Client ID   : Client ID merchant dari DOKU Dashboard
    | Secret Key  : Secret Key / Shared Key dari DOKU Dashboard
    | API Key     : API Key dari DOKU Dashboard
    | Environment : false = Sandbox (https://api-sandbox.doku.com), true = Production (https://api.doku.com)
    |
    */

    'client_id'      => env('DOKU_CLIENT_ID', 'BRN-0278-1787993889058'),
    'secret_key'     => env('DOKU_SECRET_KEY', 'SK-zFVwhMBdIZH81aEBxNrz'),
    'api_key'        => env('DOKU_API_KEY', 'doku_key_f22ec66aef6f41a2b809c5206fca539d'),
    'is_production'  => env('DOKU_IS_PRODUCTION', false),

    /*
    |--------------------------------------------------------------------------
    | Harga per cek plagiasi (IDR)
    |--------------------------------------------------------------------------
    */
    'check_price'    => env('DOKU_CHECK_PRICE', 10000),
];
