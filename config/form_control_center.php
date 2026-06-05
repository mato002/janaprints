<?php

return [

    'categories' => [
        'commercial' => [
            'label' => 'Commercial',
            'icon' => 'shopping-bag',
            'description' => 'CRM, sales, quotations, POS, and customer-facing capture forms.',
        ],
        'production' => [
            'label' => 'Production',
            'icon' => 'chip',
            'description' => 'Job cards, shop floor, and production workflow forms.',
        ],
        'supply_chain' => [
            'label' => 'Supply Chain',
            'icon' => 'truck',
            'description' => 'Inventory, warehousing, stock movement, and procurement forms.',
        ],
        'accounting' => [
            'label' => 'Accounting',
            'icon' => 'currency-dollar',
            'description' => 'Invoices, payments, journals, and finance capture forms.',
        ],
        'hr' => [
            'label' => 'HR',
            'icon' => 'identification',
            'description' => 'Employee, payroll, and people operations forms.',
        ],
        'administration' => [
            'label' => 'Administration',
            'icon' => 'office-building',
            'description' => 'Platform, company, and governance administration forms.',
        ],
    ],

    /*
    | Maps registry form keys to control-center metadata.
    | Category must match a key in categories above.
    */
    'form_meta' => [
        'customer' => ['category' => 'commercial', 'icon' => 'users', 'keywords' => ['crm', 'client']],
        'lead' => ['category' => 'commercial', 'icon' => 'sparkles', 'keywords' => ['crm', 'pipeline']],
        'quotation' => ['category' => 'commercial', 'icon' => 'document-text', 'keywords' => ['sales', 'quote']],
        'artwork' => ['category' => 'commercial', 'icon' => 'color-swatch', 'keywords' => ['design', 'creative']],
        'sales_order' => ['category' => 'commercial', 'icon' => 'shopping-cart', 'keywords' => ['sales', 'order']],
        'commercial_price_book.create' => ['category' => 'commercial', 'icon' => 'tag', 'keywords' => ['pricing', 'catalog']],
        'activity.create' => ['category' => 'commercial', 'icon' => 'calendar', 'keywords' => ['crm', 'follow-up']],
        'segment.create' => ['category' => 'commercial', 'icon' => 'collection', 'keywords' => ['crm', 'segmentation']],
        'commercial_complaint.create' => ['category' => 'commercial', 'icon' => 'exclamation', 'keywords' => ['support', 'complaint']],
        'commercial_support_ticket.create' => ['category' => 'commercial', 'icon' => 'inbox', 'keywords' => ['helpdesk', 'ticket']],
        'pos_sale.create' => ['category' => 'commercial', 'icon' => 'credit-card', 'keywords' => ['pos', 'retail', 'checkout']],

        'inventory_item' => ['category' => 'supply_chain', 'icon' => 'cube', 'keywords' => ['stock', 'sku', 'catalogue']],
        'warehouse.create' => ['category' => 'supply_chain', 'icon' => 'building', 'keywords' => ['store', 'location']],
        'warehouse.edit' => ['category' => 'supply_chain', 'icon' => 'building', 'keywords' => ['store', 'location']],
        'warehouse.manager_assignment' => ['category' => 'supply_chain', 'icon' => 'users', 'keywords' => ['manager', 'store']],
        'stock_issue.create' => ['category' => 'supply_chain', 'icon' => 'switch-horizontal', 'keywords' => ['issue', 'dispatch']],
        'store_transfer.create' => ['category' => 'supply_chain', 'icon' => 'truck', 'keywords' => ['transfer', 'inter-store']],
        'stock_receipt.create' => ['category' => 'supply_chain', 'icon' => 'archive', 'keywords' => ['receiving', 'grn']],
        'stock_adjustment.create' => ['category' => 'supply_chain', 'icon' => 'refresh', 'keywords' => ['adjustment', 'stocktake']],
    ],

    'planned_forms' => [
        [
            'id' => 'planned_production_job_card',
            'category' => 'production',
            'label' => 'Job Cards',
            'description' => 'Shop floor job card and production routing fields.',
            'icon' => 'chip',
            'keywords' => ['manufacturing', 'routing', 'work order'],
        ],
        [
            'id' => 'planned_production_materials',
            'category' => 'production',
            'label' => 'Material Consumption',
            'description' => 'Bill of materials and consumption capture fields.',
            'icon' => 'cube',
            'keywords' => ['bom', 'consumption', 'materials'],
        ],
        [
            'id' => 'planned_procurement_po',
            'category' => 'supply_chain',
            'label' => 'Purchase Orders',
            'description' => 'Purchase order header and line capture fields.',
            'icon' => 'document-text',
            'keywords' => ['procurement', 'vendor', 'po'],
        ],
        [
            'id' => 'planned_procurement_vendor',
            'category' => 'supply_chain',
            'label' => 'Supplier Onboarding',
            'description' => 'Vendor master and compliance capture fields.',
            'icon' => 'truck',
            'keywords' => ['supplier', 'vendor', 'onboarding'],
        ],
        [
            'id' => 'planned_accounting_invoice',
            'category' => 'accounting',
            'label' => 'Invoices',
            'description' => 'Customer invoice header and line capture fields.',
            'icon' => 'receipt-tax',
            'keywords' => ['billing', 'ar', 'invoice'],
        ],
        [
            'id' => 'planned_accounting_payment',
            'category' => 'accounting',
            'label' => 'Payments',
            'description' => 'Payment receipt and allocation capture fields.',
            'icon' => 'cash',
            'keywords' => ['receipt', 'collection', 'payment'],
        ],
        [
            'id' => 'planned_hr_employee',
            'category' => 'hr',
            'label' => 'Employee Records',
            'description' => 'Employee master data and onboarding fields.',
            'icon' => 'identification',
            'keywords' => ['staff', 'personnel', 'onboarding'],
        ],
        [
            'id' => 'planned_hr_leave',
            'category' => 'hr',
            'label' => 'Leave Requests',
            'description' => 'Leave application and approval capture fields.',
            'icon' => 'calendar',
            'keywords' => ['absence', 'time off', 'leave'],
        ],
        [
            'id' => 'planned_admin_company',
            'category' => 'administration',
            'label' => 'Company Profile',
            'description' => 'Legal entity and branding administration fields.',
            'icon' => 'office-building',
            'keywords' => ['company', 'entity', 'branding'],
        ],
        [
            'id' => 'planned_admin_users',
            'category' => 'administration',
            'label' => 'User Provisioning',
            'description' => 'User account and access provisioning fields.',
            'icon' => 'shield-check',
            'keywords' => ['users', 'access', 'provisioning'],
        ],
    ],

];
