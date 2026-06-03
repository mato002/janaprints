<?php

return [

    'forms' => [

        'customer' => [
            'label' => 'Customers',
            'description' => 'Customer master data fields shown on create and edit forms.',
            'fields' => [
                'company_name' => ['label' => 'Company name', 'type' => 'text', 'required' => true, 'visible' => true, 'sort_order' => 1],
                'customer_type' => ['label' => 'Type', 'type' => 'select', 'required' => true, 'visible' => true, 'sort_order' => 2],
                'contact_person' => ['label' => 'Contact person', 'type' => 'text', 'required' => false, 'visible' => true, 'sort_order' => 3],
                'phone' => ['label' => 'Phone', 'type' => 'text', 'required' => false, 'visible' => true, 'sort_order' => 4],
                'email' => ['label' => 'Email', 'type' => 'email', 'required' => false, 'visible' => true, 'sort_order' => 5],
                'kra_pin' => ['label' => 'KRA PIN', 'type' => 'text', 'required' => false, 'visible' => true, 'sort_order' => 6],
                'website' => ['label' => 'Website', 'type' => 'text', 'required' => false, 'visible' => true, 'sort_order' => 7],
                'physical_address' => ['label' => 'Physical address', 'type' => 'textarea', 'required' => false, 'visible' => true, 'sort_order' => 8],
                'city' => ['label' => 'City', 'type' => 'text', 'required' => false, 'visible' => true, 'sort_order' => 9],
                'credit_limit' => ['label' => 'Credit limit', 'type' => 'number', 'required' => false, 'visible' => true, 'sort_order' => 10],
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
                'estimated_value' => ['label' => 'Estimated value', 'type' => 'number', 'required' => false, 'visible' => true, 'sort_order' => 5],
                'probability' => ['label' => 'Probability %', 'type' => 'number', 'required' => false, 'visible' => true, 'sort_order' => 6],
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
            'description' => 'Sales order creation from quotation.',
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
                'sku' => ['label' => 'SKU', 'type' => 'text', 'required' => true, 'visible' => true, 'sort_order' => 3],
                'item_name' => ['label' => 'Name', 'type' => 'text', 'required' => true, 'visible' => true, 'sort_order' => 4],
                'description' => ['label' => 'Description', 'type' => 'textarea', 'required' => false, 'visible' => true, 'sort_order' => 5],
                'reorder_level' => ['label' => 'Reorder level', 'type' => 'number', 'required' => true, 'visible' => true, 'sort_order' => 6, 'default' => 0],
                'reorder_quantity' => ['label' => 'Reorder qty', 'type' => 'number', 'required' => true, 'visible' => true, 'sort_order' => 7, 'default' => 0],
                'standard_cost' => ['label' => 'Standard cost', 'type' => 'number', 'required' => true, 'visible' => true, 'sort_order' => 8, 'default' => 0],
            ],
        ],

    ],

];
