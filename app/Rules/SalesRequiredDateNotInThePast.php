<?php

namespace App\Rules;

use App\Models\Sales\SalesOrder;

class SalesRequiredDateNotInThePast extends DateNotInThePast
{
    public function __construct(?SalesOrder $existing = null)
    {
        parent::__construct($existing?->required_date);
    }
}
