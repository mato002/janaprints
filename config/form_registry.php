<?php

return [

    'forms' => [

        'customer' => [
            'label' => 'Customers',
            'description' => 'Customer master data fields shown on create and edit forms.',
            'fields' => [
                'company_name' => ['label' => 'Company name', 'type' => 'text', 'required' => false, 'visible' => true, 'sort_order' => 1],
                'customer_type' => ['label' => 'Type', 'type' => 'select', 'required' => true, 'visible' => true, 'sort_order' => 2],
                'contact_person' => ['label' => 'Contact person', 'type' => 'text', 'required' => false, 'visible' => true, 'sort_order' => 3],
                'phone' => ['label' => 'Phone', 'type' => 'text', 'required' => false, 'visible' => true, 'sort_order' => 4],
                'email' => ['label' => 'Email', 'type' => 'email', 'required' => false, 'visible' => true, 'sort_order' => 5],
                'kra_pin' => ['label' => 'KRA PIN', 'type' => 'text', 'required' => false, 'visible' => true, 'sort_order' => 6],
                'website' => ['label' => 'Website', 'type' => 'text', 'required' => false, 'visible' => true, 'sort_order' => 7],
                'physical_address' => ['label' => 'Physical address', 'type' => 'textarea', 'required' => false, 'visible' => true, 'sort_order' => 8],
                'city' => ['label' => 'City', 'type' => 'text', 'required' => false, 'visible' => true, 'sort_order' => 9],
                'credit_limit' => ['label' => 'Credit limit', 'type' => 'number', 'required' => false, 'visible' => true, 'sort_order' => 10, 'default' => 0],
                'payment_terms' => ['label' => 'Payment terms', 'type' => 'text', 'required' => false, 'visible' => true, 'sort_order' => 11],
                'status' => ['label' => 'Status', 'type' => 'select', 'required' => true, 'visible' => true, 'sort_order' => 12],
                'notes' => ['label' => 'Notes', 'type' => 'textarea', 'required' => false, 'visible' => true, 'sort_order' => 13],
                'segment_ids' => ['label' => 'Segments', 'type' => 'checkbox_group', 'required' => false, 'visible' => true, 'sort_order' => 14],
            ],
        ],

        'lead' => [
            'label' => 'Leads',
            'description' => 'Lead capture and pipeline fields.',
            'fields' => [
                'lead_name' => ['label' => 'Lead name', 'type' => 'text', 'required' => true, 'visible' => true, 'sort_order' => 1],
                'company_name' => ['label' => 'Company name', 'type' => 'text', 'required' => false, 'visible' => true, 'sort_order' => 2],
                'lead_source_id' => ['label' => 'Source', 'type' => 'select', 'required' => false, 'visible' => true, 'sort_order' => 3],
                'stage_id' => ['label' => 'Stage', 'type' => 'select', 'required' => false, 'visible' => true, 'sort_order' => 4],
                'estimated_value' => ['label' => 'Estimated value', 'type' => 'number', 'required' => false, 'visible' => true, 'sort_order' => 5, 'default' => 0],
                'probability' => ['label' => 'Probability %', 'type' => 'number', 'required' => false, 'visible' => true, 'sort_order' => 6, 'default' => 0],
                'status' => ['label' => 'Status', 'type' => 'select', 'required' => true, 'visible' => true, 'sort_order' => 7],
                'assigned_to' => ['label' => 'Assigned to', 'type' => 'select', 'required' => false, 'visible' => true, 'sort_order' => 8],
                'expected_close_date' => ['label' => 'Expected close date', 'type' => 'date', 'required' => false, 'visible' => true, 'sort_order' => 9],
                'notes' => ['label' => 'Notes', 'type' => 'textarea', 'required' => false, 'visible' => true, 'sort_order' => 10],
            ],
        ],

        'quotation' => [
            'label' => 'Quotations',
            'description' => 'Quotation header fields on create and edit.',
            'fields' => [
                'customer_id' => ['label' => 'Customer', 'type' => 'select', 'required' => true, 'visible' => true, 'sort_order' => 1],
                'lead_id' => ['label' => 'Lead (optional)', 'type' => 'select', 'required' => false, 'visible' => true, 'sort_order' => 2],
                'quotation_date' => ['label' => 'Quotation date', 'type' => 'date', 'required' => true, 'visible' => true, 'sort_order' => 3],
                'valid_until' => ['label' => 'Valid until', 'type' => 'date', 'required' => false, 'visible' => true, 'sort_order' => 4, 'default' => null],
                'currency' => ['label' => 'Currency', 'type' => 'text', 'required' => true, 'visible' => true, 'sort_order' => 5, 'default' => 'KES'],
                'notes' => ['label' => 'Notes', 'type' => 'textarea', 'required' => false, 'visible' => true, 'sort_order' => 6],
            ],
        ],

        'artwork' => [
            'label' => 'Artwork',
            'description' => 'Artwork request form fields.',
            'fields' => [
                'customer_id' => ['label' => 'Customer', 'type' => 'select', 'required' => true, 'visible' => true, 'sort_order' => 1],
                'quotation_id' => ['label' => 'Quotation', 'type' => 'select', 'required' => false, 'visible' => true, 'sort_order' => 2],
                'title' => ['label' => 'Title', 'type' => 'text', 'required' => true, 'visible' => true, 'sort_order' => 3],
                'description' => ['label' => 'Description', 'type' => 'textarea', 'required' => false, 'visible' => true, 'sort_order' => 4],
                'priority' => ['label' => 'Priority', 'type' => 'select', 'required' => true, 'visible' => true, 'sort_order' => 5],
                'due_date' => ['label' => 'Due date', 'type' => 'date', 'required' => false, 'visible' => true, 'sort_order' => 6],
            ],
        ],

        'sales_order' => [
            'label' => 'Sales Orders',
            'description' => 'Sales order creation from quotation or direct customer order.',
            'fields' => [
                'quotation_id' => ['label' => 'Quotation', 'type' => 'select', 'required' => true, 'visible' => true, 'sort_order' => 1],
            ],
        ],

        'inventory_item' => [
            'label' => 'Inventory',
            'description' => 'Inventory item master fields.',
            'fields' => [
                'inventory_category_id' => ['label' => 'Category', 'type' => 'select', 'required' => true, 'visible' => true, 'sort_order' => 1],
                'unit_of_measure_id' => ['label' => 'Unit', 'type' => 'select', 'required' => true, 'visible' => true, 'sort_order' => 2],
                'sku' => ['label' => 'SKU', 'type' => 'text', 'required' => false, 'visible' => false, 'sort_order' => 3],
                'item_name' => ['label' => 'Name', 'type' => 'text', 'required' => true, 'visible' => true, 'sort_order' => 4],
                'description' => ['label' => 'Description', 'type' => 'textarea', 'required' => false, 'visible' => true, 'sort_order' => 5],
                'reorder_level' => ['label' => 'Reorder level', 'type' => 'number', 'required' => true, 'visible' => true, 'sort_order' => 6, 'default' => 0],
                'reorder_quantity' => ['label' => 'Reorder qty', 'type' => 'number', 'required' => true, 'visible' => true, 'sort_order' => 7, 'default' => 0],
                'standard_cost' => ['label' => 'Standard cost', 'type' => 'number', 'required' => true, 'visible' => true, 'sort_order' => 8, 'default' => 0],
            ],
        ],

        'warehouse.create' => [
            'label' => 'Warehouse (create)',
            'description' => 'Fields on the new warehouse form.',
            'fields' => [
                'name' => ['label' => 'Warehouse name', 'type' => 'text', 'required' => true, 'visible' => true, 'sort_order' => 1],
                'code' => ['label' => 'Code', 'type' => 'text', 'required' => false, 'visible' => true, 'sort_order' => 2],
                'branch_id' => ['label' => 'Branch', 'type' => 'select', 'required' => false, 'visible' => false, 'sort_order' => 3],
                'is_active' => ['label' => 'Status', 'type' => 'checkbox', 'required' => false, 'visible' => true, 'sort_order' => 4],
                'location' => ['label' => 'Location', 'type' => 'text', 'required' => false, 'visible' => true, 'sort_order' => 5],
                'manager' => ['label' => 'Manager', 'type' => 'select', 'required' => false, 'visible' => false, 'sort_order' => 6],
                'description' => ['label' => 'Notes', 'type' => 'textarea', 'required' => false, 'visible' => true, 'sort_order' => 7],
            ],
        ],

        'warehouse.edit' => [
            'label' => 'Warehouse (edit)',
            'description' => 'Fields on the warehouse edit form.',
            'fields' => [
                'name' => ['label' => 'Warehouse name', 'type' => 'text', 'required' => true, 'visible' => true, 'sort_order' => 1],
                'code' => ['label' => 'Code', 'type' => 'text', 'required' => true, 'visible' => true, 'sort_order' => 2],
                'branch_id' => ['label' => 'Branch', 'type' => 'select', 'required' => false, 'visible' => false, 'sort_order' => 3],
                'is_active' => ['label' => 'Status', 'type' => 'checkbox', 'required' => false, 'visible' => true, 'sort_order' => 4],
                'location' => ['label' => 'Location', 'type' => 'text', 'required' => false, 'visible' => true, 'sort_order' => 5],
                'manager' => ['label' => 'Manager', 'type' => 'select', 'required' => false, 'visible' => false, 'sort_order' => 6],
                'description' => ['label' => 'Notes', 'type' => 'textarea', 'required' => false, 'visible' => true, 'sort_order' => 7],
            ],
        ],

        'warehouse.manager_assignment' => [
            'label' => 'Warehouse managers',
            'description' => 'Store manager assignment for a warehouse.',
            'fields' => [
                'manager_ids' => ['label' => 'Managers', 'type' => 'checkbox', 'required' => false, 'visible' => true, 'sort_order' => 1],
            ],
        ],

        'stock_issue.create' => [
            'label' => 'Stock issue (create)',
            'description' => 'Stock issue draft header and line fields.',
            'fields' => [
                'warehouse_id' => ['label' => 'Source warehouse', 'type' => 'select', 'required' => true, 'visible' => true, 'sort_order' => 1],
                'issue_date' => ['label' => 'Issue date', 'type' => 'date', 'required' => true, 'visible' => true, 'sort_order' => 2],
                'destination' => ['label' => 'Reason / destination', 'type' => 'select', 'required' => true, 'visible' => true, 'sort_order' => 3],
                'to_warehouse_id' => ['label' => 'Destination warehouse', 'type' => 'select', 'required' => false, 'visible' => true, 'sort_order' => 4],
                'inventory_item_id' => ['label' => 'Item', 'type' => 'select', 'required' => true, 'visible' => true, 'sort_order' => 5],
                'quantity' => ['label' => 'Quantity', 'type' => 'number', 'required' => true, 'visible' => true, 'sort_order' => 6],
                'unit_cost' => ['label' => 'Unit cost', 'type' => 'number', 'required' => true, 'visible' => true, 'sort_order' => 7],
                'notes' => ['label' => 'Notes', 'type' => 'textarea', 'required' => false, 'visible' => true, 'sort_order' => 8],
            ],
        ],

        'store_transfer.create' => [
            'label' => 'Store transfer (create)',
            'description' => 'Inter-store transfer draft fields.',
            'fields' => [
                'warehouse_id' => ['label' => 'Source warehouse', 'type' => 'select', 'required' => true, 'visible' => true, 'sort_order' => 1],
                'to_warehouse_id' => ['label' => 'Destination warehouse', 'type' => 'select', 'required' => true, 'visible' => true, 'sort_order' => 2],
                'issue_date' => ['label' => 'Transfer date', 'type' => 'date', 'required' => true, 'visible' => true, 'sort_order' => 3],
                'inventory_item_id' => ['label' => 'Item', 'type' => 'select', 'required' => true, 'visible' => true, 'sort_order' => 4],
                'quantity' => ['label' => 'Quantity', 'type' => 'number', 'required' => true, 'visible' => true, 'sort_order' => 5],
                'unit_cost' => ['label' => 'Unit cost', 'type' => 'number', 'required' => false, 'visible' => true, 'sort_order' => 6],
                'notes' => ['label' => 'Notes', 'type' => 'textarea', 'required' => false, 'visible' => true, 'sort_order' => 7],
            ],
        ],

        'stock_receipt.create' => [
            'label' => 'Stock receipt (create)',
            'description' => 'Goods receiving draft fields.',
            'fields' => [
                'warehouse_id' => ['label' => 'Warehouse', 'type' => 'select', 'required' => true, 'visible' => true, 'sort_order' => 1],
                'source' => ['label' => 'Source', 'type' => 'select', 'required' => true, 'visible' => true, 'sort_order' => 2],
                'receipt_date' => ['label' => 'Receipt date', 'type' => 'date', 'required' => true, 'visible' => true, 'sort_order' => 3],
                'inventory_item_id' => ['label' => 'Item', 'type' => 'select', 'required' => true, 'visible' => true, 'sort_order' => 4],
                'quantity' => ['label' => 'Quantity', 'type' => 'number', 'required' => true, 'visible' => true, 'sort_order' => 5],
                'unit_cost' => ['label' => 'Unit cost', 'type' => 'number', 'required' => true, 'visible' => true, 'sort_order' => 6],
                'notes' => ['label' => 'Notes', 'type' => 'textarea', 'required' => false, 'visible' => false, 'sort_order' => 7],
            ],
        ],

        'commercial_price_book.create' => [
            'label' => 'Commercial Price Books',
            'description' => 'Create commercial price book header fields.',
            'fields' => [
                'name' => ['label' => 'Name', 'type' => 'text', 'required' => true, 'visible' => true, 'sort_order' => 1],
                'code' => ['label' => 'Code', 'type' => 'text', 'required' => false, 'visible' => true, 'sort_order' => 2],
                'description' => ['label' => 'Description', 'type' => 'textarea', 'required' => false, 'visible' => true, 'sort_order' => 3],
                'currency' => ['label' => 'Currency', 'type' => 'text', 'required' => true, 'visible' => true, 'sort_order' => 4, 'default' => 'KES'],
                'branch_id' => ['label' => 'Branch', 'type' => 'select', 'required' => false, 'visible' => true, 'sort_order' => 5],
                'status' => ['label' => 'Status', 'type' => 'select', 'required' => true, 'visible' => true, 'sort_order' => 6],
                'starts_at' => ['label' => 'Starts at', 'type' => 'date', 'required' => false, 'visible' => true, 'sort_order' => 7],
                'ends_at' => ['label' => 'Ends at', 'type' => 'date', 'required' => false, 'visible' => true, 'sort_order' => 8],
                'is_default' => ['label' => 'Default price book', 'type' => 'checkbox', 'required' => false, 'visible' => true, 'sort_order' => 9],
            ],
        ],

        'activity.create' => [
            'label' => 'Commercial Activities',
            'description' => 'Customer and lead activity logging fields.',
            'fields' => [
                'customer_id' => ['label' => 'Customer', 'type' => 'select', 'required' => false, 'visible' => true, 'sort_order' => 1],
                'lead_id' => ['label' => 'Lead', 'type' => 'select', 'required' => false, 'visible' => true, 'sort_order' => 2],
                'activity_type' => ['label' => 'Activity type', 'type' => 'select', 'required' => true, 'visible' => true, 'sort_order' => 3],
                'status' => ['label' => 'Status', 'type' => 'select', 'required' => true, 'visible' => true, 'sort_order' => 4],
                'user_id' => ['label' => 'Assigned to', 'type' => 'select', 'required' => false, 'visible' => true, 'sort_order' => 5],
                'activity_at' => ['label' => 'Activity date', 'type' => 'datetime', 'required' => true, 'visible' => true, 'sort_order' => 6],
                'subject' => ['label' => 'Subject', 'type' => 'text', 'required' => true, 'visible' => true, 'sort_order' => 7],
                'description' => ['label' => 'Description', 'type' => 'textarea', 'required' => false, 'visible' => true, 'sort_order' => 8],
            ],
        ],

        'segment.create' => [
            'label' => 'Customer Segments',
            'description' => 'Customer segment definition fields.',
            'fields' => [
                'name' => ['label' => 'Name', 'type' => 'text', 'required' => true, 'visible' => true, 'sort_order' => 1],
                'code' => ['label' => 'Code', 'type' => 'text', 'required' => false, 'visible' => true, 'sort_order' => 2],
                'description' => ['label' => 'Description', 'type' => 'textarea', 'required' => false, 'visible' => true, 'sort_order' => 3],
                'is_active' => ['label' => 'Active', 'type' => 'checkbox', 'required' => false, 'visible' => true, 'sort_order' => 4],
            ],
        ],

        'commercial_complaint.create' => [
            'label' => 'Commercial Complaints',
            'description' => 'Customer complaint capture fields.',
            'fields' => [
                'customer_id' => ['label' => 'Customer', 'type' => 'select', 'required' => false, 'visible' => true, 'sort_order' => 1],
                'subject' => ['label' => 'Subject', 'type' => 'text', 'required' => true, 'visible' => true, 'sort_order' => 2],
                'description' => ['label' => 'Description', 'type' => 'textarea', 'required' => true, 'visible' => true, 'sort_order' => 3],
                'source' => ['label' => 'Source', 'type' => 'select', 'required' => true, 'visible' => true, 'sort_order' => 4],
                'priority' => ['label' => 'Priority', 'type' => 'select', 'required' => true, 'visible' => true, 'sort_order' => 5],
            ],
        ],

        'commercial_support_ticket.create' => [
            'label' => 'Support Tickets',
            'description' => 'Help desk ticket capture fields.',
            'fields' => [
                'customer_id' => ['label' => 'Customer', 'type' => 'select', 'required' => false, 'visible' => true, 'sort_order' => 1],
                'subject' => ['label' => 'Subject', 'type' => 'text', 'required' => true, 'visible' => true, 'sort_order' => 2],
                'description' => ['label' => 'Description', 'type' => 'textarea', 'required' => true, 'visible' => true, 'sort_order' => 3],
                'channel' => ['label' => 'Channel', 'type' => 'select', 'required' => true, 'visible' => true, 'sort_order' => 4],
                'priority' => ['label' => 'Priority', 'type' => 'select', 'required' => true, 'visible' => true, 'sort_order' => 5],
            ],
        ],

        'pos_sale.create' => [
            'label' => 'POS Counter Sales',
            'description' => 'Point-of-sale checkout header fields.',
            'fields' => [
                'customer_id' => ['label' => 'Customer', 'type' => 'select', 'required' => false, 'visible' => true, 'sort_order' => 1],
                'is_walk_in' => ['label' => 'Walk-in sale', 'type' => 'checkbox', 'required' => false, 'visible' => true, 'sort_order' => 2],
                'discount_amount' => ['label' => 'Discount', 'type' => 'number', 'required' => false, 'visible' => true, 'sort_order' => 3],
                'tax_amount' => ['label' => 'Tax', 'type' => 'number', 'required' => false, 'visible' => true, 'sort_order' => 4],
                'notes' => ['label' => 'Notes', 'type' => 'textarea', 'required' => false, 'visible' => true, 'sort_order' => 5],
            ],
        ],

        'stock_count.create' => [
            'label' => 'Stock count (create)',
            'description' => 'Physical stock count header fields.',
            'fields' => [
                'warehouse_id' => ['label' => 'Warehouse', 'type' => 'select', 'required' => true, 'visible' => true, 'sort_order' => 1],
                'count_type' => ['label' => 'Count type', 'type' => 'select', 'required' => true, 'visible' => true, 'sort_order' => 2],
                'count_date' => ['label' => 'Count date', 'type' => 'date', 'required' => true, 'visible' => true, 'sort_order' => 3],
                'notes' => ['label' => 'Notes', 'type' => 'textarea', 'required' => false, 'visible' => true, 'sort_order' => 4],
            ],
        ],

        'cycle_count_schedule.create' => [
            'label' => 'Cycle count schedule (create)',
            'description' => 'Cycle count schedule fields.',
            'fields' => [
                'warehouse_id' => ['label' => 'Warehouse', 'type' => 'select', 'required' => true, 'visible' => true, 'sort_order' => 1],
                'frequency' => ['label' => 'Frequency', 'type' => 'select', 'required' => true, 'visible' => true, 'sort_order' => 2],
                'next_count_date' => ['label' => 'Next count date', 'type' => 'date', 'required' => true, 'visible' => true, 'sort_order' => 3],
                'inventory_category_id' => ['label' => 'Category', 'type' => 'select', 'required' => false, 'visible' => true, 'sort_order' => 4],
                'responsible_user_id' => ['label' => 'Responsible user', 'type' => 'select', 'required' => true, 'visible' => true, 'sort_order' => 5],
                'notes' => ['label' => 'Notes', 'type' => 'textarea', 'required' => false, 'visible' => true, 'sort_order' => 6],
            ],
        ],

        'stock_adjustment.create' => [
            'label' => 'Stock adjustment (create)',
            'description' => 'Stock adjustment draft fields.',
            'fields' => [
                'warehouse_id' => ['label' => 'Warehouse', 'type' => 'select', 'required' => true, 'visible' => true, 'sort_order' => 1],
                'adjustment_date' => ['label' => 'Adjustment date', 'type' => 'date', 'required' => true, 'visible' => true, 'sort_order' => 2],
                'reason' => ['label' => 'Reason', 'type' => 'text', 'required' => true, 'visible' => true, 'sort_order' => 3],
                'inventory_item_id' => ['label' => 'Item', 'type' => 'select', 'required' => true, 'visible' => true, 'sort_order' => 4],
                'quantity' => ['label' => 'Quantity', 'type' => 'number', 'required' => true, 'visible' => true, 'sort_order' => 5],
                'unit_cost' => ['label' => 'Unit cost', 'type' => 'number', 'required' => true, 'visible' => true, 'sort_order' => 6],
                'notes' => ['label' => 'Notes', 'type' => 'textarea', 'required' => false, 'visible' => false, 'sort_order' => 7],
            ],
        ],

        'payroll_run.create' => [
            'label' => 'Payroll Runs',
            'description' => 'New payroll run header fields.',
            'fields' => [
                'branch_id' => ['label' => 'Branch', 'type' => 'select', 'required' => false, 'visible' => true, 'sort_order' => 1],
                'period_start' => ['label' => 'Period start', 'type' => 'date', 'required' => true, 'visible' => true, 'sort_order' => 2, 'default' => null],
                'period_end' => ['label' => 'Period end', 'type' => 'date', 'required' => true, 'visible' => true, 'sort_order' => 3, 'default' => null],
                'pay_date' => ['label' => 'Pay date', 'type' => 'date', 'required' => true, 'visible' => true, 'sort_order' => 4, 'default' => null],
                'notes' => ['label' => 'Notes', 'type' => 'textarea', 'required' => false, 'visible' => true, 'sort_order' => 5],
            ],
        ],

        'employee.create' => [
            'label' => 'Employee Records',
            'description' => 'Employee master data and onboarding fields.',
            'fields' => [
                'company_id' => ['label' => 'Company', 'type' => 'select', 'required' => true, 'visible' => true, 'sort_order' => 1],
                'branch_id' => ['label' => 'Branch', 'type' => 'select', 'required' => true, 'visible' => true, 'sort_order' => 2],
                'department_id' => ['label' => 'Department', 'type' => 'select', 'required' => false, 'visible' => true, 'sort_order' => 3],
                'first_name' => ['label' => 'First name', 'type' => 'text', 'required' => true, 'visible' => true, 'sort_order' => 4],
                'middle_name' => ['label' => 'Middle name', 'type' => 'text', 'required' => false, 'visible' => true, 'sort_order' => 5],
                'last_name' => ['label' => 'Last name', 'type' => 'text', 'required' => true, 'visible' => true, 'sort_order' => 6],
                'job_title_id' => ['label' => 'Job title', 'type' => 'select', 'required' => false, 'visible' => true, 'sort_order' => 7],
                'employment_status' => ['label' => 'Employment status', 'type' => 'select', 'required' => true, 'visible' => true, 'sort_order' => 8],
                'email' => ['label' => 'Email', 'type' => 'email', 'required' => true, 'visible' => true, 'sort_order' => 9],
                'phone' => ['label' => 'Phone', 'type' => 'text', 'required' => false, 'visible' => true, 'sort_order' => 10],
                'hire_date' => ['label' => 'Hire date', 'type' => 'date', 'required' => false, 'visible' => true, 'sort_order' => 11],
                'gender' => ['label' => 'Gender', 'type' => 'select', 'required' => false, 'visible' => true, 'sort_order' => 12],
                'date_of_birth' => ['label' => 'Date of birth', 'type' => 'date', 'required' => false, 'visible' => true, 'sort_order' => 13],
                'address' => ['label' => 'Address', 'type' => 'textarea', 'required' => false, 'visible' => true, 'sort_order' => 14],
                'national_id' => ['label' => 'National ID', 'type' => 'text', 'required' => false, 'visible' => true, 'sort_order' => 15],
                'kra_pin' => ['label' => 'KRA PIN', 'type' => 'text', 'required' => false, 'visible' => true, 'sort_order' => 16],
                'nhif_number' => ['label' => 'NHIF number', 'type' => 'text', 'required' => false, 'visible' => true, 'sort_order' => 17],
                'nssf_number' => ['label' => 'NSSF number', 'type' => 'text', 'required' => false, 'visible' => true, 'sort_order' => 18],
                'bank_name' => ['label' => 'Bank name', 'type' => 'text', 'required' => false, 'visible' => true, 'sort_order' => 19],
                'bank_account_number' => ['label' => 'Bank account', 'type' => 'text', 'required' => false, 'visible' => true, 'sort_order' => 20],
                'bank_branch_code' => ['label' => 'Bank branch code', 'type' => 'text', 'required' => false, 'visible' => true, 'sort_order' => 21],
                'emergency_contact_name' => ['label' => 'Emergency contact', 'type' => 'text', 'required' => false, 'visible' => true, 'sort_order' => 22],
                'emergency_contact_phone' => ['label' => 'Emergency phone', 'type' => 'text', 'required' => false, 'visible' => true, 'sort_order' => 23],
                'salary_template_id' => ['label' => 'Payroll class', 'type' => 'select', 'required' => false, 'visible' => true, 'sort_order' => 24],
            ],
        ],

        'leave_request.create' => [
            'label' => 'Leave Requests',
            'description' => 'Leave application and approval capture fields.',
            'fields' => [
                'employee_id' => ['label' => 'Employee', 'type' => 'select', 'required' => true, 'visible' => true, 'sort_order' => 1],
                'leave_type_id' => ['label' => 'Leave type', 'type' => 'select', 'required' => true, 'visible' => true, 'sort_order' => 2],
                'start_date' => ['label' => 'Start date', 'type' => 'date', 'required' => true, 'visible' => true, 'sort_order' => 3],
                'end_date' => ['label' => 'End date', 'type' => 'date', 'required' => true, 'visible' => true, 'sort_order' => 4],
                'is_half_day_start' => ['label' => 'Half day (start)', 'type' => 'checkbox', 'required' => false, 'visible' => true, 'sort_order' => 5],
                'is_half_day_end' => ['label' => 'Half day (end)', 'type' => 'checkbox', 'required' => false, 'visible' => true, 'sort_order' => 6],
                'reason' => ['label' => 'Reason', 'type' => 'textarea', 'required' => true, 'visible' => true, 'sort_order' => 7],
                'notes' => ['label' => 'Notes', 'type' => 'textarea', 'required' => false, 'visible' => false, 'sort_order' => 8],
            ],
        ],

    ],

    'field_aliases' => [
        'warehouse.create' => [
            'notes' => 'description',
        ],
        'warehouse.edit' => [
            'notes' => 'description',
        ],
    ],

];
