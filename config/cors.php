<?php

return [

    /*
    |--------------------------------------------------------------------------
    | CORS Paths
    |--------------------------------------------------------------------------
    |
    | Which paths should have CORS headers applied.
    |
    */

    'paths' => ['api/*', 'register', 'login', 'sanctum/csrf-cookie'],

    /*
    |--------------------------------------------------------------------------
    | Allowed Methods
    |--------------------------------------------------------------------------
    */

    'allowed_methods' => ['*'],

    /*
    |--------------------------------------------------------------------------
    | Allowed Origins
    |--------------------------------------------------------------------------
    |
    | React app kon origin theke hit korbe sheta ekhane dite hobe.
    | * diye dile sob origin allow hoye jabe (only dev e use koro).
    |
    */

    'allowed_origins' => ['http://localhost:3000'],

    // jodi 127.0.0.1 use koro tokhon etao add korte paro:
    // 'allowed_origins' => ['http://localhost:3000', 'http://127.0.0.1:3000'],

    'allowed_origins_patterns' => [],

    /*
    |--------------------------------------------------------------------------
    | Allowed Headers
    |--------------------------------------------------------------------------
    */

    'allowed_headers' => ['*'],

    /*
    |--------------------------------------------------------------------------
    | Exposed Headers
    |--------------------------------------------------------------------------
    */

    'exposed_headers' => [],

    /*
    |--------------------------------------------------------------------------
    | Max Age
    |--------------------------------------------------------------------------
    */

    'max_age' => 0,

    /*
    |--------------------------------------------------------------------------
    | Supports Credentials
    |--------------------------------------------------------------------------
    |
    | jodi cookies/session পাঠাতে চাও (Sanctum etc.), tokhon true kore
    | React side e axios/fetch e withCredentials: true dite hobe।
    |
    */

    'supports_credentials' => false,

];
