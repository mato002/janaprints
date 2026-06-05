<?php

/**
 * Administration workspace hub and section catalogs (presentation only).
 * Root hub shows six workspace cards; features live on section pages.
 */
return [

    'hub' => [
        [
            'label' => 'Security & Access',
            'description' => 'Identity, authentication, authorization and security controls.',
            'route' => 'admin.workspaces.administration.section',
            'route_params' => ['section' => 'security-access'],
            'permission' => 'users.view|roles.view',
            'icon' => 'shield-check',
            'active_routes' => ['admin.workspaces.administration.section:security-access', 'admin.users.*', 'admin.access-control.*', 'admin.roles.*', 'admin.permissions.*'],
        ],
        [
            'label' => 'Organization',
            'description' => 'Organizational structure and workforce hierarchy.',
            'route' => 'admin.workspaces.administration.section',
            'route_params' => ['section' => 'organization'],
            'permission' => 'companies.manage|branches.manage|departments.manage|employees.manage',
            'icon' => 'building',
            'active_routes' => ['admin.workspaces.administration.section:organization', 'admin.companies.*', 'admin.branches.*', 'admin.departments.*', 'admin.employees.*'],
        ],
        [
            'label' => 'Configuration',
            'description' => 'ERP-wide configuration and behavioral settings.',
            'route' => 'admin.workspaces.administration.section',
            'route_params' => ['section' => 'configuration'],
            'permission' => 'settings.view',
            'icon' => 'cog',
            'active_routes' => ['admin.workspaces.administration.section:configuration', 'admin.settings.index', 'admin.settings.show', 'admin.settings.update', 'admin.settings.branding.*', 'admin.settings.numbering.*', 'admin.settings.forms.*'],
        ],
        [
            'label' => 'Workflow & Governance',
            'description' => 'Business governance and workflow automation.',
            'route' => 'admin.workspaces.administration.section',
            'route_params' => ['section' => 'workflow-governance'],
            'permission' => 'settings.view',
            'icon' => 'badge-check',
            'active_routes' => ['admin.workspaces.administration.section:workflow-governance', 'admin.settings.approvals.*'],
        ],
        [
            'label' => 'Integrations',
            'description' => 'External system connectivity and communication channels.',
            'route' => 'admin.workspaces.administration.section',
            'route_params' => ['section' => 'integrations'],
            'permission' => 'integrations.view|integrations.manage',
            'icon' => 'switch-horizontal',
            'active_routes' => ['admin.workspaces.administration.section:integrations', 'admin.integrations.*'],
        ],
        [
            'label' => 'System Operations',
            'description' => 'Monitoring, maintenance and operational controls.',
            'route' => 'admin.workspaces.administration.section',
            'route_params' => ['section' => 'system-operations'],
            'permission' => 'activity_logs.view',
            'icon' => 'chip',
            'active_routes' => ['admin.workspaces.administration.section:system-operations', 'admin.activity-logs.*'],
        ],
    ],

    'sections' => [

        'security-access' => [
            'title' => 'Security & Access',
            'description' => 'Identity, authentication, authorization and security controls.',
            'icon' => 'shield-check',
            'groups' => [
                [
                    'label' => 'Security & Access',
                    'items' => [
                        ['label' => 'Users', 'description' => 'User accounts, branches, and role assignment.', 'route' => 'admin.users.index', 'permission' => 'users.view', 'icon' => 'users', 'active_routes' => ['admin.users.*']],
                        ['label' => 'Roles', 'description' => 'Security groups and role governance.', 'route' => 'admin.access-control.roles', 'permission' => 'roles.view', 'icon' => 'shield-check', 'active_routes' => ['admin.access-control.roles', 'admin.roles.*']],
                        ['label' => 'Permissions', 'description' => 'Permission matrix and access rights.', 'route' => 'admin.access-control.matrix', 'permission' => 'roles.view', 'icon' => 'key', 'active_routes' => ['admin.access-control.matrix', 'admin.permissions.*', 'admin.roles.permissions.*']],
                        ['label' => 'User Sessions', 'description' => 'Active sessions and sign-in activity.', 'coming_soon' => true, 'icon' => 'clock'],
                        ['label' => 'Access Audit', 'description' => 'Authentication and authorization audit trail.', 'coming_soon' => true, 'icon' => 'document-text'],
                    ],
                ],
            ],
        ],

        'organization' => [
            'title' => 'Organization',
            'description' => 'Organizational structure and workforce hierarchy.',
            'icon' => 'building',
            'groups' => [
                [
                    'label' => 'Organization',
                    'items' => [
                        ['label' => 'Companies', 'description' => 'Legal entities and tenant companies.', 'route' => 'admin.companies.index', 'permission' => 'companies.manage', 'icon' => 'building', 'active_routes' => ['admin.companies.*']],
                        ['label' => 'Branches', 'description' => 'Branch locations and defaults.', 'route' => 'admin.branches.index', 'permission' => 'branches.manage', 'icon' => 'location-marker', 'active_routes' => ['admin.branches.*']],
                        ['label' => 'Departments', 'description' => 'Organizational units and hierarchy.', 'route' => 'admin.departments.index', 'permission' => 'departments.manage', 'icon' => 'view-grid', 'active_routes' => ['admin.departments.*']],
                        ['label' => 'Employees', 'description' => 'Employee master records.', 'route' => 'admin.employees.index', 'permission' => 'employees.manage', 'icon' => 'identification', 'active_routes' => ['admin.employees.*']],
                        ['label' => 'Job Titles', 'description' => 'Position titles and reporting structure.', 'coming_soon' => true, 'icon' => 'badge-check'],
                    ],
                ],
            ],
        ],

        'configuration' => [
            'title' => 'Configuration',
            'description' => 'ERP-wide configuration and behavioral settings.',
            'icon' => 'cog',
            'groups' => [
                [
                    'label' => 'Configuration',
                    'items' => [
                        ['label' => 'System Settings', 'description' => 'Company-wide configuration and preferences.', 'route' => 'admin.settings.index', 'permission' => 'settings.view', 'icon' => 'cog', 'active_routes' => ['admin.settings.index', 'admin.settings.show', 'admin.settings.update', 'admin.settings.branding.*']],
                        ['label' => 'Number Series', 'description' => 'Document sequences, prefixes, and numbering rules.', 'route' => 'admin.settings.numbering.index', 'permission' => 'settings.view', 'icon' => 'template', 'active_routes' => ['admin.settings.numbering.*']],
                        ['label' => 'Form Controls', 'description' => 'Required fields and form visibility rules.', 'route' => 'admin.settings.forms.index', 'permission' => 'settings.view', 'icon' => 'clipboard-list', 'active_routes' => ['admin.settings.forms.*']],
                        ['label' => 'Master Data', 'description' => 'Shared reference data and lookup values.', 'coming_soon' => true, 'icon' => 'template'],
                        ['label' => 'Document Types', 'description' => 'Document classification and numbering profiles.', 'coming_soon' => true, 'icon' => 'document-text'],
                    ],
                ],
            ],
        ],

        'workflow-governance' => [
            'title' => 'Workflow & Governance',
            'description' => 'Business governance and workflow automation.',
            'icon' => 'badge-check',
            'groups' => [
                [
                    'label' => 'Workflow & Governance',
                    'items' => [
                        ['label' => 'Approval Rules', 'description' => 'Discount, credit, and workflow approvals.', 'route' => 'admin.settings.approvals.index', 'permission' => 'settings.view', 'icon' => 'badge-check', 'active_routes' => ['admin.settings.approvals.*']],
                        ['label' => 'Approval Chains', 'description' => 'Multi-step approval sequences and sign-off paths.', 'coming_soon' => true, 'icon' => 'switch-horizontal'],
                        ['label' => 'Workflow Rules', 'description' => 'Automated business process triggers and actions.', 'coming_soon' => true, 'icon' => 'cog'],
                        ['label' => 'Escalations', 'description' => 'Timeout rules and escalation routing.', 'coming_soon' => true, 'icon' => 'exclamation'],
                        ['label' => 'Delegations', 'description' => 'Temporary approval authority and substitutes.', 'coming_soon' => true, 'icon' => 'users'],
                    ],
                ],
            ],
        ],

        'integrations' => [
            'title' => 'Integrations',
            'description' => 'External system connectivity and communication channels.',
            'icon' => 'switch-horizontal',
            'groups' => [
                [
                    'label' => 'Integrations',
                    'items' => [
                        ['label' => 'Email Settings', 'description' => 'SMTP, delivery, and outbound email configuration.', 'route' => 'admin.integrations.email.index', 'permission' => 'integrations.view|integrations.email.manage', 'icon' => 'inbox', 'active_routes' => ['admin.integrations.email.*']],
                        ['label' => 'SMS Settings', 'description' => 'SMS provider credentials and routing.', 'route' => 'admin.integrations.sms.index', 'permission' => 'integrations.view|integrations.sms.manage', 'icon' => 'inbox', 'active_routes' => ['admin.integrations.sms.*']],
                        ['label' => 'API Keys', 'description' => 'Developer keys and programmatic access.', 'route' => 'admin.integrations.api-keys.index', 'permission' => 'integrations.view|integrations.api.manage', 'icon' => 'key', 'active_routes' => ['admin.integrations.api-keys.*']],
                        ['label' => 'Webhooks', 'description' => 'Outbound event subscriptions and callbacks.', 'route' => 'admin.integrations.webhooks.index', 'permission' => 'integrations.view|integrations.webhooks.manage', 'icon' => 'switch-horizontal', 'active_routes' => ['admin.integrations.webhooks.*']],
                        ['label' => 'Third Party Integrations', 'description' => 'Connectors for external business systems.', 'route' => 'admin.integrations.providers.index', 'permission' => 'integrations.view|integrations.providers.manage', 'icon' => 'switch-horizontal', 'active_routes' => ['admin.integrations.providers.*']],
                    ],
                ],
            ],
        ],

        'system-operations' => [
            'title' => 'System Operations',
            'description' => 'Monitoring, maintenance and operational controls.',
            'icon' => 'chip',
            'groups' => [
                [
                    'label' => 'System Operations',
                    'items' => [
                        ['label' => 'Audit Logs', 'description' => 'Compliance-grade audit records.', 'coming_soon' => true, 'icon' => 'document-text'],
                        ['label' => 'Activity Logs', 'description' => 'User actions and system activity trail.', 'route' => 'admin.activity-logs.index', 'permission' => 'activity_logs.view', 'icon' => 'clock', 'active_routes' => ['admin.activity-logs.*']],
                        ['label' => 'Background Jobs', 'description' => 'Queued tasks and scheduled job monitoring.', 'coming_soon' => true, 'icon' => 'switch-horizontal'],
                        ['label' => 'System Health', 'description' => 'Service status, queues, and performance metrics.', 'coming_soon' => true, 'icon' => 'chip'],
                        ['label' => 'Backups', 'description' => 'Database and file backup schedules.', 'coming_soon' => true, 'icon' => 'archive'],
                        ['label' => 'Data Retention', 'description' => 'Archive policies and purge rules.', 'coming_soon' => true, 'icon' => 'archive'],
                    ],
                ],
            ],
        ],

    ],

];
