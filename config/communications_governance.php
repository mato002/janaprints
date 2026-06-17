<?php

/**
 * Communications permission matrix (COM-EMAIL-IMPLEMENT-005).
 *
 * Document-only reference for least-privilege governance. Enforced via
 * RolesAndPermissionsSeeder, route middleware, and policies.
 */
return [

    'roles' => [
        'Company Admin' => [
            'email_center' => ['view', 'send', 'schedule', 'manage', 'audit'],
            'document_email' => ['quotations', 'invoices', 'receipts', 'onboarding'],
            'notes' => 'Full Email Center and document email access.',
        ],
        'Sales' => [
            'email_center' => ['view', 'send'],
            'document_email' => ['quotations'],
            'notes' => 'Compose and view Email Center; send quotations via sales workflow (quotations.send).',
        ],
        'Accountant' => [
            'email_center' => ['view'],
            'document_email' => ['invoices', 'receipts'],
            'notes' => 'Read-only Email Center; send invoices/receipts from sales screens (invoices.view, payments.receipt.email).',
        ],
        'HR' => [
            'email_center' => ['view'],
            'document_email' => ['onboarding'],
            'notes' => 'Read-only Email Center; employee onboarding emails via HR jobs (no Email Center compose).',
        ],
        'Staff' => [
            'email_center' => ['view'],
            'document_email' => [],
            'notes' => 'Read-only Email Center.',
        ],
        'Viewer' => [
            'email_center' => ['view'],
            'document_email' => [],
            'notes' => 'Read-only Email Center.',
        ],
    ],

    'permission_map' => [
        'communications.email.view' => 'Dashboard, inbox, queue, sent, reports, analytics, message detail',
        'communications.email.send' => 'Compose, send draft, cancel queued, retry failed',
        'communications.email.schedule' => 'Schedule campaign sends (future-dated)',
        'communications.email.manage' => 'Settings, diagnostics, certification report, template sync',
        'communications.email.audit' => 'Delivery tracking index',
        'quotations.send' => 'Quotation document email from sales',
        'invoices.view' => 'Invoice document email from accounts (posted invoices)',
        'payments.receipt.email' => 'Receipt document email from payments',
    ],

    'known_gaps' => [
        'Document emails (invoice/receipt/quotation) use domain permissions, not communications.email.send — intentional separation.',
        'Campaign scheduling requires communications.email.schedule; bulk campaign send uses communications.email.send.',
        'No per-user "view own communications only" scope — tenant-scoped company visibility.',
    ],

];
