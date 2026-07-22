<?php

namespace App\Support\Artwork;

use App\Support\Operator\OperatorModeKey;
use App\Support\Operator\ReturnsToOperatorDesk;
use Illuminate\Http\Request;

trait ReturnsToDesignerDesk
{
    use ReturnsToOperatorDesk;

    protected function wantsDesignerDeskReturn(?Request $request = null): bool
    {
        return $this->wantsOperatorDeskReturn(OperatorModeKey::Designer, $request);
    }

    /**
     * @param  array<string, mixed>  $params
     */
    protected function designerDeskUrl(array $params = []): string
    {
        return $this->operatorDeskUrl(OperatorModeKey::Designer, $params);
    }
}
