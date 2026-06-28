<?php

/**
 * Production Reports (P3.6) — historical reporting catalog.
 * Tabular exports only; intelligence lives in Production 360 and Command Center.
 */

return [

    'title' => 'Production Reports',
    'description' => 'Period-based production performance — filter by date and branch, then review throughput, quality, materials, dispatch, and profitability.',

    'tabs' => [
        'throughput' => [
            'label' => 'Production Throughput',
            'reports' => [
                'jobs_completed' => 'Jobs Completed',
                'jobs_delayed' => 'Jobs Delayed',
                'jobs_cancelled' => 'Jobs Cancelled',
                'average_turnaround' => 'Average Turnaround',
                'department_throughput' => 'Department Throughput',
                'machine_utilization' => 'Machine Utilization',
            ],
        ],
        'quality' => [
            'label' => 'Quality Reports',
            'reports' => [
                'pass_rate' => 'Pass Rate',
                'fail_rate' => 'Fail Rate',
                'rework_rate' => 'Rework Rate',
                'hold_rate' => 'Hold Rate',
                'quality_fail_reasons' => 'Quality Fail Reasons',
                'rework_summary' => 'Rework Summary',
            ],
        ],
        'materials' => [
            'label' => 'Material Reports',
            'reports' => [
                'material_consumption' => 'Material Consumption',
                'material_cost' => 'Material Cost',
                'waste_analysis' => 'Waste Analysis',
                'production_material_usage' => 'Production Material Usage',
                'production_waste' => 'Production Waste',
                'material_variance' => 'Material Variance',
            ],
        ],
        'dispatch' => [
            'label' => 'Dispatch Reports',
            'reports' => [
                'ready_for_collection' => 'Ready For Collection',
                'collected_orders' => 'Collected Orders',
                'delivered_orders' => 'Delivered Orders',
                'outstanding_collections' => 'Outstanding Collections',
                'outstanding_deliveries' => 'Outstanding Deliveries',
                'delivered_jobs' => 'Delivered Jobs',
                'late_deliveries' => 'Late Deliveries',
                'delivery_success' => 'Delivery Success',
            ],
        ],
        'profitability' => [
            'label' => 'Profitability Reports',
            'reports' => [
                'job_profitability' => 'Job Profitability',
                'department_profitability' => 'Department Profitability',
                'customer_profitability' => 'Customer Profitability',
            ],
        ],
    ],

    'schedule_frequencies' => [
        'daily' => 'Daily',
        'weekly' => 'Weekly',
        'monthly' => 'Monthly',
    ],

];
