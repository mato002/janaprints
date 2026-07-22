<?php

namespace App\Support\Sales;

use App\Support\Operator\OperatorModeKey;
use App\Support\Operator\ReturnsToOperatorDesk;
use Illuminate\Http\Request;

trait ReturnsToSalesDesk
{
    use ReturnsToOperatorDesk;

    protected function wantsSalesDeskReturn(?Request $request = null): bool
    {
        return $this->wantsOperatorDeskReturn(OperatorModeKey::Sales, $request);
    }

    /**
     * @param  array<string, mixed>  $params
     */
    protected function salesDeskUrl(array $params = []): string
    {
        return $this->operatorDeskUrl(OperatorModeKey::Sales, $params);
    }
}
