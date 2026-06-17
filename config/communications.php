<?php

return [

    'email_attachment_disk' => env('COMMUNICATIONS_EMAIL_ATTACHMENT_DISK', 'local'),

    /*
    |--------------------------------------------------------------------------
    | Email retention policy (infrastructure only — no automatic deletion)
    |--------------------------------------------------------------------------
    */
    'retention_days' => (int) env('EMAIL_RETENTION_DAYS', 3650),

    /*
    |--------------------------------------------------------------------------
    | Queue certification thresholds
    |--------------------------------------------------------------------------
    */
    'queue' => [
        'stuck_sending_minutes' => (int) env('EMAIL_QUEUE_STUCK_MINUTES', 15),
        'warning_backlog' => (int) env('EMAIL_QUEUE_WARNING_BACKLOG', 10),
        'critical_backlog' => (int) env('EMAIL_QUEUE_CRITICAL_BACKLOG', 50),
    ],

    /*
    |--------------------------------------------------------------------------
    | Synchronous delivery purposes
    |--------------------------------------------------------------------------
    |
    | Security-sensitive transactional mail is sent immediately instead of
    | waiting for a queue worker (avoids stale workers blocking password resets).
    |
    */
    'sync_deliver_purposes' => [
        'password_reset',
    ],

];
