<?php

namespace App\Support\Inventory;

use App\Support\Operator\OperatorModeKey;
use App\Support\Operator\ReturnsToOperatorDesk;
use Illuminate\Http\Request;

trait ReturnsToStoreDesk
{
    use ReturnsToOperatorDesk;

    protected function wantsStoreDeskReturn(?Request $request = null): bool
    {
        return $this->wantsOperatorDeskReturn(OperatorModeKey::Storekeeper, $request);
    }

    /**
     * @param  array<string, mixed>  $params
     */
    protected function storeDeskUrl(array $params = []): string
    {
        return $this->operatorDeskUrl(OperatorModeKey::Storekeeper, $params);
    }
}
