<?php

/**
 * Department ownership and default role boundaries (documentation + onboarding reference).
 * Authorization remains in RolesAndPermissionsSeeder and policies.
 */
return [

    'departments' => [

        'sales' => [
            'label' => 'Sales',
            'role' => 'Sales',
            'operator_desk' => 'admin.sales.desk',
            'owns' => [
                'crm.customers',
                'crm.leads',
                'quotations',
                'artwork',
                'sales_orders',
                'sales_orders.production',
            ],
            'must_not' => [
                'invoices.create',
                'invoices.post',
                'payments.create',
                'payments.post',
                'dispatch.dispatch',
                'production.start',
            ],
            'handoff' => 'Release to Production creates a job card for Production.',
        ],

        'production' => [
            'label' => 'Production',
            'role' => 'Production',
            'operator_desk' => 'admin.production.floor',
            'owns' => [
                'production.view',
                'production.start',
                'production.complete',
                'production.qc',
                'machines.assign',
            ],
            'must_not' => [
                'sales_orders.production',
                'invoices',
                'payments',
                'quotations.edit',
                'crm.customers.edit',
                'dispatch.dispatch',
            ],
            'workflow' => ['Assign machine', 'Start', 'Print', 'Finish', 'QC', 'Ready for dispatch'],
        ],

        'finance' => [
            'label' => 'Finance / Accounts',
            'role' => 'Accountant',
            'workspace' => 'admin.workspaces.accounting',
            'owns' => [
                'invoices',
                'payments',
                'receivables',
                'payables',
                'tax',
                'accounting',
            ],
            'must_not' => [
                'sales_orders.production',
                'production.start',
                'dispatch.dispatch',
            ],
        ],

        'dispatch' => [
            'label' => 'Dispatch / Customer Service (outbound)',
            'role' => 'Dispatch',
            'operator_desk' => 'admin.dispatch.dashboard',
            'owns' => [
                'dispatch.view',
                'dispatch.create',
                'dispatch.dispatch',
                'dispatch.deliver',
            ],
            'must_not' => [
                'sales_orders.production',
                'production.start',
                'invoices.post',
                'payments.post',
            ],
            'workflow' => ['Ready', 'Package', 'Courier', 'Collect', 'Deliver', 'Proof of delivery'],
        ],

    ],

];
