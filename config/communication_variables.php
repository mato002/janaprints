<?php

/**
 * Communication template placeholder definitions.
 * Keys are used without braces in validation; rendering uses {{key}} syntax.
 */

return [
    'customer_name' => [
        'label' => 'Customer Name',
        'description' => 'Full name or company name of the customer.',
        'sample' => 'John Kamau',
    ],
    'quotation_number' => [
        'label' => 'Quotation Number',
        'description' => 'Commercial quotation reference.',
        'sample' => 'QT-00125',
    ],
    'invoice_number' => [
        'label' => 'Invoice Number',
        'description' => 'Customer invoice reference.',
        'sample' => 'INV-00482',
    ],
    'job_number' => [
        'label' => 'Job Number',
        'description' => 'Production job card reference.',
        'sample' => 'JOB-1024',
    ],
    'payment_amount' => [
        'label' => 'Payment Amount',
        'description' => 'Formatted payment amount received.',
        'sample' => 'KES 15,000.00',
    ],
    'branch_name' => [
        'label' => 'Branch Name',
        'description' => 'Operating branch name.',
        'sample' => 'Nairobi Main',
    ],
    'company_name' => [
        'label' => 'Company Name',
        'description' => 'Legal or trading name of the tenant company.',
        'sample' => 'Jana Prints Ltd',
    ],
    'employee_name' => [
        'label' => 'Employee Name',
        'description' => 'Employee full name for HR communications.',
        'sample' => 'Jane Wanjiru',
    ],
    'approval_date' => [
        'label' => 'Approval Date',
        'description' => 'Date an approval or decision was recorded.',
        'sample' => '4 Jun 2026',
    ],
    'otp_code' => [
        'label' => 'OTP Code',
        'description' => 'One-time verification code.',
        'sample' => '482910',
    ],
    'reset_link' => [
        'label' => 'Password Reset Link',
        'description' => 'Secure password reset URL.',
        'sample' => 'https://erp.janaprints.example/reset/abc123',
    ],
    'role_name' => [
        'label' => 'Role Name',
        'description' => 'Assigned or changed role label.',
        'sample' => 'Branch Manager',
    ],
    'due_date' => [
        'label' => 'Due Date',
        'description' => 'Payment or collection due date.',
        'sample' => '15 Jun 2026',
    ],
    'order_number' => [
        'label' => 'Sales Order Number',
        'description' => 'Confirmed sales order reference.',
        'sample' => 'SO-00891',
    ],
];
