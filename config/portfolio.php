<?php

return [

    /*
    | Values that count as "not filled in yet". Anything matching is hidden from public
    | pages, JSON-LD, the sitemap and llms.txt, and listed on the admin Launch checklist.
    */
    'placeholders' => [
        'contains' => ['@example.com', '/placeholder', '[placeholder]', 'example.org', 'example.net'],
        'exact' => ['+10000000000', '+0000000000', 'placeholder', 'TODO', 'tbd'],
    ],

    /* Fallback recipient for inquiries while the Site Settings email is a placeholder. */
    'contact_address' => env('MAIL_CONTACT_ADDRESS'),

    'admin_path' => env('ADMIN_PATH', 'admin'),

    'turnstile' => [
        'site_key' => env('TURNSTILE_SITE_KEY'),
        'secret_key' => env('TURNSTILE_SECRET_KEY'),
    ],

    'contact' => [
        'project_types' => ['laravel-app', 'saas', 'ecommerce', 'admin-panel', 'api', 'audit', 'other'],
        'budget_ranges' => ['under-3k', '3k-7.5k', '7.5k-15k', '15k-30k', '30k-plus', 'not-sure'],
        'min_seconds' => 3,
        'max_minutes' => 120,
        'rate_limits' => [
            'ip_per_10_minutes' => 3,
            'ip_per_day' => 10,
            'email_per_day' => 3,
        ],
    ],
];
