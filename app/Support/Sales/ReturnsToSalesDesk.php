<?php

namespace App\Support\Sales;

use App\Support\Operator\OperatorModeKey;
use App\Support\Operator\ReturnsToOperatorDesk;
use Illuminate\Http\Request;

trait ReturnsToSalesDesk
{
    use ReturnsToOperatorDesk;

    protected function operatorDeskModeKey(): OperatorModeKey
    {
        return OperatorModeKey::Sales;
    }

    protected function wantsSalesDeskReturn(?Request $request = null): bool
    {
        return $this->wantsOperatorDeskReturn($request);
    }

    /**
     * @param  array<string, mixed>  $params
     */
    protected function salesDeskUrl(array $params = []): string
    {
        return $this->operatorDeskUrl($params);
    }
}
