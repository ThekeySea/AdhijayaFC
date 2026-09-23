<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Midtrans
    |--------------------------------------------------------------------------
    |
    | Konfigurasi Midtrans Sandbox. Server key hanya boleh dibaca di sisi
    | server dan tidak boleh masuk ke client bundle.
    |
    */

    'server_key' => env('MIDTRANS_SERVER_KEY'),
    'client_key' => env('MIDTRANS_CLIENT_KEY'),
    'is_production' => env('MIDTRANS_IS_PRODUCTION', false),

    /* Core API v2 (charge QRIS / VA). */
    'api_url' => env('MIDTRANS_IS_PRODUCTION', false)
        ? 'https://api.midtrans.com'
        : 'https://api.sandbox.midtrans.com',

    /* Host Snap (dashboard / redirect) — beda dari Core API. */
    'snap_url' => env('MIDTRANS_IS_PRODUCTION', false)
        ? 'https://app.midtrans.com'
        : 'https://app.sandbox.midtrans.com',

    'snap_js' => env('MIDTRANS_IS_PRODUCTION', false)
        ? 'https://app.midtrans.com/snap/snap.js'
        : 'https://app.sandbox.midtrans.com/snap/snap.js',

    /*
    | Metode overlay custom: QRIS dan transfer bank (VA).
    */
    'enabled_payments' => ['qris', 'bank_transfer'],

    /*
    | Total di atas nominal ini kena DP 50%.
    */
    'dp_threshold' => 100000,

    'dp_percent' => 50,

];
