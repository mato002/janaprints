<?php

/**
 * HR workspace hub and section catalogs (presentation only).
 *
 * Attendance / Leave / Recruitment already use in-page tabs.
 * Analytics live under Reports & Intelligence.
 */
return [

    'hub' => [
        [
            'label' => 'People',
            'description' => 'Employees, attendance, leave, and recruitment.',
            'route' => 'admin.workspaces.hr.section',
            'route_params' => ['section' => 'people'],
            'permission' => 'employees.manage|hr.attendance.view|hr.leave.view|hr.recruitment.view',
            'icon' => 'identification',
            'active_routes' => [
                'admin.workspaces.hr.section:people',
                'admin.employees.*',
                'admin.hr.employees.*',
                'admin.hr.attendance.*',
                'admin.hr.leave.*',
                'admin.hr.shifts.*',
                'admin.hr.recruitment.*',
            ],
        ],
        [
            'label' => 'Payroll',
            'description' => 'Pay runs and compensation setup.',
            'route' => 'admin.workspaces.hr.section',
            'route_params' => ['section' => 'payroll'],
            'permission' => 'hr.payroll.view|hr.compensation.view',
            'icon' => 'cash',
            'active_routes' => ['admin.workspaces.hr.section:payroll', 'admin.hr.payroll.*', 'admin.hr.compensation.*'],
        ],
        [
            'label' => 'Development',
            'description' => 'Performance and training.',
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
    ],

    'sections' => [
        'people' => [
            'title' => 'People',
            'description' => 'Employee records and workforce desks.',
            'icon' => 'identification',
            'groups' => [[
                'label' => 'People',
                'items' => [
                    ['key' => 'employees', 'label' => 'Employees', 'description' => 'Employee records linked to user accounts.', 'route' => 'admin.employees.index', 'permission' => 'employees.manage', 'icon' => 'identification', 'active_routes' => ['admin.employees.*', 'admin.hr.dashboard']],
                    ['key' => 'attendance', 'label' => 'Attendance', 'description' => 'Time tracking and shift records.', 'route' => 'admin.hr.attendance.dashboard', 'permission' => 'hr.attendance.view', 'icon' => 'clock', 'active_routes' => ['admin.hr.attendance.*', 'admin.hr.shifts.*']],
                    ['key' => 'leave', 'label' => 'Leave', 'description' => 'Leave requests, balances, calendar, and setup.', 'route' => 'admin.hr.leave.dashboard', 'permission' => 'hr.leave.view', 'icon' => 'calendar', 'active_routes' => ['admin.hr.leave.dashboard', 'admin.hr.leave.index', 'admin.hr.leave.create', 'admin.hr.leave.show', 'admin.hr.leave.calendar', 'admin.hr.leave.balances', 'admin.hr.leave.config', 'admin.hr.leave.config.*']],
                    ['key' => 'recruitment', 'label' => 'Recruitment', 'description' => 'Vacancies, applications, offers, and onboarding.', 'route' => 'admin.hr.recruitment.dashboard', 'permission' => 'hr.recruitment.view', 'icon' => 'users', 'active_routes' => ['admin.hr.recruitment.*']],
                ],
            ]],
        ],
        'payroll' => [
            'title' => 'Payroll',
            'description' => 'Pay runs and compensation.',
            'icon' => 'cash',
            'groups' => [
                [
                    'label' => 'Payroll',
                    'items' => [
                        [
                            'key' => 'payroll-runs',
                            'label' => 'Pay runs',
                            'description' => 'Create and manage payroll runs and payslips.',
                            'route' => 'admin.hr.payroll.dashboard',
                            'permission' => 'hr.payroll.view',
                            'icon' => 'cash',
                            'active_routes' => ['admin.hr.payroll.dashboard', 'admin.hr.payroll.index', 'admin.hr.payroll.create', 'admin.hr.payroll.show', 'admin.hr.payroll.*'],
                        ],
                        [
                            'key' => 'compensation',
                            'label' => 'Salaries',
                            'description' => 'View and assign employee salaries.',
                            'route' => 'admin.hr.compensation.register',
                            'permission' => 'hr.compensation.view',
                            'icon' => 'table-cells',
                            'active_routes' => ['admin.hr.compensation.dashboard', 'admin.hr.compensation.register', 'admin.hr.compensation.edit', 'admin.hr.compensation.create'],
                        ],
                    ],
                ],
                [
                    'label' => 'Setup',
                    'items' => [
                        ['key' => 'payroll-classes', 'label' => 'Classes', 'description' => 'Salary bands for onboarding.', 'route' => 'admin.hr.compensation.templates', 'permission' => 'hr.compensation.view', 'icon' => 'collection', 'active_routes' => ['admin.hr.compensation.templates', 'admin.hr.compensation.templates.*']],
                        ['key' => 'payroll-groups', 'label' => 'Pay groups', 'description' => 'Payroll run groups such as Main or Casual.', 'route' => 'admin.hr.compensation.payroll-groups', 'permission' => 'hr.compensation.view', 'icon' => 'users', 'active_routes' => ['admin.hr.compensation.payroll-groups', 'admin.hr.compensation.payroll-groups.*']],
                        ['key' => 'allowances', 'label' => 'Allowances', 'description' => 'Reusable allowance types.', 'route' => 'admin.hr.compensation.allowances', 'permission' => 'hr.compensation.view', 'icon' => 'plus-circle', 'active_routes' => ['admin.hr.compensation.allowances', 'admin.hr.compensation.allowances.*']],
                        ['key' => 'deductions', 'label' => 'Deductions', 'description' => 'Statutory and custom deductions.', 'route' => 'admin.hr.compensation.deductions', 'permission' => 'hr.compensation.view', 'icon' => 'minus-circle', 'active_routes' => ['admin.hr.compensation.deductions', 'admin.hr.compensation.deductions.*']],
                    ],
                ],
            ],
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
    ],
];
