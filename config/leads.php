<?php

return [

    'admin_email' => env('JANAPRINTS_ADMIN_EMAIL', env('MAIL_FROM_ADDRESS')),

    'rate_limit' => [
        'max_attempts' => (int) env('PUBLIC_LEADS_RATE_LIMIT', 5),
        'decay_minutes' => (int) env('PUBLIC_LEADS_RATE_DECAY', 15),
    ],

    'artwork' => [
        'disk' => 'public',
        'directory' => 'quote-artwork',
        'max_size_kb' => 25600,
        'allowed_extensions' => ['pdf', 'ai', 'eps', 'psd', 'jpg', 'jpeg', 'png', 'svg'],
        'allowed_mimes' => [
            'application/pdf',
            'application/postscript',
            'application/illustrator',
            'image/vnd.adobe.photoshop',
            'image/jpeg',
            'image/png',
            'image/svg+xml',
        ],
    ],

];
