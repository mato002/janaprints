<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Queue Names
    |--------------------------------------------------------------------------
    |
    | Database queue is the default driver. Switch QUEUE_CONNECTION to redis
    | later without changing job queue assignments.
    |
    */

    'queues' => [
        'default' => env('PLATFORM_QUEUE_DEFAULT', 'default'),
        'notifications' => env('PLATFORM_QUEUE_NOTIFICATIONS', 'notifications'),
        'reports' => env('PLATFORM_QUEUE_REPORTS', 'reports'),
        'documents' => env('PLATFORM_QUEUE_DOCUMENTS', 'documents'),
        'emails' => env('PLATFORM_QUEUE_EMAILS', 'emails'),
        'sms' => env('PLATFORM_QUEUE_SMS', 'sms'),
        'whatsapp' => env('PLATFORM_QUEUE_WHATSAPP', 'whatsapp'),
        'imports' => env('PLATFORM_QUEUE_IMPORTS', 'imports'),
        'exports' => env('PLATFORM_QUEUE_EXPORTS', 'exports'),
        'integrations' => env('PLATFORM_QUEUE_INTEGRATIONS', 'integrations'),
    ],

    /*
    |--------------------------------------------------------------------------
    | Cache Categories (seconds)
    |--------------------------------------------------------------------------
    |
    | Do not cache transactional stock balances unless invalidation is explicit.
    |
    */

    'cache' => [
        'navigation' => (int) env('PLATFORM_CACHE_NAVIGATION_TTL', 300),
        'permissions' => (int) env('PLATFORM_CACHE_PERMISSIONS_TTL', 600),
        'dashboard' => (int) env('PLATFORM_CACHE_DASHBOARD_TTL', 60),
        'system_settings' => (int) env('PLATFORM_CACHE_SETTINGS_TTL', 900),
        'branches' => (int) env('PLATFORM_CACHE_BRANCHES_TTL', 900),
        'departments' => (int) env('PLATFORM_CACHE_DEPARTMENTS_TTL', 900),
        'lead_stages' => (int) env('PLATFORM_CACHE_LEAD_STAGES_TTL', 900),
        'quotation_statuses' => (int) env('PLATFORM_CACHE_QUOTATION_STATUSES_TTL', 3600),
        'inventory_categories' => (int) env('PLATFORM_CACHE_INVENTORY_CATEGORIES_TTL', 900),
        'stock_balance' => (int) env('PLATFORM_CACHE_STOCK_BALANCE_TTL', 30),
    ],

    /*
    |--------------------------------------------------------------------------
    | Default Numbering Template
    |--------------------------------------------------------------------------
    |
    | Example: JANA-HQ-QUOTE-2026-00001
    |
    */

    'numbering' => [
        'default_template' => '{company}-{branch}-{type}-{year}-{number}',
        'default_padding' => 5,
    ],

    /*
    |--------------------------------------------------------------------------
    | List Pagination
    |--------------------------------------------------------------------------
    */

    'pagination' => [
        'default' => 15,
        'admin' => 20,
        'permissions' => 50,
    ],

];
