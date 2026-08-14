<?php

namespace App\Support\Operator;

use Illuminate\Http\Request;

trait ReturnsToOperatorDesk
{
    protected function wantsOperatorDeskReturn(OperatorModeKey $mode, ?Request $request = null): bool
    {
        $request ??= request();
        $config = OperatorModeRegistry::returnConfig($mode);

        return $request->input('from') === $config['from']
            || $request->boolean($config['flag']);
    }

    /**
     * @param  array<string, mixed>  $params
     */
    protected function operatorDeskUrl(OperatorModeKey $mode, array $params = []): string
    {
        return OperatorModeRegistry::homeUrl($mode, $params);
    }
}
