<?php

namespace App\Support\Dispatch;

use App\Support\Operator\OperatorModeKey;
use App\Support\Operator\ReturnsToOperatorDesk;
use Illuminate\Http\Request;

trait ReturnsToDispatchDesk
{
    use ReturnsToOperatorDesk;

    protected function wantsDispatchDeskReturn(?Request $request = null): bool
    {
        return $this->wantsOperatorDeskReturn(OperatorModeKey::Dispatch, $request);
    }

    /**
     * @param  array<string, mixed>  $params
     */
    protected function dispatchDeskUrl(array $params = []): string
    {
        return $this->operatorDeskUrl(OperatorModeKey::Dispatch, $params);
    }
}
