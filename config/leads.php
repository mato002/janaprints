<?php

return [

    /**
     * Inbox for internal alerts when guests submit contact/quote forms.
     */
    'admin_email' => env('JANAPRINTS_ADMIN_EMAIL', env('MAILBOX_INFO', env('MAIL_FROM_ADDRESS'))),

    /**
     * SMTP mailer for storefront guest emails — defaults to onboarding (info@ cPanel).
     */
    'mailer' => env('PUBLIC_LEADS_MAIL_MAILER', env('ONBOARDING_MAIL_MAILER', 'onboarding')),

    'from_name' => env('PUBLIC_LEADS_FROM_NAME', env('ONBOARDING_MAIL_FROM_NAME', env('MAIL_FROM_NAME', env('APP_NAME')))),
    'reply_to' => env('PUBLIC_LEADS_REPLY_TO', env('MAILBOX_INFO', env('ONBOARDING_MAIL_REPLY_TO', env('MAIL_FROM_ADDRESS')))),

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
