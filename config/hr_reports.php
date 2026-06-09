<?php

/**
 * HR Reports (HR-C4) — workforce analytics catalog.
 */

return [

    'title' => 'HR Reports',
    'description' => 'Workforce, attendance, leave, payroll, and compliance analytics.',

    'tabs' => [
        'attendance' => [
            'label' => 'Attendance',
            'reports' => [
                'attendance_summary' => 'Attendance Report',
                'late_arrivals' => 'Late Arrivals Report',
                'absenteeism' => 'Absenteeism Report',
                'overtime' => 'Overtime Report',
            ],
        ],
        'leave' => [
            'label' => 'Leave',
            'reports' => [
                'leave_utilization' => 'Leave Utilization Report',
            ],
        ],
        'payroll' => [
            'label' => 'Payroll',
            'reports' => [
                'payroll_cost' => 'Payroll Cost Report',
            ],
        ],
        'workforce' => [
            'label' => 'Workforce',
            'reports' => [
                'department_headcount' => 'Department Headcount Report',
                'employee_movement' => 'Employee Movement Report',
                'contract_expiry' => 'Contract Expiry Report',
                'training_compliance' => 'Training Compliance Report',
            ],
        ],
    ],

];
