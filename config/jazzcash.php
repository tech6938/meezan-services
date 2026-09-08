<?php

return [
    'merchant_id' => env('JAZZCASH_MERCHANT_ID'),
    'password'    => env('JAZZCASH_PASSWORD'),
    'salt'        => env('JAZZCASH_INTEGERITY_SALT'),
    'merchant_mpin' => env('JAZZCASH_MERCHANT_MPIN', ''),
    'env'         => env('JAZZCASH_ENV', 'sandbox'),

    'endpoints' => [
        'sandbox' => [
            'mwallet' => 'https://onlinepayments.jazzcash.com.pk/payment-orchestrator/api/v2/rest/payments/m-wallet',
            'status'  => 'https://onlinepayments.jazzcash.com.pk/payment-orchestrator/api/v2/rest/payments/status/inquiry',
            'refund'  => 'https://onlinepayments.jazzcash.com.pk/payment-orchestrator/api/v1/rest/payments/m-wallet/refund',
        ],
        'production' => [
            'mwallet' => 'https://onlinepayments.jazzcash.com.pk/payment-orchestrator/api/v2/rest/payments/m-wallet',
            'status'  => 'https://onlinepayments.jazzcash.com.pk/payment-orchestrator/api/v2/rest/payments/status/inquiry',
            'refund'  => 'https://onlinepayments.jazzcash.com.pk/payment-orchestrator/api/v1/rest/payments/m-wallet/refund',
        ],
    ],
];