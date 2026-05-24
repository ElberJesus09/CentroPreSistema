<?php

return [
    'headers' => [
        'X-Frame-Options' => env('SECURITY_X_FRAME_OPTIONS', 'SAMEORIGIN'),
        'X-Content-Type-Options' => 'nosniff',
        'Referrer-Policy' => env('SECURITY_REFERRER_POLICY', 'strict-origin-when-cross-origin'),
        'Permissions-Policy' => env(
            'SECURITY_PERMISSIONS_POLICY',
            'camera=(), microphone=(), geolocation=(), payment=()'
        ),
        'X-Permitted-Cross-Domain-Policies' => 'none',
    ],

    'hsts' => env('SECURITY_HSTS', 'max-age=31536000; includeSubDomains'),
];
