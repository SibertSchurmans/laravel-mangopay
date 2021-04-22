<?php

return [

    /*
    |--------------------------------------------------------------------------
    | MangoPay Credentials
    |--------------------------------------------------------------------------
    |
    | Specify the key and secret. Both can be found here:
    | https://hub.mangopay.com/api-keys
    |
    | You can also set the environment that is used.
    |
    | Supported environments: "sandbox", "production"
    |
    */

    'env' => env('MANGOPAY_ENVIRONMENT', 'sandbox'),
    'key' => env('MANGOPAY_KEY'),
    'secret' => env('MANGOPAY_SECRET'),

    /*
    |--------------------------------------------------------------------------
    | Default values
    |--------------------------------------------------------------------------
    |
    */

    'currency' => 'EUR',
    'culture' => 'BE',

    /*
    |--------------------------------------------------------------------------
    | Directories
    |--------------------------------------------------------------------------
    |
    | Mangopay needs to have directories for temporary files.
    | Default these directories are created in the default storage path.
    |
    */

    'directories' => [
        'sandbox' => env("MANGOPAY_SANDBOX_DIRECTORY", storage_path('mangopay' . DIRECTORY_SEPARATOR . 'sandbox' . DIRECTORY_SEPARATOR)),
        'production' => env("MANGOPAY_PRODUCTION_DIRECTORY", storage_path('mangopay' . DIRECTORY_SEPARATOR . 'production' . DIRECTORY_SEPARATOR))
    ],

    /*
    |--------------------------------------------------------------------------
    | Fees
    |--------------------------------------------------------------------------
    |
    | The different fees that are used in transactions (percentage)
    |
    */

    'fees' => [
        "top_up" => 0.00,
        "wallet_transactions" => 0.00
    ],
];
