<?php

namespace App\Support\Tax;

use App\Enums\TaxReturnStatus;
use App\Models\Tax\TaxPeriod;
use App\Models\Tax\TaxReturn;
use Illuminate\Support\Facades\DB;

class TaxReturnService
{
    public function __construct(
        protected TaxReportService $reports,
    ) {}

    public function buildDraft(TaxPeriod $period, int $userId): TaxReturn
    {
        $summary = $this->reports->vatSummary([
            'company_id' => $period->company_id,
            'from_date' => $period->start_date->toDateString(),
            'to_date' => $period->end_date->toDateString(),
            'tax_period_id' => $period->id,
        ]);

        return TaxReturn::query()->updateOrCreate(
            [
                'company_id' => $period->company_id,
                'tax_period_id' => $period->id,
                'return_type' => 'vat',
            ],
            [
                'return_number' => 'TR-'.$period->code,
                'status' => TaxReturnStatus::Draft,
                'output_tax' => $summary['output_vat'],
                'input_tax' => $summary['input_vat'],
                'withholding_tax' => $summary['withholding_tax'],
                'net_liability' => $summary['net_liability'],
            ],
        );
    }

    public function file(TaxReturn $taxReturn, int $userId): TaxReturn
    {
        return DB::transaction(function () use ($taxReturn, $userId) {
            $taxReturn->update([
                'status' => TaxReturnStatus::Filed,
                'filed_by' => $userId,
                'filed_at' => now(),
            ]);

            return $taxReturn->fresh(['taxPeriod', 'filedByUser']);
        });
    }
}
