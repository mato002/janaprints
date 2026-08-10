<?php

/**
 * Template category groups for workspace navigation and governance.
 */

return [
    'groups' => [
        'commercial' => [
            'label' => 'Sales',
            'categories' => [
                'quotation_ready',
                'quotation_approved',
                'quotation_rejected',
            ],
        ],
        'production' => [
            'label' => 'Production',
            'categories' => [
                'artwork_submitted',
                'artwork_approved',
                'artwork_rejected',
                'production_started',
                'production_completed',
                'ready_for_collection',
                'dispatch_started',
                'delivered',
            ],
        ],
        'finance' => [
            'label' => 'Finance',
            'categories' => [
                'invoice_generated',
                'invoice_overdue',
                'payment_received',
                'deposit_received',
                'supplier_bill_approved',
            ],
        ],
        'hr' => [
            'label' => 'HR',
            'categories' => [
                'employee_created',
                'leave_approved',
                'leave_rejected',
            ],
        ],
        'system' => [
            'label' => 'System',
            'categories' => [
                'password_reset',
                'otp_verification',
                'account_activated',
                'role_changed',
            ],
        ],
    ],
];
