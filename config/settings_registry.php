<?php

return [

    'sections' => [

        'hub' => [
            'label' => 'Settings',
            'description' => 'Control center for organization-wide configuration.',
            'settings' => [],
        ],

        'company' => [
            'label' => 'Company Settings',
            'description' => 'Defaults that apply across all branches unless overridden.',
            'settings' => [
                'default_currency' => [
                    'label' => 'Default currency',
                    'description' => 'ISO 4217 currency code used on documents.',
                    'type' => 'string',
                    'scopes' => ['company'],
                    'default' => 'KES',
                ],
                'default_tax_rate' => [
                    'label' => 'Default tax rate (%)',
                    'description' => 'Standard VAT or tax percentage applied to quotations and orders.',
                    'type' => 'integer',
                    'scopes' => ['company'],
                    'default' => 16,
                ],
                'default_payment_terms' => [
                    'label' => 'Default payment terms',
                    'description' => 'Payment terms shown on quotations and invoices.',
                    'type' => 'string',
                    'scopes' => ['company'],
                    'default' => 'Net 30',
                ],
                'fiscal_year_start_month' => [
                    'label' => 'Fiscal year start month',
                    'description' => 'Month number (1–12) when the fiscal year begins.',
                    'type' => 'integer',
                    'scopes' => ['company'],
                    'default' => 1,
                ],
            ],
        ],

        'branch' => [
            'label' => 'Branch Settings',
            'description' => 'Branch-specific display and operational preferences.',
            'settings' => [
                'branch_display_name' => [
                    'label' => 'Branch display name',
                    'description' => 'Friendly name shown on branch-scoped documents.',
                    'type' => 'string',
                    'scopes' => ['branch'],
                    'default' => '',
                ],
                'branch_phone' => [
                    'label' => 'Branch phone',
                    'description' => 'Contact phone number printed on branch documents.',
                    'type' => 'string',
                    'scopes' => ['branch'],
                    'default' => '',
                ],
                'branch_email' => [
                    'label' => 'Branch email',
                    'description' => 'Contact email for branch correspondence.',
                    'type' => 'string',
                    'scopes' => ['branch'],
                    'default' => '',
                ],
            ],
        ],

        'crm' => [
            'label' => 'CRM Settings',
            'description' => 'Customer and lead management defaults.',
            'settings' => [
                'lead_follow_up_reminder_days' => [
                    'label' => 'Lead follow-up reminder (days)',
                    'description' => 'Days before a scheduled follow-up to show dashboard reminders.',
                    'type' => 'integer',
                    'scopes' => ['company', 'branch'],
                    'default' => 1,
                ],
                'require_customer_email' => [
                    'label' => 'Require customer email',
                    'description' => 'When enabled, customer email is required on create.',
                    'type' => 'boolean',
                    'scopes' => ['company', 'branch'],
                    'default' => false,
                ],
                'auto_convert_lead_on_quote' => [
                    'label' => 'Link lead on quotation',
                    'description' => 'Suggest linking an open lead when creating a quotation for the same customer.',
                    'type' => 'boolean',
                    'scopes' => ['company'],
                    'default' => true,
                ],
            ],
        ],

        'quotation' => [
            'label' => 'Quotation Settings',
            'description' => 'Quotation validity, approval, and pricing defaults.',
            'settings' => [
                'quotation_validity_days' => [
                    'label' => 'Quotation validity (days)',
                    'description' => 'Default number of days a quotation remains valid.',
                    'type' => 'integer',
                    'scopes' => ['company', 'branch'],
                    'default' => 30,
                ],
                'quotation_require_approval' => [
                    'label' => 'Require approval before send',
                    'description' => 'Quotations must be approved before they can be sent to customers.',
                    'type' => 'boolean',
                    'scopes' => ['company', 'branch'],
                    'default' => true,
                ],
                'quotation_max_discount_percent' => [
                    'label' => 'Maximum discount (%)',
                    'description' => 'Maximum discount percentage without additional approval.',
                    'type' => 'integer',
                    'scopes' => ['company', 'branch'],
                    'default' => 10,
                ],
            ],
        ],

        'artwork' => [
            'label' => 'Artwork Settings',
            'description' => 'Artwork request and approval workflow defaults.',
            'settings' => [
                'artwork_requires_customer_approval' => [
                    'label' => 'Require customer approval',
                    'description' => 'Artwork must be approved by the customer before production.',
                    'type' => 'boolean',
                    'scopes' => ['company', 'branch'],
                    'default' => true,
                ],
                'artwork_default_due_days' => [
                    'label' => 'Default due date (days)',
                    'description' => 'Days from request date to set the default artwork due date.',
                    'type' => 'integer',
                    'scopes' => ['company', 'branch'],
                    'default' => 5,
                ],
                'artwork_auto_assign_designer' => [
                    'label' => 'Auto-assign designer',
                    'description' => 'Automatically assign artwork requests to the default designer role.',
                    'type' => 'boolean',
                    'scopes' => ['company'],
                    'default' => false,
                ],
            ],
        ],

        'sales-order' => [
            'label' => 'Sales Order Settings',
            'description' => 'Sales order confirmation and fulfilment defaults.',
            'settings' => [
                'sales_order_default_required_days' => [
                    'label' => 'Default required date (days)',
                    'description' => 'Days from order date for the default required delivery date.',
                    'type' => 'integer',
                    'scopes' => ['company', 'branch'],
                    'default' => 14,
                ],
                'sales_order_auto_create_job_card' => [
                    'label' => 'Auto-create job card on confirm',
                    'description' => 'Create a production job card when a sales order is confirmed.',
                    'type' => 'boolean',
                    'scopes' => ['company', 'branch'],
                    'default' => false,
                ],
                'sales_order_require_deposit' => [
                    'label' => 'Require deposit on confirm',
                    'description' => 'A deposit must be recorded before confirming a sales order.',
                    'type' => 'boolean',
                    'scopes' => ['company'],
                    'default' => false,
                ],
            ],
        ],

        'production' => [
            'label' => 'Production Settings',
            'description' => 'Job card and shop floor defaults.',
            'settings' => [
                'production_default_priority' => [
                    'label' => 'Default job priority',
                    'description' => 'Default priority for new job cards (normal, high, urgent).',
                    'type' => 'string',
                    'scopes' => ['company', 'branch'],
                    'default' => 'normal',
                ],
                'production_auto_schedule_on_create' => [
                    'label' => 'Auto-schedule on create',
                    'description' => 'Place new job cards on the production queue automatically.',
                    'type' => 'boolean',
                    'scopes' => ['company', 'branch'],
                    'default' => false,
                ],
                'production_qc_required' => [
                    'label' => 'Quality check required',
                    'description' => 'A passed quality check is required before completing a job card.',
                    'type' => 'boolean',
                    'scopes' => ['company'],
                    'default' => true,
                ],
            ],
        ],

        'inventory' => [
            'label' => 'Inventory Settings',
            'description' => 'Stock control and warehouse defaults.',
            'settings' => [
                'inventory_allow_negative_stock' => [
                    'label' => 'Allow negative stock',
                    'description' => 'Permit stock issues that would result in negative balances.',
                    'type' => 'boolean',
                    'scopes' => ['company', 'branch'],
                    'default' => false,
                ],
                'inventory_reorder_alert_enabled' => [
                    'label' => 'Reorder alerts enabled',
                    'description' => 'Generate alerts when stock falls below reorder level.',
                    'type' => 'boolean',
                    'scopes' => ['company', 'branch'],
                    'default' => true,
                ],
                'inventory_default_receipt_source' => [
                    'label' => 'Default receipt source',
                    'description' => 'Default source type for new stock receipts (purchase, return, adjustment).',
                    'type' => 'string',
                    'scopes' => ['company'],
                    'default' => 'purchase',
                ],
            ],
        ],

    ],

];
