<?php

$localTesting = filter_var(env('EMAIL_LOCAL_TESTING', false), FILTER_VALIDATE_BOOL);
$localFrom = (string) env('EMAIL_LOCAL_TEST_FROM', env('EMAIL_USER', ''));
$overrideSenders = filter_var(env('EMAIL_LOCAL_TEST_OVERRIDE_SENDERS', true), FILTER_VALIDATE_BOOL);

$department = [
    'support' => env('MAILBOX_SUPPORT'),
    'info' => env('MAILBOX_INFO'),
    'hr' => env('MAILBOX_HR'),
    'accounts' => env('MAILBOX_ACCOUNTS'),
    'production' => env('MAILBOX_PRODUCTION'),
    'sales' => env('MAILBOX_SALES'),
];

$system = [
    'notifications' => env('MAILBOX_NOTIFICATIONS'),
    'noreply' => env('MAILBOX_NOREPLY'),
    'billing' => env('MAILBOX_BILLING'),
];

if ($localTesting && $overrideSenders && filled($localFrom)) {
    $department = array_map(static fn () => $localFrom, $department);
    $system = array_map(static fn () => $localFrom, $system);
}

$productionOnboardingFrom = env('ONBOARDING_MAIL_FROM_ADDRESS', env('MAILBOX_INFO', 'info@janaprints.co.ke'));
$productionOnboardingReply = env('ONBOARDING_MAIL_REPLY_TO', env('MAILBOX_INFO', 'info@janaprints.co.ke'));

return [

    /*
    |--------------------------------------------------------------------------
    | Corporate Mail Domain
    |--------------------------------------------------------------------------
    |
    | Corporate email addresses (firstname.lastname@domain) still use this domain
    | during local testing — only SMTP delivery routes through Gmail.
    |
    */

    'domain' => env('MAIL_DOMAIN', 'janaprints.co.ke'),

    'department' => $department,

    'system' => $system,

    'cpanel' => [
        'host' => env('CPANEL_HOST'),
        'username' => env('CPANEL_USERNAME'),
        'api_token' => env('CPANEL_API_TOKEN'),
        'port' => (int) env('CPANEL_PORT', 2083),
        'default_quota_mb' => (int) env('CPANEL_MAILBOX_QUOTA_MB', 250),
    ],

    'activation' => [
        'token_expiry_hours' => (int) env('EMPLOYEE_ACTIVATION_EXPIRY_HOURS', 72),
        'support_email' => env('MAILBOX_SUPPORT', env('MAILBOX_INFO')),
    ],

    'onboarding' => [
        'mailer' => $localTesting
            ? env('EMAIL_LOCAL_TEST_MAILER', 'onboarding')
            : env('ONBOARDING_MAIL_MAILER', 'onboarding'),
        'from_address' => $localTesting && filled($localFrom) ? $localFrom : $productionOnboardingFrom,
        'from_name' => env('ONBOARDING_MAIL_FROM_NAME', env('APP_NAME', 'Jana Prints')),
        'reply_to' => $localTesting && filled($localFrom) ? $localFrom : $productionOnboardingReply,
    ],

];
