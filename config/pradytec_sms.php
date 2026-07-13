<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Pradytec CRM SMS wallet (https://crm.pradytecai.com/api-documentation)
    |--------------------------------------------------------------------------
    | Real SMS credits live on the CRM wallet. Top-ups use M-Pesa STK via
    | POST /api/{client_id}/wallet/topup — not local balance inventing.
    */

    'api_url' => rtrim((string) env('PRADYTEC_SMS_API_URL', env('BULKSMS_API_URL', 'https://crm.pradytecai.com/api')), '/'),
    'client_id' => (string) env('PRADYTEC_SMS_CLIENT_ID', env('BULKSMS_CLIENT_ID', '1')),
    'api_key' => (string) env('PRADYTEC_SMS_API_KEY', env('BULKSMS_API_KEY', '')),
    'sender_id' => (string) env('PRADYTEC_SMS_SENDER_ID', env('BULKSMS_SENDER_ID', '')),

    'balance_path' => (string) env('PRADYTEC_SMS_BALANCE_PATH', 'client/balance'),
    'topup_path' => (string) env('PRADYTEC_SMS_TOPUP_PATH', 'wallet/topup'),
    'transactions_path' => (string) env('PRADYTEC_SMS_TRANSACTIONS_PATH', 'wallet/transactions'),

    'currency' => (string) env('PRADYTEC_SMS_CURRENCY', 'KES'),
    'min_topup_amount' => (float) env('PRADYTEC_SMS_MIN_TOPUP', 10),
    'max_topup_amount' => (float) env('PRADYTEC_SMS_MAX_TOPUP', 50000),
    'pending_timeout_seconds' => (int) env('PRADYTEC_SMS_TOPUP_TIMEOUT', 120),
    'timeout_seconds' => (int) env('PRADYTEC_SMS_TIMEOUT', 25),
    'verify_ssl' => filter_var(env('PRADYTEC_SMS_VERIFY_SSL', true), FILTER_VALIDATE_BOOL),

];
