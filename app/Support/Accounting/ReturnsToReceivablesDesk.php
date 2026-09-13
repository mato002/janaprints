<?php

namespace App\Support\Accounting;

use Illuminate\Http\Request;

trait ReturnsToReceivablesDesk
{
    protected function wantsReceivablesReturn(?Request $request = null): bool
    {
        $request ??= request();

        return $request->input('from') === 'receivables';
    }

    /**
     * @param  array<string, mixed>  $params
     */
    protected function receivablesInvoicesUrl(array $params = []): string
    {
        return route('admin.invoices.index', $params);
    }
}
