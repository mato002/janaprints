<?php

namespace App\Support\Hr;

use App\Enums\PostingEventCode;
use App\Models\Accounting\Journal;
use App\Models\Hr\PayrollRun;
use App\Support\Accounting\AccountingPostingService;

class PayrollAccountingPostingService
{
    public function __construct(
        protected AccountingPostingService $posting,
    ) {}

    public function postPayrollRun(PayrollRun $run, int $userId): Journal
    {
        return $this->posting->postEvent(
            PostingEventCode::PayrollPosted,
            $run->company_id,
            $userId,
            'payroll_run',
            $run->id,
            $run->pay_date->toDateString(),
            [
                'gross_amount' => (float) $run->gross_total,
                'paye_amount' => (float) $run->paye_total,
                'shif_amount' => (float) $run->shif_total,
                'nssf_amount' => (float) $run->nssf_total,
                'housing_levy_amount' => (float) $run->housing_levy_total,
                'net_amount' => (float) $run->net_total,
            ],
            $run->branch_id,
            reference: $run->reference,
            description: __('Payroll :ref (:start – :end)', [
                'ref' => $run->reference,
                'start' => $run->period_start->format('Y-m-d'),
                'end' => $run->period_end->format('Y-m-d'),
            ]),
        );
    }
}
