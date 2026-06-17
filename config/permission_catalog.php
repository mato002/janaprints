<?php

/**
 * UI-only permission catalog for Access Control screens.
 * Maps existing permission keys to business modules — does not alter authorization.
 */

return [

    'columns' => [
        'view' => ['label' => 'View'],
        'create' => ['label' => 'Create'],
        'edit' => ['label' => 'Edit'],
        'delete' => ['label' => 'Delete'],
        'approve' => ['label' => 'Approve'],
    ],

    'modules' => [

        'administration' => [
            'label' => 'Administration',
            'entities' => [
                'users' => [
                    'label' => 'Users',
                    'permissions' => [
                        'view' => 'users.view',
                        'create' => 'users.create',
                        'edit' => 'users.edit',
                        'delete' => 'users.delete',
                    ],
                ],
                'roles' => [
                    'label' => 'Roles',
                    'permissions' => [
                        'view' => 'roles.view',
                        'create' => 'roles.create',
                        'edit' => 'roles.edit',
                        'delete' => 'roles.delete',
                    ],
                ],
                'activity_logs' => [
                    'label' => 'Activity Logs',
                    'permissions' => [
                        'view' => 'activity_logs.view',
                    ],
                ],
                'website_gallery' => [
                    'label' => 'Website Gallery',
                    'permissions' => [
                        'view' => 'website.gallery.view',
                        'create' => 'website.gallery.create',
                        'edit' => 'website.gallery.edit',
                        'delete' => 'website.gallery.delete',
                        'approve' => 'website.gallery.publish',
                    ],
                ],
                'website_media' => [
                    'label' => 'Website Media Library',
                    'permissions' => [
                        'view' => 'website.media.view',
                        'create' => 'website.media.create',
                        'edit' => 'website.media.edit',
                        'delete' => 'website.media.delete',
                    ],
                ],
                'website_settings' => [
                    'label' => 'Website Settings',
                    'permissions' => [
                        'view' => 'website.settings.view',
                        'edit' => 'website.settings.edit',
                    ],
                ],
                'document_settings' => [
                    'label' => 'Commercial Document Settings',
                    'permissions' => [
                        'view' => 'documents.settings.view',
                        'edit' => 'documents.settings.edit',
                    ],
                ],
                'system_health' => [
                    'label' => 'System Health',
                    'permissions' => [
                        'view' => 'operations.health.view',
                        'edit' => 'operations.health.manage',
                    ],
                ],
                'background_jobs' => [
                    'label' => 'Background Jobs',
                    'permissions' => [
                        'view' => 'operations.jobs.view',
                        'edit' => 'operations.jobs.retry',
                        'delete' => 'operations.jobs.cancel',
                    ],
                ],
                'audit_logs' => [
                    'label' => 'Audit Logs',
                    'permissions' => [
                        'view' => 'operations.audit.view',
                        'create' => 'operations.audit.export',
                    ],
                ],
                'backups' => [
                    'label' => 'Backups',
                    'permissions' => [
                        'view' => 'operations.backups.view',
                        'create' => 'operations.backups.download',
                        'edit' => 'operations.backups.manage',
                    ],
                ],
                'data_retention' => [
                    'label' => 'Data Retention',
                    'permissions' => [
                        'view' => 'operations.retention.view',
                        'edit' => 'operations.retention.manage',
                    ],
                ],
                'approval_chains' => [
                    'label' => 'Approval Chains',
                    'permissions' => [
                        'view' => 'governance.chains.view',
                        'create' => 'governance.chains.create',
                        'edit' => 'governance.chains.edit',
                        'approve' => 'governance.chains.activate',
                    ],
                ],
                'workflow_rules' => [
                    'label' => 'Workflow Rules',
                    'permissions' => [
                        'view' => 'governance.workflow.view',
                        'create' => 'governance.workflow.create',
                        'edit' => 'governance.workflow.manage',
                    ],
                ],
                'escalations' => [
                    'label' => 'Workflow Escalations',
                    'permissions' => [
                        'view' => 'governance.escalations.view',
                        'edit' => 'governance.escalations.manage',
                    ],
                ],
                'security_sessions' => [
                    'label' => 'User Sessions',
                    'permissions' => [
                        'view' => 'security.sessions.view',
                        'edit' => 'security.sessions.terminate',
                        'delete' => 'security.sessions.force_logout',
                        'approve' => 'security.sessions.audit',
                    ],
                ],
                'security_audit' => [
                    'label' => 'Access Audit',
                    'permissions' => [
                        'view' => 'security.audit.view',
                        'create' => 'security.audit.export',
                        'edit' => 'security.audit.manage',
                    ],
                ],
                'settings' => [
                    'label' => 'Settings',
                    'permissions' => [
                        'view' => 'settings.view',
                        'edit' => 'settings.manage',
                    ],
                ],
                'integrations' => [
                    'label' => 'Integrations',
                    'permissions' => [
                        'view' => 'integrations.view',
                        'edit' => 'integrations.manage',
                    ],
                ],
                'integrations_email' => [
                    'label' => 'Email Settings',
                    'permissions' => [
                        'edit' => 'integrations.email.manage',
                    ],
                ],
                'integrations_sms' => [
                    'label' => 'SMS Settings',
                    'permissions' => [
                        'edit' => 'integrations.sms.manage',
                    ],
                ],
                'integrations_api' => [
                    'label' => 'API Keys',
                    'permissions' => [
                        'edit' => 'integrations.api.manage',
                    ],
                ],
                'integrations_webhooks' => [
                    'label' => 'Webhooks',
                    'permissions' => [
                        'edit' => 'integrations.webhooks.manage',
                    ],
                ],
                'integrations_providers' => [
                    'label' => 'Third Party Integrations',
                    'permissions' => [
                        'edit' => 'integrations.providers.manage',
                    ],
                ],
                'integrations_audit' => [
                    'label' => 'Integration Audit',
                    'permissions' => [
                        'view' => 'integrations.audit.view',
                    ],
                ],
                'master_data' => [
                    'label' => 'Master Data',
                    'permissions' => [
                        'view' => 'configuration.master_data.view',
                        'create' => 'configuration.master_data.create',
                        'edit' => 'configuration.master_data.edit',
                        'delete' => 'configuration.master_data.deactivate',
                        'approve' => 'configuration.master_data.import',
                    ],
                ],
            ],
        ],

        'organization' => [
            'label' => 'Organization',
            'entities' => [
                'companies' => [
                    'label' => 'Companies',
                    'permissions' => [
                        'edit' => 'companies.manage',
                    ],
                ],
                'branches' => [
                    'label' => 'Branches',
                    'permissions' => [
                        'edit' => 'branches.manage',
                    ],
                ],
                'departments' => [
                    'label' => 'Departments',
                    'permissions' => [
                        'edit' => 'departments.manage',
                    ],
                ],
                'employees' => [
                    'label' => 'Employees',
                    'permissions' => [
                        'edit' => 'employees.manage',
                    ],
                ],
                'job_titles' => [
                    'label' => 'Job Titles',
                    'permissions' => [
                        'view' => 'organization.job_titles.view',
                        'create' => 'organization.job_titles.create',
                        'edit' => 'organization.job_titles.edit',
                        'delete' => 'organization.job_titles.deactivate',
                    ],
                ],
            ],
        ],

        'hr' => [
            'label' => 'HR',
            'entities' => [
                'attendance' => [
                    'label' => 'Attendance',
                    'permissions' => [
                        'view' => 'hr.attendance.view',
                        'create' => 'hr.attendance.create',
                        'edit' => 'hr.attendance.edit',
                        'approve' => 'hr.attendance.approve',
                        'export' => 'hr.attendance.export',
                    ],
                ],
                'leave' => [
                    'label' => 'Leave',
                    'permissions' => [
                        'view' => 'hr.leave.view',
                        'create' => 'hr.leave.create',
                        'approve' => 'hr.leave.approve',
                        'reject' => 'hr.leave.reject',
                        'export' => 'hr.leave.export',
                    ],
                ],
                'payroll' => [
                    'label' => 'Payroll',
                    'permissions' => [
                        'view' => 'hr.payroll.view',
                        'create' => 'hr.payroll.process',
                        'edit' => 'hr.payroll.review',
                        'approve' => 'hr.payroll.approve',
                        'delete' => 'hr.payroll.post',
                        'export' => 'hr.payroll.export',
                    ],
                ],
                'documents' => [
                    'label' => 'Documents',
                    'permissions' => [
                        'view' => 'hr.documents.view',
                        'create' => 'hr.documents.upload',
                        'delete' => 'hr.documents.delete',
                    ],
                ],
                'performance' => [
                    'label' => 'Performance',
                    'permissions' => [
                        'view' => 'hr.performance.view',
                        'create' => 'hr.performance.manage',
                        'edit' => 'hr.performance.manage',
                        'delete' => 'hr.performance.manage',
                    ],
                ],
                'training' => [
                    'label' => 'Training',
                    'permissions' => [
                        'view' => 'hr.training.view',
                        'create' => 'hr.training.manage',
                        'edit' => 'hr.training.manage',
                        'delete' => 'hr.training.manage',
                    ],
                ],
                'exit' => [
                    'label' => 'Exit Management',
                    'permissions' => [
                        'view' => 'hr.exit.view',
                        'create' => 'hr.exit.manage',
                        'edit' => 'hr.exit.manage',
                    ],
                ],
                'dashboard' => [
                    'label' => 'HR Dashboard',
                    'permissions' => [
                        'view' => 'hr.dashboard.view',
                    ],
                ],
            ],
        ],

        'crm' => [
            'label' => 'CRM',
            'entities' => [
                'customers' => [
                    'label' => 'Customers',
                    'permissions' => [
                        'view' => 'crm.customers.view',
                        'create' => 'crm.customers.create',
                        'edit' => 'crm.customers.edit',
                        'delete' => 'crm.customers.delete',
                    ],
                ],
                'leads' => [
                    'label' => 'Leads',
                    'permissions' => [
                        'view' => 'crm.leads.view',
                        'create' => 'crm.leads.create',
                        'edit' => 'crm.leads.edit',
                        'delete' => 'crm.leads.delete',
                    ],
                ],
                'activities' => [
                    'label' => 'Activities',
                    'permissions' => [
                        'view' => 'crm.activities.view',
                        'create' => 'crm.activities.create',
                        'edit' => 'crm.activities.edit',
                        'delete' => 'crm.activities.delete',
                    ],
                ],
            ],
        ],

        'sales' => [
            'label' => 'Sales',
            'entities' => [
                'quotations' => [
                    'label' => 'Quotations',
                    'permissions' => [
                        'view' => 'quotations.view',
                        'create' => 'quotations.create',
                        'edit' => 'quotations.edit',
                        'delete' => 'quotations.delete',
                        'approve' => 'quotations.approve',
                    ],
                    'extra' => [
                        ['label' => 'Send', 'permission' => 'quotations.send'],
                        ['label' => 'Convert', 'permission' => 'quotations.convert'],
                    ],
                ],
                'sales_orders' => [
                    'label' => 'Sales Orders',
                    'permissions' => [
                        'view' => 'sales_orders.view',
                        'create' => 'sales_orders.create',
                        'edit' => 'sales_orders.edit',
                        'delete' => 'sales_orders.delete',
                        'approve' => 'sales_orders.confirm',
                    ],
                    'extra' => [
                        ['label' => 'Production handoff', 'permission' => 'sales_orders.production'],
                        ['label' => 'Close', 'permission' => 'sales_orders.close'],
                    ],
                ],
                'pos' => [
                    'label' => 'Point of Sale',
                    'permissions' => [
                        'view' => 'pos.view',
                        'create' => 'pos.create',
                        'edit' => 'pos.edit',
                        'cancel' => 'pos.cancel',
                        'refund' => 'pos.refund',
                    ],
                    'extra' => [
                        ['label' => 'View sessions', 'permission' => 'pos.sessions.view'],
                        ['label' => 'Open sessions', 'permission' => 'pos.sessions.open'],
                        ['label' => 'Close sessions', 'permission' => 'pos.sessions.close'],
                        ['label' => 'Approve session variance', 'permission' => 'pos.sessions.approve_variance'],
                        ['label' => 'Export session summary', 'permission' => 'pos.sessions.export'],
                        ['label' => 'View sessions (legacy)', 'permission' => 'commercial.pos.sessions.view'],
                        ['label' => 'Open sessions (legacy)', 'permission' => 'commercial.pos.sessions.open'],
                        ['label' => 'Close sessions (legacy)', 'permission' => 'commercial.pos.sessions.close'],
                        ['label' => 'Session audit', 'permission' => 'commercial.pos.sessions.audit'],
                        ['label' => 'Session admin', 'permission' => 'commercial.pos.sessions.admin'],
                        ['label' => 'View reconciliation', 'permission' => 'commercial.pos.reconciliation.view'],
                        ['label' => 'Submit reconciliation', 'permission' => 'commercial.pos.reconciliation.create'],
                        ['label' => 'Approve reconciliation', 'permission' => 'commercial.pos.reconciliation.approve'],
                        ['label' => 'Reconciliation audit', 'permission' => 'commercial.pos.reconciliation.audit'],
                        ['label' => 'View returns', 'permission' => 'commercial.pos.returns.view'],
                        ['label' => 'Create returns', 'permission' => 'commercial.pos.returns.create'],
                        ['label' => 'Approve returns', 'permission' => 'commercial.pos.returns.approve'],
                        ['label' => 'Return audit', 'permission' => 'commercial.pos.returns.audit'],
                        ['label' => 'View POS intelligence', 'permission' => 'commercial.pos.reports.view'],
                        ['label' => 'Export POS intelligence', 'permission' => 'commercial.pos.reports.export'],
                        ['label' => 'View POS certification', 'permission' => 'commercial.pos.certification.view'],
                        ['label' => 'Counter sales workstation', 'permission' => 'pos.counter_sales.view'],
                        ['label' => 'Counter sales create', 'permission' => 'pos.counter_sales.create'],
                        ['label' => 'Counter sales hold', 'permission' => 'pos.counter_sales.hold'],
                        ['label' => 'Counter sales complete', 'permission' => 'pos.counter_sales.complete'],
                        ['label' => 'Counter sales cancel', 'permission' => 'pos.counter_sales.cancel'],
                        ['label' => 'Reprint POS receipts', 'permission' => 'pos.receipts.reprint'],
                    ],
                ],
            ],
        ],

        'artwork' => [
            'label' => 'Artwork',
            'entities' => [
                'artwork' => [
                    'label' => 'Artwork Requests',
                    'permissions' => [
                        'view' => 'artwork.view',
                        'create' => 'artwork.create',
                        'edit' => 'artwork.edit',
                        'delete' => 'artwork.delete',
                        'approve' => 'artwork.approve',
                    ],
                    'extra' => [
                        ['label' => 'Assign', 'permission' => 'artwork.assign'],
                        ['label' => 'Submit', 'permission' => 'artwork.submit'],
                    ],
                ],
            ],
        ],

        'production' => [
            'label' => 'Production',
            'entities' => [
                'jobs' => [
                    'label' => 'Production Jobs',
                    'permissions' => [
                        'view' => 'production.view',
                        'create' => 'production.create',
                        'edit' => 'production.edit',
                        'delete' => 'production.delete',
                        'approve' => 'production.qc',
                    ],
                    'extra' => [
                        ['label' => 'Schedule', 'permission' => 'production.schedule'],
                        ['label' => 'Start', 'permission' => 'production.start'],
                        ['label' => 'Complete', 'permission' => 'production.complete'],
                        ['label' => 'Job costing', 'permission' => 'production.costing.view'],
                        ['label' => 'Manage costing', 'permission' => 'production.costing.manage'],
                    ],
                ],
                'outputs' => [
                    'label' => 'Production Outputs',
                    'permissions' => [
                        'view' => 'production.outputs.view',
                        'create' => 'production.outputs.create',
                    ],
                    'extra' => [
                        ['label' => 'Post completion', 'permission' => 'production.outputs.post'],
                        ['label' => 'Manual unit cost', 'permission' => 'production.outputs.manual-cost'],
                    ],
                ],
                'workspaces' => [
                    'label' => 'Production Workspaces',
                    'permissions' => [],
                    'extra' => [
                        [
                            'label' => 'View Production Queue',
                            'permission' => 'production.queue.view',
                            'description' => 'Access production queue register.',
                        ],
                        [
                            'label' => 'View Production Scheduling',
                            'permission' => 'production.scheduling.view',
                            'description' => 'Access production planning and scheduling workspace.',
                        ],
                        [
                            'label' => 'View Quality Control',
                            'permission' => 'production.quality.view',
                            'description' => 'Access QC inspection register.',
                        ],
                        [
                            'label' => 'View Work Centers',
                            'permission' => 'production.work-centers.view',
                            'description' => 'Access work center and capacity master data.',
                        ],
                    ],
                ],
            ],
        ],

        'inventory' => [
            'label' => 'Inventory',
            'entities' => [
                'catalogue' => [
                    'label' => 'Catalogue',
                    'permissions' => [
                        'view' => 'catalogue.view',
                        'create' => 'catalogue.create',
                        'edit' => 'catalogue.edit',
                        'delete' => 'catalogue.delete',
                    ],
                    'extra' => [
                        ['label' => 'Manage classification', 'permission' => 'inventory.classification.manage'],
                    ],
                ],
                'stores' => [
                    'label' => 'Stores',
                    'permissions' => [
                        'view' => 'inventory.view',
                        'create' => 'inventory.create',
                        'edit' => 'inventory.edit',
                        'delete' => 'inventory.delete',
                    ],
                    'extra' => [
                        ['label' => 'Receive', 'permission' => 'inventory.receive'],
                        ['label' => 'Issue', 'permission' => 'inventory.issue'],
                        ['label' => 'Production issue override', 'permission' => 'inventory.issue.production.override'],
                        ['label' => 'Adjust', 'permission' => 'inventory.adjust'],
                        ['label' => 'Transfer', 'permission' => 'inventory.transfer'],
                        ['label' => 'Valuation', 'permission' => 'inventory.valuation.view'],
                    ],
                ],
                'stock_counts' => [
                    'label' => 'Stock Counts',
                    'permissions' => [
                        'view' => 'inventory.count.view',
                        'create' => 'inventory.count.create',
                        'edit' => 'inventory.count.edit',
                    ],
                    'extra' => [
                        ['label' => 'Submit', 'permission' => 'inventory.count.submit'],
                        ['label' => 'Approve', 'permission' => 'inventory.count.approve'],
                        ['label' => 'Post', 'permission' => 'inventory.count.post'],
                    ],
                ],
                'cycle_counts' => [
                    'label' => 'Cycle Counts',
                    'permissions' => [
                        'view' => 'inventory.cycle.view',
                    ],
                    'extra' => [
                        ['label' => 'Manage', 'permission' => 'inventory.cycle.manage'],
                    ],
                ],
                'variances' => [
                    'label' => 'Variance Reports',
                    'permissions' => [
                        'view' => 'inventory.variance.view',
                    ],
                    'extra' => [
                        ['label' => 'Export', 'permission' => 'inventory.variance.export'],
                    ],
                ],
                'variance_reasons' => [
                    'label' => 'Variance Reason Codes',
                    'permissions' => [
                        'view' => 'inventory.variance-reasons.view',
                    ],
                    'extra' => [
                        ['label' => 'Manage', 'permission' => 'inventory.variance-reasons.manage'],
                    ],
                ],
                'virtual_locations' => [
                    'label' => 'Virtual Locations',
                    'permissions' => [
                        'view' => 'inventory.virtual-locations.view',
                    ],
                    'extra' => [
                        ['label' => 'Manage', 'permission' => 'inventory.virtual-locations.manage'],
                    ],
                ],
                'reconciliations' => [
                    'label' => 'Reconciliations',
                    'permissions' => [
                        'view' => 'inventory.reconcile.view',
                        'approve' => 'inventory.reconcile.approve',
                        'post' => 'inventory.reconcile.post',
                    ],
                ],
                'intelligence' => [
                    'label' => 'Inventory Intelligence',
                    'permissions' => [
                        'view' => 'inventory.intelligence.view',
                        'generate' => 'inventory.intelligence.generate',
                        'configure' => 'inventory.intelligence.configure',
                    ],
                ],
            ],
        ],

        'printing_intelligence' => [
            'label' => 'Printing Intelligence',
            'entities' => [
                'workspace' => [
                    'label' => 'Printing Intelligence',
                    'permissions' => [
                        'view' => 'printing.intelligence.view',
                        'configure' => 'printing.intelligence.configure',
                    ],
                ],
                'artwork_analysis' => [
                    'label' => 'Artwork Analysis',
                    'permissions' => [
                        'analyze' => 'printing.artwork.analyze',
                        'colour_analyze' => 'printing.artwork.colour-analyze',
                        'estimate_ink' => 'printing.artwork.estimate-ink',
                        'estimate_production' => 'printing.artwork.estimate-production',
                    ],
                ],
                'quotation_intelligence' => [
                    'label' => 'Quotation Intelligence',
                    'permissions' => [
                        'estimate' => 'printing.quotation.estimate',
                        'apply_estimate' => 'printing.quotation.apply-estimate',
                    ],
                ],
                'estimate_actual_learning' => [
                    'label' => 'Estimate vs Actual Learning',
                    'permissions' => [
                        'view' => 'printing.estimate-actual.view',
                        'compare' => 'printing.estimate-actual.compare',
                        'analytics' => 'printing.estimate-actual.analytics',
                    ],
                ],
                'calibration_governance' => [
                    'label' => 'Cost Accuracy Governance',
                    'permissions' => [
                        'view' => 'printing.calibration.view',
                        'review' => 'printing.calibration.review',
                        'approve' => 'printing.calibration.approve',
                        'manage' => 'printing.calibration.manage',
                    ],
                ],
                'profitability_intelligence' => [
                    'label' => 'Production Profitability',
                    'permissions' => [
                        'view' => 'printing.profitability.view',
                        'analytics' => 'printing.profitability.analytics',
                        'generate' => 'printing.profitability.generate',
                    ],
                ],
                'executive_intelligence' => [
                    'label' => 'Executive Intelligence',
                    'permissions' => [
                        'view' => 'printing.executive.view',
                        'forecast' => 'printing.executive.forecast',
                        'analytics' => 'printing.executive.analytics',
                    ],
                ],
                'ink_profiles' => [
                    'label' => 'Ink Profiles',
                    'permissions' => [
                        'view' => 'printing.ink-profiles.view',
                        'manage' => 'printing.ink-profiles.manage',
                    ],
                ],
            ],
        ],

        'procurement' => [
            'label' => 'Procurement',
            'entities' => [
                'vendors' => [
                    'label' => 'Vendors',
                    'permissions' => [
                        'view' => 'procurement.vendors.view',
                        'create' => 'procurement.vendors.create',
                        'edit' => 'procurement.vendors.edit',
                        'delete' => 'procurement.vendors.delete',
                    ],
                ],
                'requests' => [
                    'label' => 'Purchase Requests',
                    'permissions' => [
                        'view' => 'procurement.requests.view',
                        'create' => 'procurement.requests.create',
                        'edit' => 'procurement.requests.edit',
                        'delete' => 'procurement.requests.delete',
                        'approve' => 'procurement.requests.approve',
                    ],
                ],
                'orders' => [
                    'label' => 'Purchase Orders',
                    'permissions' => [
                        'view' => 'procurement.orders.view',
                        'create' => 'procurement.orders.create',
                        'edit' => 'procurement.orders.edit',
                        'delete' => 'procurement.orders.delete',
                        'approve' => 'procurement.orders.approve',
                    ],
                    'extra' => [
                        ['label' => 'Receive goods', 'permission' => 'procurement.orders.receive'],
                    ],
                ],
                'rfq' => [
                    'label' => 'RFQ',
                    'permissions' => [
                        'view' => 'procurement.rfq.view',
                        'create' => 'procurement.rfq.create',
                        'edit' => 'procurement.rfq.edit',
                    ],
                ],
                'comparison' => [
                    'label' => 'Vendor Comparison',
                    'permissions' => [
                        'view' => 'procurement.comparison.view',
                    ],
                    'extra' => [
                        ['label' => 'Award vendor', 'permission' => 'procurement.comparison.manage'],
                    ],
                ],
                'vendor_comparison_workspace' => [
                    'label' => 'Vendor Comparison Workspace',
                    'permissions' => [
                        'view' => 'procurement.vendor_comparison.view',
                    ],
                    'extra' => [
                        ['label' => 'Award supplier', 'permission' => 'procurement.vendor_comparison.award'],
                        ['label' => 'Manage comparison', 'permission' => 'procurement.vendor_comparison.manage'],
                    ],
                ],
                'performance' => [
                    'label' => 'Supplier Performance Intelligence',
                    'permissions' => [
                        'view' => 'procurement.performance.view',
                    ],
                    'extra' => [
                        ['label' => 'Export', 'permission' => 'procurement.performance.export'],
                    ],
                ],
            ],
        ],

        'assets' => [
            'label' => 'Fixed Assets',
            'entities' => [
                'register' => [
                    'label' => 'Asset Register',
                    'permissions' => [
                        'view' => 'assets.view',
                        'create' => 'assets.create',
                        'edit' => 'assets.edit',
                    ],
                    'extra' => [
                        ['label' => 'Manage lifecycle', 'permission' => 'assets.manage'],
                    ],
                ],
                'categories' => [
                    'label' => 'Asset Categories',
                    'permissions' => [
                        'view' => 'assets.categories.view',
                    ],
                    'extra' => [
                        ['label' => 'Manage categories', 'permission' => 'assets.categories.manage'],
                    ],
                ],
                'machines' => [
                    'label' => 'Production Machines',
                    'permissions' => [
                        'view' => 'machines.view',
                    ],
                    'extra' => [
                        ['label' => 'Manage machines', 'permission' => 'machines.manage'],
                        ['label' => 'Manage capacity', 'permission' => 'machines.capacity.manage'],
                        ['label' => 'Assign machines', 'permission' => 'machines.assign'],
                    ],
                ],
                'maintenance' => [
                    'label' => 'Maintenance',
                    'permissions' => [
                        'view' => 'maintenance.view',
                        'create' => 'maintenance.create',
                    ],
                    'extra' => [
                        ['label' => 'Manage maintenance', 'permission' => 'maintenance.manage'],
                        ['label' => 'Assign work orders', 'permission' => 'maintenance.assign'],
                        ['label' => 'Complete work orders', 'permission' => 'maintenance.complete'],
                        ['label' => 'Close work orders', 'permission' => 'maintenance.close'],
                        ['label' => 'View calendar', 'permission' => 'maintenance.calendar.view'],
                    ],
                ],
                'finance' => [
                    'label' => 'Asset Finance',
                    'permissions' => [
                        'view' => 'assets.depreciation.view',
                    ],
                    'extra' => [
                        ['label' => 'Run depreciation', 'permission' => 'assets.depreciation.run'],
                        ['label' => 'Post depreciation', 'permission' => 'assets.depreciation.post'],
                        ['label' => 'View reconciliation', 'permission' => 'assets.reconciliation.view'],
                        ['label' => 'Post disposals', 'permission' => 'assets.disposal.post'],
                        ['label' => 'Manage write-offs', 'permission' => 'assets.writeoff.manage'],
                    ],
                ],
                'intelligence' => [
                    'label' => 'Intelligence & Analytics',
                    'permissions' => [
                        'view' => 'assets.360.view',
                    ],
                    'extra' => [
                        ['label' => 'View analytics', 'permission' => 'assets.analytics.view'],
                        ['label' => 'View health scores', 'permission' => 'assets.health.view'],
                        ['label' => 'View replacement intelligence', 'permission' => 'assets.replacement.view'],
                        ['label' => 'View lifecycle analytics', 'permission' => 'assets.lifecycle.view'],
                    ],
                ],
                'acquisitions' => [
                    'label' => 'Acquisitions & Capitalization',
                    'permissions' => [
                        'view' => 'assets.acquisition.view',
                    ],
                    'extra' => [
                        ['label' => 'Capitalize assets', 'permission' => 'assets.capitalize'],
                        ['label' => 'Approve capitalization', 'permission' => 'assets.capitalize.approve'],
                        ['label' => 'Post acquisition journals', 'permission' => 'assets.acquisition.post'],
                        ['label' => 'Manage warranties', 'permission' => 'assets.warranty.manage'],
                    ],
                ],
                'custody' => [
                    'label' => 'Custody & Accountability',
                    'permissions' => [
                        'view' => 'assets.custody.view',
                    ],
                    'extra' => [
                        ['label' => 'Assign assets', 'permission' => 'assets.assign'],
                        ['label' => 'Transfer assets', 'permission' => 'assets.transfer'],
                        ['label' => 'Record returns', 'permission' => 'assets.return'],
                        ['label' => 'Manage handovers', 'permission' => 'assets.handover.manage'],
                        ['label' => 'Manage custody', 'permission' => 'assets.custody.manage'],
                    ],
                ],
            ],
        ],

        'accounting' => [
            'label' => 'Accounting',
            'entities' => [
                'chart_of_accounts' => [
                    'label' => 'Chart of Accounts',
                    'permissions' => [
                        'view' => 'accounting.chart.view',
                        'create' => 'accounting.chart.create',
                        'edit' => 'accounting.chart.edit',
                        'delete' => 'accounting.chart.delete',
                    ],
                    'extra' => [
                        ['label' => 'Lock / Unlock', 'permission' => 'accounting.chart.lock'],
                    ],
                ],
                'periods' => [
                    'label' => 'Accounting Periods',
                    'permissions' => [
                        'view' => 'accounting.periods.view',
                        'create' => 'accounting.periods.create',
                        'edit' => 'accounting.periods.manage',
                        'delete' => 'accounting.periods.reopen',
                    ],
                    'extra' => [
                        ['label' => 'Close', 'permission' => 'accounting.periods.close'],
                        ['label' => 'Lock', 'permission' => 'accounting.periods.lock'],
                    ],
                ],
                'journals' => [
                    'label' => 'Journals & GL',
                    'permissions' => [
                        'view' => 'accounting.journals.view',
                        'create' => 'accounting.journals.create',
                    ],
                    'extra' => [
                        ['label' => 'Post', 'permission' => 'accounting.journals.post'],
                        ['label' => 'Reverse', 'permission' => 'accounting.journals.reverse'],
                    ],
                ],
                'posting' => [
                    'label' => 'Posting Engine',
                    'permissions' => [
                        'view' => 'accounting.posting.view',
                    ],
                    'extra' => [
                        ['label' => 'Manage rules & templates', 'permission' => 'accounting.posting.manage'],
                        ['label' => 'View posting rules workspace', 'permission' => 'accounting.posting_rules.view'],
                        ['label' => 'Manage posting rules', 'permission' => 'accounting.posting_rules.manage'],
                        ['label' => 'Audit posting rules', 'permission' => 'accounting.posting_rules.audit'],
                    ],
                ],
                'invoices' => [
                    'label' => 'Customer Invoices',
                    'permissions' => [
                        'view' => 'invoices.view',
                        'create' => 'invoices.create',
                        'edit' => 'invoices.edit',
                        'delete' => 'invoices.delete',
                        'approve' => 'invoices.approve',
                    ],
                    'extra' => [
                        ['label' => 'Post to AR', 'permission' => 'invoices.post'],
                        ['label' => 'Cancel', 'permission' => 'invoices.cancel'],
                        ['label' => 'Credit note', 'permission' => 'invoices.credit_note'],
                    ],
                ],
                'payments' => [
                    'label' => 'Customer Payments',
                    'permissions' => [
                        'view' => 'payments.view',
                        'create' => 'payments.create',
                        'edit' => 'payments.edit',
                        'delete' => 'payments.delete',
                    ],
                    'extra' => [
                        ['label' => 'Post', 'permission' => 'payments.post'],
                        ['label' => 'Cancel', 'permission' => 'payments.cancel'],
                    ],
                ],
                'financial_reports' => [
                    'label' => 'Financial Reports',
                    'permissions' => [
                        'view' => 'accounting.reports.view',
                    ],
                ],
                'dashboard' => [
                    'label' => 'Accounting Dashboard',
                    'permissions' => [
                        'view' => 'accounting.dashboard.view',
                    ],
                ],
                'receivables' => [
                    'label' => 'Receivables Reports',
                    'permissions' => [
                        'view' => 'receivables.ledger.view',
                    ],
                    'extra' => [
                        ['label' => 'Customer statement', 'permission' => 'receivables.statement.view'],
                        ['label' => 'Aging analysis', 'permission' => 'receivables.aging.view'],
                    ],
                ],
                'payables_bills' => [
                    'label' => 'Supplier Bills',
                    'permissions' => [
                        'view' => 'payables.bills.view',
                        'create' => 'payables.bills.create',
                        'edit' => 'payables.bills.edit',
                        'delete' => 'payables.bills.delete',
                        'approve' => 'payables.bills.approve',
                    ],
                    'extra' => [
                        ['label' => 'Post to AP', 'permission' => 'payables.bills.post'],
                        ['label' => 'Cancel', 'permission' => 'payables.bills.cancel'],
                        ['label' => 'Credit note', 'permission' => 'payables.bills.credit_note'],
                    ],
                ],
                'payables_payments' => [
                    'label' => 'Supplier Payments',
                    'permissions' => [
                        'view' => 'payables.payments.view',
                        'create' => 'payables.payments.create',
                        'edit' => 'payables.payments.edit',
                        'delete' => 'payables.payments.delete',
                    ],
                    'extra' => [
                        ['label' => 'Post', 'permission' => 'payables.payments.post'],
                        ['label' => 'Cancel', 'permission' => 'payables.payments.cancel'],
                    ],
                ],
                'payables_reports' => [
                    'label' => 'Payables Reports',
                    'permissions' => [
                        'view' => 'payables.ledger.view',
                    ],
                    'extra' => [
                        ['label' => 'Supplier statement', 'permission' => 'payables.statement.view'],
                        ['label' => 'Aging analysis', 'permission' => 'payables.aging.view'],
                    ],
                ],
                'tax_codes' => [
                    'label' => 'Tax Codes & Rates',
                    'permissions' => [
                        'view' => 'tax.codes.view',
                    ],
                    'extra' => [
                        ['label' => 'Manage codes & rates', 'permission' => 'tax.codes.manage'],
                    ],
                ],
                'tax_reports' => [
                    'label' => 'Tax Reports',
                    'permissions' => [
                        'view' => 'tax.reports.view',
                    ],
                ],
                'tax_ledger' => [
                    'label' => 'Tax Ledger',
                    'permissions' => [
                        'view' => 'tax.ledger.view',
                    ],
                ],
                'tax_returns' => [
                    'label' => 'Tax Returns',
                    'permissions' => [
                        'view' => 'tax.returns.manage',
                    ],
                ],
                'tax_periods' => [
                    'label' => 'Tax Periods',
                    'permissions' => [
                        'view' => 'tax.periods.view',
                    ],
                ],
                'tax_audit' => [
                    'label' => 'Tax Audit Trail',
                    'permissions' => [
                        'view' => 'tax.audit.view',
                    ],
                ],
            ],
        ],

        'communications' => [
            'label' => 'Communications',
            'entities' => [
                'templates' => [
                    'label' => 'Communication Templates',
                    'permissions' => [
                        'view' => 'communications.templates.view',
                        'create' => 'communications.templates.create',
                        'edit' => 'communications.templates.edit',
                    ],
                    'extra' => [
                        ['label' => 'View version history', 'permission' => 'communications.templates.version_view'],
                        ['label' => 'Restore template versions', 'permission' => 'communications.templates.restore'],
                    ],
                ],
                'notifications' => [
                    'label' => 'Notification Center',
                    'permissions' => [
                        'view' => 'communications.notifications.view',
                        'edit' => 'communications.notifications.manage',
                    ],
                    'extra' => [
                        ['label' => 'Administer all user notifications', 'permission' => 'communications.notifications.admin'],
                    ],
                ],
                'communication_logs' => [
                    'label' => 'Communication Logs',
                    'permissions' => [
                        'view' => 'communications.logs.view',
                    ],
                    'extra' => [
                        ['label' => 'Audit delivery events', 'permission' => 'communications.logs.audit'],
                        ['label' => 'Export logs', 'permission' => 'communications.logs.export'],
                        ['label' => 'Administer all logs', 'permission' => 'communications.logs.admin'],
                    ],
                ],
                'sms' => [
                    'label' => 'Bulk SMS',
                    'permissions' => [
                        'view' => 'communications.sms.view',
                        'create' => 'communications.sms.send',
                        'approve' => 'communications.sms.approve',
                    ],
                    'extra' => [
                        ['label' => 'Schedule campaigns', 'permission' => 'communications.sms.schedule'],
                        ['label' => 'SMS audit & credits', 'permission' => 'communications.sms.audit'],
                    ],
                ],
                'whatsapp' => [
                    'label' => 'WhatsApp',
                    'permissions' => [
                        'view' => 'communications.whatsapp.view',
                        'create' => 'communications.whatsapp.send',
                        'edit' => 'communications.whatsapp.manage',
                    ],
                    'extra' => [
                        ['label' => 'Delivery audit', 'permission' => 'communications.whatsapp.audit'],
                    ],
                ],
                'email' => [
                    'label' => 'Email Center',
                    'permissions' => [
                        'view' => 'communications.email.view',
                        'create' => 'communications.email.send',
                        'edit' => 'communications.email.manage',
                    ],
                    'extra' => [
                        ['label' => 'Schedule sends', 'permission' => 'communications.email.schedule'],
                        ['label' => 'Delivery audit', 'permission' => 'communications.email.audit'],
                    ],
                ],
                'inbox' => [
                    'label' => 'Shared Inbox',
                    'permissions' => [
                        'view' => 'communications.inbox.view',
                        'create' => 'communications.inbox.reply',
                        'edit' => 'communications.inbox.assign',
                    ],
                    'extra' => [
                        ['label' => 'Close conversations', 'permission' => 'communications.inbox.close'],
                        ['label' => 'Internal notes', 'permission' => 'communications.inbox.notes'],
                        ['label' => 'Attachments', 'permission' => 'communications.inbox.attachments'],
                        ['label' => 'Inbox audit', 'permission' => 'communications.inbox.audit'],
                        ['label' => 'Escalate conversations', 'permission' => 'communications.inbox.escalate'],
                        ['label' => 'Executive inbox view', 'permission' => 'communications.inbox.executive'],
                        ['label' => 'Inbox admin', 'permission' => 'communications.inbox.admin'],
                    ],
                ],
            ],
        ],

        'commercial' => [
            'label' => 'Commercial',
            'entities' => [
                'price_books' => [
                    'label' => 'Price Books',
                    'permissions' => [
                        'view' => 'commercial.price_books.view',
                        'create' => 'commercial.price_books.create',
                        'edit' => 'commercial.price_books.edit',
                        'delete' => 'commercial.price_books.delete',
                    ],
                ],
                'approvals' => [
                    'label' => 'Approvals Queue',
                    'permissions' => [
                        'view' => 'commercial.approvals.view',
                    ],
                    'extra' => [
                        ['label' => 'Take action', 'permission' => 'commercial.approvals.action'],
                    ],
                ],
                'complaints' => [
                    'label' => 'Complaints',
                    'permissions' => [
                        'view' => 'commercial.complaints.view',
                        'create' => 'commercial.complaints.create',
                        'edit' => 'commercial.complaints.edit',
                    ],
                    'extra' => [
                        ['label' => 'Resolve', 'permission' => 'commercial.complaints.resolve'],
                    ],
                ],
                'support_tickets' => [
                    'label' => 'Support Tickets',
                    'permissions' => [
                        'view' => 'commercial.tickets.view',
                        'create' => 'commercial.tickets.create',
                        'edit' => 'commercial.tickets.edit',
                    ],
                    'extra' => [
                        ['label' => 'Assign', 'permission' => 'commercial.tickets.assign'],
                        ['label' => 'Resolve', 'permission' => 'commercial.tickets.resolve'],
                    ],
                ],
                'sales_reports' => [
                    'label' => 'Sales Reports',
                    'permissions' => [
                        'view' => 'commercial.reports.sales.view',
                    ],
                    'extra' => [
                        ['label' => 'Export', 'permission' => 'commercial.reports.sales.export'],
                        ['label' => 'Manage', 'permission' => 'commercial.reports.sales.manage'],
                    ],
                ],
                'quotation_reports' => [
                    'label' => 'Quotation Reports',
                    'permissions' => [
                        'view' => 'commercial.reports.quotations.view',
                    ],
                    'extra' => [
                        ['label' => 'Export', 'permission' => 'commercial.reports.quotations.export'],
                    ],
                ],
                'sales_order_reports' => [
                    'label' => 'Sales Order Reports',
                    'permissions' => [
                        'view' => 'commercial.reports.sales_orders.view',
                    ],
                    'extra' => [
                        ['label' => 'Export', 'permission' => 'commercial.reports.sales_orders.export'],
                    ],
                ],
                'customer_reports' => [
                    'label' => 'Customer Reports',
                    'permissions' => [
                        'view' => 'commercial.reports.customers.view',
                    ],
                    'extra' => [
                        ['label' => 'Export', 'permission' => 'commercial.reports.customers.export'],
                    ],
                ],
                'artwork_reports' => [
                    'label' => 'Artwork Reports',
                    'permissions' => [
                        'view' => 'commercial.reports.artwork.view',
                    ],
                    'extra' => [
                        ['label' => 'Export', 'permission' => 'commercial.reports.artwork.export'],
                    ],
                ],
                'conversion_reports' => [
                    'label' => 'Conversion Reports',
                    'permissions' => [
                        'view' => 'commercial.reports.conversion.view',
                    ],
                    'extra' => [
                        ['label' => 'Export', 'permission' => 'commercial.reports.conversion.export'],
                    ],
                ],
                'report_exports' => [
                    'label' => 'Report Export Framework',
                    'permissions' => [
                        'export' => 'commercial.reports.export',
                        'view' => 'commercial.reports.exports.view',
                    ],
                    'extra' => [
                        ['label' => 'Download', 'permission' => 'commercial.reports.exports.download'],
                    ],
                ],
            ],
        ],

        'reports_intelligence' => [
            'label' => 'Reports & Intelligence',
            'entities' => [
                'reports' => [
                    'label' => 'Module Reports',
                    'permissions' => [
                        'view' => 'reports.view',
                    ],
                    'extra' => [
                        ['label' => 'Export', 'permission' => 'reports.export'],
                    ],
                ],
                'kpi_center' => [
                    'label' => 'KPI Center',
                    'permissions' => [
                        'view' => 'kpi.view',
                    ],
                    'extra' => [
                        ['label' => 'Manage KPI settings', 'permission' => 'kpi.manage'],
                    ],
                ],
                'inventory_360' => [
                    'label' => 'Inventory 360',
                    'permissions' => ['view' => 'intelligence.inventory.view'],
                ],
                'inventory_reports' => [
                    'label' => 'Inventory Reports Center',
                    'permissions' => [
                        'view' => 'reports.inventory.view',
                    ],
                    'extra' => [
                        ['label' => 'Export', 'permission' => 'reports.inventory.export'],
                    ],
                ],
                'costing_reports' => [
                    'label' => 'Costing Reports Center',
                    'permissions' => [
                        'view' => 'reports.costing.view',
                    ],
                    'extra' => [
                        ['label' => 'Export', 'permission' => 'reports.costing.export'],
                    ],
                ],
                'procurement_reports' => [
                    'label' => 'Procurement Reports Center',
                    'permissions' => [
                        'view' => 'reports.procurement.view',
                    ],
                    'extra' => [
                        ['label' => 'Export', 'permission' => 'reports.procurement.export'],
                    ],
                ],
                'procurement_360' => [
                    'label' => 'Procurement 360',
                    'permissions' => ['view' => 'intelligence.vendor.view'],
                ],
                'branch_360' => [
                    'label' => 'Branch 360',
                    'permissions' => ['view' => 'intelligence.branch.view'],
                ],
                'production_360' => [
                    'label' => 'Production 360',
                    'permissions' => ['view' => 'intelligence.production.view'],
                ],
                'financial_360' => [
                    'label' => 'Financial 360',
                    'permissions' => ['view' => 'intelligence.financial.view'],
                ],
                'commercial_360' => [
                    'label' => 'Commercial 360',
                    'permissions' => ['view' => 'intelligence.commercial.view'],
                ],
                'asset_360' => [
                    'label' => 'Asset Intelligence',
                    'permissions' => ['view' => 'intelligence.assets.view'],
                ],
            ],
        ],

    ],

];
