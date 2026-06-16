<?php

return [
    'hub' => [
        [
            'label' => 'People',
            'description' => 'Employees, attendance, leave, and workforce records.',
            'route' => 'admin.workspaces.hr.section',
            'route_params' => ['section' => 'people'],
            'permission' => 'employees.manage|hr.attendance.view|hr.leave.view',
            'icon' => 'identification',
            'active_routes' => ['admin.workspaces.hr.section:people', 'admin.employees.*', 'admin.hr.attendance.*', 'admin.hr.leave.*', 'admin.hr.shifts.*'],
        ],
        [
            'label' => 'Payroll',
            'description' => 'Pay runs, payslips, and compensation.',
            'route' => 'admin.workspaces.hr.section',
            'route_params' => ['section' => 'payroll'],
            'permission' => 'hr.payroll.view',
            'icon' => 'cash',
            'active_routes' => ['admin.workspaces.hr.section:payroll', 'admin.hr.payroll.*'],
        ],
        [
            'label' => 'Development',
            'description' => 'Performance, training, and employee growth.',
            'route' => 'admin.workspaces.hr.section',
            'route_params' => ['section' => 'development'],
            'permission' => 'hr.performance.view|hr.training.view',
            'icon' => 'badge-check',
            'active_routes' => ['admin.workspaces.hr.section:development', 'admin.hr.performance.*', 'admin.hr.training.*'],
        ],
        [
            'label' => 'Records',
            'description' => 'HR documents and exit management.',
            'route' => 'admin.workspaces.hr.section',
            'route_params' => ['section' => 'records'],
            'permission' => 'hr.documents.view|hr.exit.view',
            'icon' => 'document-text',
            'active_routes' => ['admin.workspaces.hr.section:records', 'admin.hr.documents.*', 'admin.hr.exit.*'],
        ],
        [
            'label' => 'Reports',
            'description' => 'HR analytics and workforce reporting.',
            'route' => 'admin.workspaces.hr.section',
            'route_params' => ['section' => 'reports'],
            'permission' => 'reports.view|hr.dashboard.view',
            'icon' => 'chart-pie',
            'active_routes' => ['admin.workspaces.hr.section:reports', 'admin.reports.hr'],
        ],
    ],

    'sections' => [
        'people' => [
            'title' => 'People',
            'description' => 'Employee records, attendance, and leave.',
            'icon' => 'identification',
            'groups' => [[
                'label' => 'People',
                'items' => [
                    ['key' => 'dashboard', 'label' => 'HR Dashboard', 'description' => 'Workforce metrics and HR overview.', 'route' => 'admin.hr.dashboard', 'permission' => 'hr.dashboard.view', 'icon' => 'chart-pie', 'active_routes' => ['admin.hr.dashboard']],
                    ['key' => 'employees', 'label' => 'Employees', 'description' => 'Employee records linked to user accounts.', 'route' => 'admin.employees.index', 'permission' => 'employees.manage', 'icon' => 'identification', 'active_routes' => ['admin.employees.*']],
                    ['key' => 'attendance', 'label' => 'Attendance', 'description' => 'Time tracking and shift records.', 'route' => 'admin.hr.attendance.dashboard', 'permission' => 'hr.attendance.view', 'icon' => 'clock', 'active_routes' => ['admin.hr.attendance.*', 'admin.hr.shifts.*']],
                    ['key' => 'leave', 'label' => 'Leave', 'description' => 'Leave requests and balances.', 'route' => 'admin.hr.leave.dashboard', 'permission' => 'hr.leave.view', 'icon' => 'calendar', 'active_routes' => ['admin.hr.leave.*']],
                ],
            ]],
        ],
        'payroll' => [
            'title' => 'Payroll',
            'description' => 'Pay runs, payslips, and deductions.',
            'icon' => 'cash',
            'groups' => [[
                'label' => 'Payroll',
                'items' => [
                    ['key' => 'payroll-dashboard', 'label' => 'Payroll', 'description' => 'Pay runs, payslips, and deductions.', 'route' => 'admin.hr.payroll.dashboard', 'permission' => 'hr.payroll.view', 'icon' => 'cash', 'active_routes' => ['admin.hr.payroll.*']],
                ],
            ]],
        ],
        'development' => [
            'title' => 'Development',
            'description' => 'Performance reviews and training.',
            'icon' => 'badge-check',
            'groups' => [[
                'label' => 'Development',
                'items' => [
                    ['key' => 'performance', 'label' => 'Performance', 'description' => 'Reviews, goals, and appraisals.', 'route' => 'admin.hr.performance.dashboard', 'permission' => 'hr.performance.view', 'icon' => 'badge-check', 'active_routes' => ['admin.hr.performance.*']],
                    ['key' => 'training', 'label' => 'Training', 'description' => 'Courses, certifications, and development.', 'route' => 'admin.hr.training.dashboard', 'permission' => 'hr.training.view', 'icon' => 'book-open', 'active_routes' => ['admin.hr.training.*']],
                ],
            ]],
        ],
        'records' => [
            'title' => 'Records',
            'description' => 'HR documents and exit workflows.',
            'icon' => 'document-text',
            'groups' => [[
                'label' => 'Records',
                'items' => [
                    ['key' => 'documents', 'label' => 'Documents', 'description' => 'HR document repository.', 'route' => 'admin.hr.documents.dashboard', 'permission' => 'hr.documents.view', 'icon' => 'document-text', 'active_routes' => ['admin.hr.documents.*']],
                    ['key' => 'exit', 'label' => 'Exit Management', 'description' => 'Offboarding and clearance workflows.', 'route' => 'admin.hr.exit.dashboard', 'permission' => 'hr.exit.view', 'icon' => 'switch-horizontal', 'active_routes' => ['admin.hr.exit.*']],
                ],
            ]],
        ],
        'reports' => [
            'title' => 'Reports',
            'description' => 'HR analytics and reporting.',
            'icon' => 'chart-pie',
            'groups' => [[
                'label' => 'Reports',
                'items' => [
                    ['key' => 'hr-reports', 'label' => 'HR Reports', 'description' => 'Workforce and payroll analytics.', 'route' => 'admin.reports.hr', 'permission' => 'reports.view', 'icon' => 'chart-bar', 'active_routes' => ['admin.reports.hr']],
                ],
            ]],
        ],
    ],
];
