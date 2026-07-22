<?php

namespace App\Support\Operator;

use Illuminate\Http\Request;

trait ReturnsToOperatorDesk
{
    abstract protected function operatorDeskModeKey(): OperatorModeKey;

    protected function wantsOperatorDeskReturn(?Request $request = null): bool
    {
        $request ??= request();
        $config = OperatorModeRegistry::returnConfig($this->operatorDeskModeKey());

        return $request->input('from') === $config['from']
            || $request->boolean($config['flag']);
    }

    /**
     * @param  array<string, mixed>  $params
     */
    protected function operatorDeskUrl(array $params = []): string
    {
        $config = OperatorModeRegistry::returnConfig($this->operatorDeskModeKey());

        return route($config['route'], $params);
    }
}
