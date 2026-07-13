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

    'commercial_reports' => [
        /** Days before completed export files are expired and removed. */
        'export_ttl_days' => (int) env('COMMERCIAL_REPORT_EXPORT_TTL_DAYS', 7),
    ],

    /** Bump when sidebar workspace visibility rules change (invalidates cached nav metadata). */
    'navigation_cache_version' => 5,

    'cache' => [
        'navigation' => (int) env('PLATFORM_CACHE_NAVIGATION_TTL', 300),
        'feature_discovery' => (int) env('PLATFORM_CACHE_FEATURE_DISCOVERY_TTL', 300),
        'permissions' => (int) env('PLATFORM_CACHE_PERMISSIONS_TTL', 600),
        'dashboard' => (int) env('PLATFORM_CACHE_DASHBOARD_TTL', 60),
        'assets_dashboard' => (int) env('PLATFORM_CACHE_ASSETS_DASHBOARD_TTL', 60),
        'machines_dashboard' => (int) env('PLATFORM_CACHE_MACHINES_DASHBOARD_TTL', 60),
        'maintenance_dashboard' => (int) env('PLATFORM_CACHE_MAINTENANCE_DASHBOARD_TTL', 60),
        'production_hub_counts' => (int) env('PLATFORM_CACHE_PRODUCTION_HUB_COUNTS_TTL', 60),
        'operational_registers' => (int) env('PLATFORM_CACHE_OPERATIONAL_REGISTERS_TTL', 60),
        'custody_dashboard' => (int) env('PLATFORM_CACHE_CUSTODY_DASHBOARD_TTL', 60),
        'asset_finance_dashboard' => (int) env('PLATFORM_CACHE_ASSET_FINANCE_DASHBOARD_TTL', 60),
        'asset_acquisition_dashboard' => (int) env('PLATFORM_CACHE_ASSET_ACQUISITION_DASHBOARD_TTL', 60),
        'asset_360' => (int) env('PLATFORM_CACHE_ASSET_360_TTL', 60),
        'asset_executive_dashboard' => (int) env('PLATFORM_CACHE_ASSET_EXECUTIVE_DASHBOARD_TTL', 120),
        'asset_branch_intelligence' => (int) env('PLATFORM_CACHE_ASSET_BRANCH_INTELLIGENCE_TTL', 120),
        'asset_analytics' => (int) env('PLATFORM_CACHE_ASSET_ANALYTICS_TTL', 180),
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

    /*
    |--------------------------------------------------------------------------
    | Backup Governance
    |--------------------------------------------------------------------------
    */

    'backups' => [
        'root' => storage_path('app/backups'),
        'retention_days' => [
            'database' => (int) env('BACKUP_RETENTION_DATABASE_DAYS', 30),
            'file' => (int) env('BACKUP_RETENTION_FILE_DAYS', 14),
            'storage' => (int) env('BACKUP_RETENTION_STORAGE_DAYS', 14),
        ],
        'directories' => [
            'database' => 'database',
            'file' => 'files',
            'storage' => 'storage',
        ],
    ],

    'retention' => [
        'defaults' => [
            'audit_logs' => [
                'archive_after_days' => 365,
                'delete_after_days' => 2555,
                'retention_period_days' => 2555,
            ],
            'activity_logs' => [
                'archive_after_days' => 90,
                'delete_after_days' => 365,
                'retention_period_days' => 365,
            ],
            'documents' => [
                'archive_after_days' => 180,
                'delete_after_days' => 730,
                'retention_period_days' => 730,
            ],
            'communications' => [
                'archive_after_days' => 90,
                'delete_after_days' => 365,
                'retention_period_days' => 365,
            ],
            'files' => [
                'archive_after_days' => 180,
                'delete_after_days' => 730,
                'retention_period_days' => 730,
            ],
            'backups' => [
                'archive_after_days' => 14,
                'delete_after_days' => 30,
                'retention_period_days' => 30,
            ],
        ],
    ],

];
