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
            'active_routes' => ['admin.workspaces.administration.section:security-access', 'admin.users.*', 'admin.access-control.*', 'admin.roles.*', 'admin.permissions.*', 'admin.security.sessions.*', 'admin.security.audit.*'],
        ],
        [
            'label' => 'Organization',
            'description' => 'Organizational structure and workforce hierarchy.',
            'route' => 'admin.workspaces.administration.section',
            'route_params' => ['section' => 'organization'],
            'permission' => 'companies.manage|branches.manage|departments.manage|employees.manage|organization.job_titles.view',
            'icon' => 'building',
            'active_routes' => ['admin.workspaces.administration.section:organization', 'admin.companies.*', 'admin.branches.*', 'admin.departments.*', 'admin.employees.*', 'admin.job-titles.*'],
        ],
        [
            'label' => 'Configuration',
            'description' => 'ERP-wide configuration and behavioral settings.',
            'route' => 'admin.workspaces.administration.section',
            'route_params' => ['section' => 'configuration'],
            'permission' => 'settings.view',
            'icon' => 'cog',
            'active_routes' => ['admin.workspaces.administration.section:configuration', 'admin.settings.index', 'admin.settings.show', 'admin.settings.update', 'admin.settings.branding.*', 'admin.settings.numbering.*', 'admin.settings.forms.*', 'admin.master-data.*'],
        ],
        [
            'label' => 'Workflow & Governance',
            'description' => 'Business governance and workflow automation.',
            'route' => 'admin.workspaces.administration.section',
            'route_params' => ['section' => 'workflow-governance'],
            'permission' => 'settings.view',
            'icon' => 'badge-check',
            'active_routes' => ['admin.workspaces.administration.section:workflow-governance', 'admin.settings.approvals.*', 'admin.governance.chains.*', 'admin.governance.delegations.*', 'admin.governance.escalations.*', 'admin.governance.workflow-rules.*'],
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
            'permission' => 'activity_logs.view|operations.health.view|operations.jobs.view|operations.audit.view|operations.backups.view|operations.retention.view',
            'icon' => 'chip',
            'active_routes' => ['admin.workspaces.administration.section:system-operations', 'admin.activity-logs.*', 'admin.operations.health.*', 'admin.operations.jobs.*', 'admin.operations.audit.*', 'admin.operations.backups.*', 'admin.operations.retention.*'],
        ],
        [
            'label' => 'Website Content',
            'description' => 'Public storefront gallery and marketing content.',
            'route' => 'admin.workspaces.administration.section',
            'route_params' => ['section' => 'website-content'],
            'permission' => 'website.gallery.view',
            'icon' => 'photograph',
            'active_routes' => ['admin.workspaces.administration.section:website-content', 'admin.website.gallery.*'],
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
                        ['key' => 'users', 'label' => 'Users', 'description' => 'User accounts, branches, and role assignment.', 'route' => 'admin.users.index', 'permission' => 'users.view', 'icon' => 'users', 'keywords' => ['user', 'accounts', 'identity'], 'active_routes' => ['admin.users.*']],
                        ['key' => 'roles', 'label' => 'Roles', 'description' => 'Security groups and role governance.', 'route' => 'admin.access-control.roles', 'permission' => 'roles.view', 'icon' => 'shield-check', 'active_routes' => ['admin.access-control.roles', 'admin.roles.*']],
                        ['key' => 'permissions', 'label' => 'Permissions', 'description' => 'Permission matrix and access rights.', 'route' => 'admin.access-control.matrix', 'permission' => 'roles.view', 'icon' => 'key', 'active_routes' => ['admin.access-control.matrix', 'admin.permissions.*', 'admin.roles.permissions.*']],
                        ['key' => 'user-sessions', 'label' => 'User Sessions', 'description' => 'Active sessions and sign-in activity.', 'route' => 'admin.security.sessions.index', 'permission' => 'security.sessions.view', 'icon' => 'clock', 'active_routes' => ['admin.security.sessions.*']],
                        ['key' => 'access-audit', 'label' => 'Access Audit', 'description' => 'Authentication and authorization audit trail.', 'route' => 'admin.security.audit.index', 'permission' => 'security.audit.view', 'icon' => 'document-text', 'active_routes' => ['admin.security.audit.*']],
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
                        ['label' => 'Job Titles', 'description' => 'Position titles and reporting structure.', 'route' => 'admin.job-titles.index', 'permission' => 'organization.job_titles.view', 'icon' => 'badge-check', 'active_routes' => ['admin.job-titles.*']],
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
                        ['key' => 'system-settings', 'label' => 'System Settings', 'description' => 'Company-wide configuration and preferences.', 'route' => 'admin.settings.show', 'route_params' => ['section' => 'hub'], 'permission' => 'settings.view', 'icon' => 'cog', 'keywords' => ['settings', 'configuration', 'preferences', 'hub'], 'active_routes' => ['admin.settings.index', 'admin.settings.show', 'admin.settings.update', 'admin.settings.branding.*']],
                        ['key' => 'number-series', 'label' => 'Number Series', 'description' => 'Document sequences, prefixes, and numbering rules.', 'route' => 'admin.settings.numbering.index', 'permission' => 'settings.view', 'icon' => 'template', 'keywords' => ['numbering', 'sequence', 'prefix'], 'active_routes' => ['admin.settings.numbering.*']],
                        ['key' => 'form-controls', 'label' => 'Form Controls', 'description' => 'Required fields and form visibility rules.', 'route' => 'admin.settings.forms.index', 'permission' => 'settings.view', 'icon' => 'clipboard-list', 'keywords' => ['forms', 'fields', 'controls'], 'active_routes' => ['admin.settings.forms.*']],
                        ['key' => 'master-data', 'label' => 'Master Data', 'description' => 'Shared reference data and lookup values.', 'route' => 'admin.master-data.index', 'permission' => 'configuration.master_data.view', 'icon' => 'template', 'active_routes' => ['admin.master-data.*']],
                        ['key' => 'document-types', 'label' => 'Document Types', 'description' => 'Document classification and numbering profiles.', 'route' => 'admin.settings.document-types.index', 'permission' => 'configuration.document_types.view', 'icon' => 'document-text', 'active_routes' => ['admin.settings.document-types.*']],
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
                        ['key' => 'approval-rules', 'label' => 'Approval Rules', 'description' => 'Discount, credit, and workflow approvals.', 'route' => 'admin.settings.approvals.index', 'permission' => 'settings.view', 'icon' => 'badge-check', 'keywords' => ['approval', 'approvals'], 'active_routes' => ['admin.settings.approvals.*']],
                        ['key' => 'approval-chains', 'label' => 'Approval Chains', 'description' => 'Multi-step approval sequences and sign-off paths.', 'route' => 'admin.governance.chains.index', 'permission' => 'governance.chains.view', 'icon' => 'switch-horizontal', 'active_routes' => ['admin.governance.chains.*']],
                        ['key' => 'workflow-rules', 'label' => 'Workflow Rules', 'description' => 'Automated business process triggers and actions.', 'route' => 'admin.governance.workflow-rules.index', 'permission' => 'governance.workflow.view', 'icon' => 'cog', 'active_routes' => ['admin.governance.workflow-rules.*']],
                        ['key' => 'escalations', 'label' => 'Escalations', 'description' => 'Timeout rules and escalation routing.', 'route' => 'admin.governance.escalations.index', 'permission' => 'governance.escalations.view', 'icon' => 'exclamation', 'active_routes' => ['admin.governance.escalations.*']],
                        ['key' => 'delegations', 'label' => 'Delegations', 'description' => 'Temporary approval authority and substitutes.', 'route' => 'admin.governance.delegations.index', 'permission' => 'governance.delegations.view', 'icon' => 'users', 'active_routes' => ['admin.governance.delegations.*']],
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
                        ['label' => 'Audit Logs', 'description' => 'Compliance-grade audit records.', 'route' => 'admin.operations.audit.index', 'permission' => 'operations.audit.view', 'icon' => 'document-text', 'active_routes' => ['admin.operations.audit.*']],
                        ['label' => 'Activity Logs', 'description' => 'User actions and system activity trail.', 'route' => 'admin.activity-logs.index', 'permission' => 'activity_logs.view', 'icon' => 'clock', 'active_routes' => ['admin.activity-logs.*']],
                        ['label' => 'Background Jobs', 'description' => 'Queued tasks and scheduled job monitoring.', 'route' => 'admin.operations.jobs.index', 'permission' => 'operations.jobs.view', 'icon' => 'switch-horizontal', 'active_routes' => ['admin.operations.jobs.*']],
                        ['label' => 'System Health', 'description' => 'Service status, queues, and performance metrics.', 'route' => 'admin.operations.health.index', 'permission' => 'operations.health.view', 'icon' => 'chip', 'active_routes' => ['admin.operations.health.*']],
                        ['label' => 'Backups', 'description' => 'Database and file backup schedules.', 'route' => 'admin.operations.backups.index', 'permission' => 'operations.backups.view', 'icon' => 'archive', 'active_routes' => ['admin.operations.backups.*']],
                        ['label' => 'Data Retention', 'description' => 'Archive policies and purge rules.', 'route' => 'admin.operations.retention.index', 'permission' => 'operations.retention.view', 'icon' => 'archive', 'active_routes' => ['admin.operations.retention.*']],
                    ],
                ],
            ],
        ],

        'website-content' => [
            'title' => 'Website Content',
            'description' => 'Public storefront gallery and marketing content.',
            'icon' => 'photograph',
            'groups' => [
                [
                    'label' => 'Public Website',
                    'items' => [
                        ['label' => 'Gallery', 'description' => 'Manage portfolio images and project showcases on the public site.', 'route' => 'admin.website.gallery.index', 'permission' => 'website.gallery.view', 'icon' => 'photograph', 'active_routes' => ['admin.website.gallery.*']],
                    ],
                ],
            ],
        ],

    ],

];
