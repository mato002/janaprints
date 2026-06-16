<?php

use App\Models\Artwork\ArtworkRequest;
use App\Models\Hr\PayrollRun;
use App\Models\Production\ProductionJobCard;
use App\Models\Sales\CustomerInvoice;
use App\Models\Sales\Quotation;
use App\Models\Sales\SalesOrder;

return [

    'entities' => [
        'quotation' => [
            'label' => 'Quotation',
            'module' => 'commercial',
            'model' => Quotation::class,
            'condition_fields' => ['total_amount', 'status', 'branch_id'],
        ],
        'sales_order' => [
            'label' => 'Sales Order',
            'module' => 'commercial',
            'model' => SalesOrder::class,
            'condition_fields' => ['total_amount', 'status', 'branch_id'],
        ],
        'artwork_request' => [
            'label' => 'Artwork Request',
            'module' => 'commercial',
            'model' => ArtworkRequest::class,
            'condition_fields' => ['status', 'branch_id'],
        ],
        'production_job_card' => [
            'label' => 'Production Job Card',
            'module' => 'production',
            'model' => ProductionJobCard::class,
            'condition_fields' => ['status', 'branch_id'],
        ],
        'customer_invoice' => [
            'label' => 'Customer Invoice',
            'module' => 'commercial',
            'model' => CustomerInvoice::class,
            'condition_fields' => ['total_amount', 'status', 'branch_id', 'due_date'],
        ],
        'payroll_run' => [
            'label' => 'Payroll Run',
            'module' => 'hr',
            'model' => PayrollRun::class,
            'condition_fields' => ['status', 'branch_id', 'net_total', 'employee_count'],
        ],
    ],

    'triggers' => [
        'created' => ['label' => 'Created'],
        'approved' => ['label' => 'Approved'],
        'rejected' => ['label' => 'Rejected'],
        'completed' => ['label' => 'Completed'],
        'cancelled' => ['label' => 'Cancelled'],
        'closed' => ['label' => 'Closed'],
    ],

    'actions' => [
        'create_document' => [
            'label' => 'Create Document',
            'config_fields' => ['target_entity'],
            'conversions' => [
                'quotation_to_sales_order' => [
                    'source' => 'quotation',
                    'target' => 'sales_order',
                    'trigger' => 'approved',
                ],
            ],
        ],
        'send_notification' => [
            'label' => 'Send Notification',
            'config_fields' => ['recipient_role', 'recipient_user_id', 'notification_type', 'title', 'body'],
        ],
        'send_email' => [
            'label' => 'Send Email',
            'config_fields' => ['recipient_email', 'subject', 'body'],
        ],
        'send_sms' => [
            'label' => 'Send SMS',
            'config_fields' => ['recipient_phone', 'message'],
        ],
        'assign_user' => [
            'label' => 'Assign User',
            'config_fields' => ['user_id', 'assignment_field'],
        ],
        'change_status' => [
            'label' => 'Change Status',
            'config_fields' => ['target_status'],
        ],
        'generate_task' => [
            'label' => 'Generate Task',
            'config_fields' => ['task_type', 'title', 'description', 'assigned_user_id'],
        ],
        'generate_approval' => [
            'label' => 'Generate Approval',
            'config_fields' => ['approval_rule_type'],
        ],
    ],

    'condition_operators' => [
        'equals' => ['label' => 'Equals'],
        'not_equals' => ['label' => 'Not Equals'],
        'gte' => ['label' => 'Greater Than or Equal'],
        'lte' => ['label' => 'Less Than or Equal'],
        'gt' => ['label' => 'Greater Than'],
        'lt' => ['label' => 'Less Than'],
        'in' => ['label' => 'In List'],
    ],

];
