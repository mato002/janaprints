<?php

namespace App\Support\Accounting;

use App\Support\Sales\ReceivablesInvoiceViews;
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
        return ReceivablesInvoiceViews::invoicesUrl($params);
    }

    /**
     * @param  array<string, mixed>  $params
     */
    protected function receivablesJobsUrl(array $params = []): string
    {
        return ReceivablesInvoiceViews::jobsUrl($params);
    }
}
