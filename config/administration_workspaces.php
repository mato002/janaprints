<?php

/**
 * Administration workspace hub and section catalogs (presentation only).
 * Flat business areas — not the full permission/engine taxonomy.
 */
return [

    'hub' => [
        [
            'label' => 'Security',
            'description' => 'Users, roles, and access audit.',
            'route' => 'admin.workspaces.administration.section',
            'route_params' => ['section' => 'security-access'],
            'permission' => 'users.view|roles.view',
            'icon' => 'shield-check',
            'active_routes' => [
                'admin.workspaces.administration.section:security-access',
                'admin.users.*',
                'admin.access-control.*',
                'admin.roles.*',
                'admin.permissions.*',
                'admin.security.sessions.*',
                'admin.security.audit.*',
            ],
        ],
        [
            'label' => 'Organization',
            'description' => 'Company, branches, departments, and teams.',
            'route' => 'admin.workspaces.administration.section',
            'route_params' => ['section' => 'organization'],
            'permission' => 'companies.manage|branches.manage|departments.manage|organization.job_titles.view',
            'icon' => 'building',
            'active_routes' => [
                'admin.workspaces.administration.section:organization',
                'admin.companies.*',
                'admin.branches.*',
                'admin.departments.*',
                'admin.job-titles.*',
            ],
        ],
        [
            'label' => 'Configuration',
            'description' => 'System settings, numbering, and defaults.',
            'route' => 'admin.workspaces.administration.section',
            'route_params' => ['section' => 'configuration'],
            'permission' => 'settings.view',
            'icon' => 'cog',
            'active_routes' => [
                'admin.workspaces.administration.section:configuration',
                'admin.settings.index',
                'admin.settings.show',
                'admin.settings.update',
                'admin.settings.branding.*',
                'admin.settings.numbering.*',
                'admin.settings.forms.*',
                'admin.master-data.*',
            ],
        ],
        [
            'label' => 'Operations',
            'description' => 'Workflows, integrations, and system operations.',
            'route' => 'admin.workspaces.administration.section',
            'route_params' => ['section' => 'operations'],
            'permission' => 'settings.view|integrations.view|integrations.manage|activity_logs.view|operations.health.view|operations.jobs.view|operations.audit.view|operations.backups.view|operations.retention.view',
            'icon' => 'chip',
            'active_routes' => [
                'admin.workspaces.administration.section:operations',
                'admin.workspaces.administration.section:workflow-governance',
                'admin.workspaces.administration.section:integrations',
                'admin.workspaces.administration.section:system-operations',
                'admin.settings.approvals.*',
                'admin.governance.*',
                'admin.integrations.*',
                'admin.email-identity.*',
                'admin.activity-logs.*',
                'admin.operations.*',
            ],
        ],
        [
            'label' => 'Website',
            'description' => 'Website content and commercial documents.',
            'route' => 'admin.workspaces.administration.section',
            'route_params' => ['section' => 'website'],
            'permission' => 'website.gallery.view|website.media.view|website.settings.view|documents.settings.view',
            'icon' => 'photograph',
            'active_routes' => [
                'admin.workspaces.administration.section:website',
                'admin.workspaces.administration.section:website-content',
                'admin.workspaces.administration.section:commercial-documents',
                'admin.website.gallery.*',
                'admin.website.media.*',
                'admin.website.settings.*',
                'admin.documents.settings.*',
            ],
        ],
    ],

    'sections' => [

        'security-access' => [
            'title' => 'Security',
            'description' => 'Who can sign in, what they can access, and how access changes are audited.',
            'icon' => 'shield-check',
            'groups' => [
                [
                    'label' => 'Security',
                    'items' => [
                        [
                            'key' => 'users',
                            'label' => 'Users',
                            'description' => 'Accounts, branches, and role assignment.',
                            'route' => 'admin.users.index',
                            'permission' => 'users.view',
                            'icon' => 'users',
                            'active_routes' => ['admin.users.*'],
                        ],
                        [
                            'key' => 'roles',
                            'label' => 'Roles',
                            'description' => 'What each role can access across the business.',
                            'route' => 'admin.access-control.roles',
                            'permission' => 'roles.view',
                            'icon' => 'shield-check',
                            'active_routes' => ['admin.access-control.roles', 'admin.roles.*', 'admin.access-control.matrix', 'admin.permissions.*'],
                        ],
                        [
                            'key' => 'access-audit',
                            'label' => 'Access Audit',
                            'description' => 'Authentication and authorization history.',
                            'route' => 'admin.security.audit.index',
                            'permission' => 'security.audit.view',
                            'icon' => 'document-text',
                            'active_routes' => ['admin.security.audit.*', 'admin.security.sessions.*'],
                        ],
                    ],
                ],
            ],
        ],

        'organization' => [
            'title' => 'Organization',
            'description' => 'Company structure and workforce hierarchy.',
            'icon' => 'building',
            'groups' => [
                [
                    'label' => 'Organization',
                    'items' => [
                        ['label' => 'Company', 'description' => 'Legal entities and tenant companies.', 'route' => 'admin.companies.index', 'permission' => 'companies.manage', 'icon' => 'building', 'active_routes' => ['admin.companies.*']],
                        ['label' => 'Branches', 'description' => 'Branch locations and defaults.', 'route' => 'admin.branches.index', 'permission' => 'branches.manage', 'icon' => 'location-marker', 'active_routes' => ['admin.branches.*']],
                        ['label' => 'Departments', 'description' => 'Organizational units and hierarchy.', 'route' => 'admin.departments.index', 'permission' => 'departments.manage', 'icon' => 'view-grid', 'active_routes' => ['admin.departments.*']],
                        ['label' => 'Teams', 'description' => 'Position titles and reporting structure.', 'route' => 'admin.job-titles.index', 'permission' => 'organization.job_titles.view', 'icon' => 'badge-check', 'active_routes' => ['admin.job-titles.*']],
                    ],
                ],
            ],
        ],

        'configuration' => [
            'title' => 'Configuration',
            'description' => 'System behaviour, numbering, and shared defaults.',
            'icon' => 'cog',
            'groups' => [
                [
                    'label' => 'Configuration',
                    'items' => [
                        ['key' => 'system-settings', 'label' => 'System Settings', 'description' => 'Company-wide configuration and preferences.', 'route' => 'admin.settings.show', 'route_params' => ['section' => 'hub'], 'permission' => 'settings.view', 'icon' => 'cog', 'active_routes' => ['admin.settings.index', 'admin.settings.show', 'admin.settings.update', 'admin.settings.branding.*', 'admin.settings.company-email.*']],
                        ['key' => 'business-settings', 'label' => 'Business Settings', 'description' => 'Required fields and form visibility rules.', 'route' => 'admin.settings.forms.index', 'permission' => 'settings.view', 'icon' => 'clipboard-list', 'active_routes' => ['admin.settings.forms.*']],
                        ['key' => 'number-series', 'label' => 'Numbering', 'description' => 'Document sequences, prefixes, and numbering rules.', 'route' => 'admin.settings.numbering.index', 'permission' => 'settings.view', 'icon' => 'template', 'active_routes' => ['admin.settings.numbering.*']],
                        ['key' => 'defaults', 'label' => 'Defaults', 'description' => 'Shared reference data and lookup values.', 'route' => 'admin.master-data.index', 'permission' => 'configuration.master_data.view', 'icon' => 'template', 'active_routes' => ['admin.master-data.*', 'admin.settings.document-types.*']],
                    ],
                ],
            ],
        ],

        'operations' => [
            'title' => 'Operations',
            'description' => 'Workflows, integrations, and system operations.',
            'icon' => 'chip',
            // Card hub — do not flatten every feature into the secondary tab strip.
            'presentation' => 'hub',
            'hub_route' => 'admin.workspaces.administration.catalog',
            'hub_route_params' => ['section' => 'operations'],
            'groups' => [
                [
                    'label' => 'Workflows',
                    'items' => [
                        ['key' => 'approval-rules', 'label' => 'Approval Rules', 'description' => 'Discount, credit, and workflow approvals.', 'route' => 'admin.settings.approvals.index', 'permission' => 'settings.view', 'icon' => 'badge-check', 'active_routes' => ['admin.settings.approvals.*']],
                        ['key' => 'approval-chains', 'label' => 'Approval Chains', 'description' => 'Multi-step approval sequences.', 'route' => 'admin.governance.chains.index', 'permission' => 'governance.chains.view', 'icon' => 'switch-horizontal', 'active_routes' => ['admin.governance.chains.*']],
                        ['key' => 'workflow-rules', 'label' => 'Workflow Rules', 'description' => 'Automated process triggers and actions.', 'route' => 'admin.governance.workflow-rules.index', 'permission' => 'governance.workflow.view', 'icon' => 'cog', 'active_routes' => ['admin.governance.workflow-rules.*']],
                        ['key' => 'escalations', 'label' => 'Escalations', 'description' => 'Timeout rules and escalation routing.', 'route' => 'admin.governance.escalations.index', 'permission' => 'governance.escalations.view', 'icon' => 'exclamation', 'active_routes' => ['admin.governance.escalations.*']],
                        ['key' => 'delegations', 'label' => 'Delegations', 'description' => 'Temporary approval authority.', 'route' => 'admin.governance.delegations.index', 'permission' => 'governance.delegations.view', 'icon' => 'users', 'active_routes' => ['admin.governance.delegations.*']],
                    ],
                ],
                [
                    'label' => 'Integrations',
                    'items' => [
                        ['label' => 'Email Identity', 'description' => 'Sender addresses and onboarding readiness.', 'route' => 'admin.email-identity.index', 'permission' => 'integrations.view|employees.manage|integrations.manage', 'icon' => 'inbox', 'active_routes' => ['admin.email-identity.*']],
                        ['label' => 'Email Settings', 'description' => 'SMTP and outbound email.', 'route' => 'admin.integrations.email.index', 'permission' => 'integrations.view|integrations.email.manage', 'icon' => 'inbox', 'active_routes' => ['admin.integrations.email.*']],
                        ['label' => 'SMS Settings', 'description' => 'SMS provider credentials and routing.', 'route' => 'admin.integrations.sms.index', 'permission' => 'integrations.view|integrations.sms.manage', 'icon' => 'inbox', 'active_routes' => ['admin.integrations.sms.*']],
                        ['label' => 'API Keys', 'description' => 'Developer keys and programmatic access.', 'route' => 'admin.integrations.api-keys.index', 'permission' => 'integrations.view|integrations.api.manage', 'icon' => 'key', 'active_routes' => ['admin.integrations.api-keys.*']],
                        ['label' => 'Webhooks', 'description' => 'Outbound event subscriptions.', 'route' => 'admin.integrations.webhooks.index', 'permission' => 'integrations.view|integrations.webhooks.manage', 'icon' => 'switch-horizontal', 'active_routes' => ['admin.integrations.webhooks.*']],
                        ['label' => 'Third Party Integrations', 'description' => 'Connectors for external systems.', 'route' => 'admin.integrations.providers.index', 'permission' => 'integrations.view|integrations.providers.manage', 'icon' => 'switch-horizontal', 'active_routes' => ['admin.integrations.providers.*']],
                    ],
                ],
                [
                    'label' => 'System',
                    'items' => [
                        ['label' => 'System Health', 'description' => 'Service status and queues.', 'route' => 'admin.operations.health.index', 'permission' => 'operations.health.view', 'icon' => 'chip', 'active_routes' => ['admin.operations.health.*']],
                        ['label' => 'Background Jobs', 'description' => 'Queued tasks and schedules.', 'route' => 'admin.operations.jobs.index', 'permission' => 'operations.jobs.view', 'icon' => 'switch-horizontal', 'active_routes' => ['admin.operations.jobs.*']],
                        ['label' => 'Activity Logs', 'description' => 'User and system activity trail.', 'route' => 'admin.activity-logs.index', 'permission' => 'activity_logs.view', 'icon' => 'clock', 'active_routes' => ['admin.activity-logs.*']],
                        ['label' => 'Audit Logs', 'description' => 'Compliance-grade audit records.', 'route' => 'admin.operations.audit.index', 'permission' => 'operations.audit.view', 'icon' => 'document-text', 'active_routes' => ['admin.operations.audit.*']],
                        ['label' => 'Backups', 'description' => 'Database and file backup schedules.', 'route' => 'admin.operations.backups.index', 'permission' => 'operations.backups.view', 'icon' => 'archive', 'active_routes' => ['admin.operations.backups.*']],
                        ['label' => 'Data Retention', 'description' => 'Archive policies and purge rules.', 'route' => 'admin.operations.retention.index', 'permission' => 'operations.retention.view', 'icon' => 'archive', 'active_routes' => ['admin.operations.retention.*']],
                    ],
                ],
            ],
        ],

        'website' => [
            'title' => 'Website',
            'description' => 'Public website content and commercial document branding.',
            'icon' => 'photograph',
            'presentation' => 'hub',
            'hub_route' => 'admin.workspaces.administration.catalog',
            'hub_route_params' => ['section' => 'website'],
            'groups' => [
                [
                    'label' => 'Website Content',
                    'items' => [
                        ['key' => 'gallery', 'label' => 'Gallery', 'description' => 'Portfolio projects on the public site.', 'route' => 'admin.website.gallery.index', 'permission' => 'website.gallery.view', 'icon' => 'photograph', 'active_routes' => ['admin.website.gallery.*']],
                        ['key' => 'media-library', 'label' => 'Media Library', 'description' => 'Homepage, service, team, and testimonial images.', 'route' => 'admin.website.media.index', 'permission' => 'website.media.view', 'icon' => 'collection', 'active_routes' => ['admin.website.media.*']],
                        ['key' => 'footer-contact', 'label' => 'Footer & Contact', 'description' => 'Phone, email, WhatsApp, map, and social links.', 'route' => 'admin.website.settings.footer-contact', 'permission' => 'website.settings.view', 'icon' => 'document-text', 'active_routes' => ['admin.website.settings.footer-contact', 'admin.website.settings.footer-contact.update', 'admin.website.settings.reset']],
                        ['key' => 'seo-global', 'label' => 'SEO / Global', 'description' => 'Site name, tagline, and default meta.', 'route' => 'admin.website.settings.seo-global', 'permission' => 'website.settings.view', 'icon' => 'globe-alt', 'active_routes' => ['admin.website.settings.seo-global', 'admin.website.settings.seo-global.update', 'admin.website.settings.reset']],
                    ],
                ],
                [
                    'label' => 'Commercial',
                    'items' => [
                        [
                            'key' => 'document-settings',
                            'label' => 'Commercial Documents',
                            'description' => 'Quotation, invoice, and receipt branding.',
                            'route' => 'admin.documents.settings.index',
                            'permission' => 'documents.settings.view',
                            'icon' => 'document-text',
                            'active_routes' => ['admin.documents.settings.index', 'admin.documents.settings.update', 'admin.documents.settings.reset'],
                        ],
                    ],
                ],
            ],
        ],

        // Legacy section keys — keep for deep links / bookmarks; same catalogs as Operations / Website.
        'workflow-governance' => [
            'title' => 'Operations',
            'description' => 'Workflows, integrations, and system operations.',
            'icon' => 'chip',
            'groups' => [], // filled at boot via alias — see AdministrationWorkspacePresenter
            'alias_of' => 'operations',
        ],
        'integrations' => [
            'title' => 'Operations',
            'description' => 'Workflows, integrations, and system operations.',
            'icon' => 'chip',
            'alias_of' => 'operations',
        ],
        'system-operations' => [
            'title' => 'Operations',
            'description' => 'Workflows, integrations, and system operations.',
            'icon' => 'chip',
            'alias_of' => 'operations',
        ],
        'website-content' => [
            'title' => 'Website',
            'description' => 'Public website content and commercial document branding.',
            'icon' => 'photograph',
            'alias_of' => 'website',
        ],
        'commercial-documents' => [
            'title' => 'Website',
            'description' => 'Public website content and commercial document branding.',
            'icon' => 'photograph',
            'alias_of' => 'website',
        ],
    ],

];
