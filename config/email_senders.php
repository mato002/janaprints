<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Email Purpose → Mailbox Purpose Mapping
    |--------------------------------------------------------------------------
    |
    | Maps application email purposes to mailbox purposes resolved via
    | MailboxAddressResolver (config/mailboxes.php). Addresses are never
    | hardcoded in Mail classes.
    |
    */

    'purposes' => [
        'employee_onboarding' => 'hr',
        'employee_message' => 'hr',
        'payslip' => 'hr',
        'password_reset' => 'noreply',
        'invoice' => 'accounts',
        'receipt' => 'accounts',
        'quotation' => 'sales',
        'support_ticket' => 'support',
        'production_update' => 'production',
        'system_alert' => 'notifications',
        'storefront_contact' => 'info',
        'storefront_quote' => 'info',
    ],

    /*
    |--------------------------------------------------------------------------
    | Department / System Mailbox Catalog (admin visibility)
    |--------------------------------------------------------------------------
    */

    'catalog' => [
        'info' => [
            'label' => 'General Information',
            'recommended_use' => 'Public-facing enquiries and general company contact.',
        ],
        'support' => [
            'label' => 'Customer Support',
            'recommended_use' => 'Support tickets, help desk replies, and customer assistance.',
        ],
        'hr' => [
            'label' => 'Human Resources',
            'recommended_use' => 'Employee onboarding, HR correspondence, and internal people operations.',
        ],
        'accounts' => [
            'label' => 'Accounts & Finance',
            'recommended_use' => 'Invoices, receipts, billing, and payment-related email.',
        ],
        'sales' => [
            'label' => 'Sales',
            'recommended_use' => 'Quotations, proposals, and sales follow-ups.',
        ],
        'production' => [
            'label' => 'Production',
            'recommended_use' => 'Job updates, production status, and fulfilment notifications.',
        ],
        'billing' => [
            'label' => 'Billing',
            'recommended_use' => 'Statements, dunning, and billing reminders.',
        ],
        'notifications' => [
            'label' => 'System Notifications',
            'recommended_use' => 'Automated ERP alerts and operational notifications.',
        ],
        'noreply' => [
            'label' => 'No Reply',
            'recommended_use' => 'Legacy transactional mailboxes. Prefer info@ for user-facing auth and onboarding email.',
        ],
    ],

];
