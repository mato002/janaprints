<?php

use App\Enums\GlAccountTypeCode;

return [

    'accounts' => [
        'current_year_earnings' => '3300',
        'retained_earnings' => '3200',
    ],

    'pl_account_types' => [
        GlAccountTypeCode::Revenue->value,
        GlAccountTypeCode::CostOfSales->value,
        GlAccountTypeCode::Expense->value,
    ],

];
