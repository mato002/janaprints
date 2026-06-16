<?php

/**
 * Local email testing mode — route onboarding mail through Gmail (or any
 * dev SMTP) before production SMTP is live.
 *
 * Set EMAIL_LOCAL_TESTING=false and restore ONBOARDING_MAIL_* for production.
 */
return [

    'enabled' => filter_var(env('EMAIL_LOCAL_TESTING', false), FILTER_VALIDATE_BOOL),

    /** Mailer name from config/mail.php used for onboarding invitations while testing. */
    'mailer' => env('EMAIL_LOCAL_TEST_MAILER', 'onboarding'),

    /** SMTP From / Reply-To while testing (must match authenticated SMTP user for Gmail). */
    'from_address' => env('EMAIL_LOCAL_TEST_FROM', env('EMAIL_USER')),
    'from_name' => env('EMAIL_LOCAL_TEST_FROM_NAME', env('APP_NAME', 'Jana Prints')),
    'reply_to' => env('EMAIL_LOCAL_TEST_REPLY_TO', env('EMAIL_USER')),

    /**
     * Map all department/system mailbox env addresses to the local test From address
     * so EmailSenderResolver From headers match the Gmail authenticated user.
     */
    'override_sender_addresses' => filter_var(env('EMAIL_LOCAL_TEST_OVERRIDE_SENDERS', true), FILTER_VALIDATE_BOOL),

    /** Show a banner on the Email Identity admin page while this mode is active. */
    'show_admin_banner' => filter_var(env('EMAIL_LOCAL_TEST_SHOW_BANNER', true), FILTER_VALIDATE_BOOL),

];
