<?php

namespace App\Support\Production;

use App\Models\Production\ProductionJobCard;
use App\Support\Operator\OperatorModeKey;
use App\Support\Operator\ReturnsToOperatorDesk;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

trait ReturnsToProductionFloor
{
    use ReturnsToOperatorDesk;

    protected function wantsProductionFloorReturn(?Request $request = null): bool
    {
        return $this->wantsOperatorDeskReturn(OperatorModeKey::Production, $request);
    }

    /**
     * @param  array<string, mixed>  $params
     */
    protected function productionFloorUrl(array $params = []): string
    {
        return $this->operatorDeskUrl(OperatorModeKey::Production, $params);
    }

    protected function redirectAfterProductionFloorAction(ProductionJobCard $jobCard, string $message): RedirectResponse
    {
        if ($this->wantsProductionFloorReturn()) {
            return redirect()->to($this->productionFloorUrl(['job' => $jobCard->public_id]))
                ->with('status', $message);
        }

        return back()->with('status', $message);
    }
}
